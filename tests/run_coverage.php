<?php
declare(strict_types=1);

/**
 * composer test:coverage
 * Genera JUnit y Clover y exige el umbral de líneas.
 * El umbral se lee de tests/umbral_cobertura.php. null significa que todavía
 * no hay una medición real: el comando falla y no inventa un porcentaje.
 */

$root = dirname(__DIR__);
chdir($root);

$phpunit = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpunit';
$clover = $root . '/docs/pruebas/evidencias/coverage/clover.xml';
$junit = $root . '/docs/pruebas/evidencias/junit.xml';
$html = $root . '/docs/pruebas/evidencias/coverage/html';
$log = $root . '/docs/pruebas/evidencias/coverage/consola.txt';
$umbralArchivo = $root . '/tests/umbral_cobertura.php';

if (!is_file($phpunit)) {
    fwrite(STDERR, "ERROR: falta vendor/bin/phpunit. composer install no pudo ejecutarse o vendor no está instalado.\n");
    exit(1);
}

if (!is_dir(dirname($clover))) {
    mkdir(dirname($clover), 0777, true);
}

if (is_file($clover)) {
    unlink($clover);
}

$cmd = escapeshellarg(PHP_BINARY)
    . ' -d extension=pcov -d pcov.enabled=1 '
    . escapeshellarg($phpunit)
    . ' --coverage-text'
    . ' --coverage-clover=' . escapeshellarg(str_replace('\\', '/', $clover))
    . ' --coverage-html=' . escapeshellarg(str_replace('\\', '/', $html))
    . ' --log-junit=' . escapeshellarg(str_replace('\\', '/', $junit))
    . ' --testdox-html=' . escapeshellarg(str_replace('\\', '/', $root . '/docs/pruebas/evidencias/testdox.html'));

$descriptor = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$proceso = proc_open($cmd, $descriptor, $pipes, $root);
if (!is_resource($proceso)) {
    fwrite(STDERR, "ERROR: no se pudo iniciar PHPUnit.\n");
    exit(1);
}
fclose($pipes[0]);
$salida = stream_get_contents($pipes[1]);
$errores = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$codigo = proc_close($proceso);

$texto = $salida . ($errores !== '' ? "\n" . $errores : '');
echo $texto;
file_put_contents($log, $texto);

$umbral = is_file($umbralArchivo) ? require $umbralArchivo : null;
$metricas = metricasClover(is_file($clover) ? $clover : null);

$resumen = "\n--- umbral ---\n";
$resumen .= 'Fecha: ' . date('Y-m-d H:i:s') . "\n";
$resumen .= 'Exit PHPUnit: ' . $codigo . "\n";
if ($metricas === null) {
    $resumen .= "Clover: no generado. No hay driver de cobertura (PCOV/Xdebug).\n";
    $resumen .= "Umbral: no aplicado. No se fija un porcentaje sin medición.\n";
} else {
    $resumen .= 'Cobertura global: ' . $metricas['global'] . "%\n";
    $resumen .= 'Cobertura backend/: ' . $metricas['backend'] . "%\n";
    $resumen .= 'Sentencias global: ' . $metricas['covered'] . '/' . $metricas['statements'] . "\n";
    $resumen .= 'Sentencias backend: ' . $metricas['backendCovered'] . '/' . $metricas['backendStatements'] . "\n";
    if (!is_int($umbral) && !is_float($umbral)) {
        $resumen .= "Umbral: no configurado (tests/umbral_cobertura.php devolvió null).\n";
        $codigo = $codigo === 0 ? 2 : $codigo;
    } else {
        $resumen .= 'Umbral mínimo de líneas: ' . $umbral . "%\n";
        if ($metricas['global'] + 0.0001 < (float) $umbral) {
            $resumen .= "RESULTADO: no alcanza el umbral.\n";
            $codigo = $codigo === 0 ? 1 : $codigo;
        } else {
            $resumen .= "RESULTADO: alcanza el umbral.\n";
        }
    }
}
echo $resumen;
file_put_contents($log, $texto . $resumen);

if ($metricas === null && $codigo === 0) {
    $codigo = 1;
}

exit($codigo);

/**
 * @return array{global:float,backend:float,statements:int,covered:int,backendStatements:int,backendCovered:int}|null
 */
function metricasClover(?string $ruta): ?array
{
    if ($ruta === null || !is_file($ruta)) {
        return null;
    }
    $xml = @simplexml_load_file($ruta);
    if ($xml === false) {
        return null;
    }
    $proyecto = $xml->project;
    if (!isset($proyecto->metrics)) {
        return null;
    }
    $m = $proyecto->metrics;
    $statements = (int) $m['statements'];
    $covered = (int) $m['coveredstatements'];
    if ($statements < 1) {
        return null;
    }

    $backendStatements = 0;
    $backendCovered = 0;
    foreach ($proyecto->file as $file) {
        $nombre = str_replace('\\', '/', (string) $file['name']);
        if (!str_contains($nombre, '/backend/')) {
            continue;
        }
        if (!isset($file->metrics)) {
            continue;
        }
        $backendStatements += (int) $file->metrics['statements'];
        $backendCovered += (int) $file->metrics['coveredstatements'];
    }

    return [
        'global' => round(100 * $covered / $statements, 2),
        'backend' => $backendStatements > 0 ? round(100 * $backendCovered / $backendStatements, 2) : 0.0,
        'statements' => $statements,
        'covered' => $covered,
        'backendStatements' => $backendStatements,
        'backendCovered' => $backendCovered,
    ];
}
