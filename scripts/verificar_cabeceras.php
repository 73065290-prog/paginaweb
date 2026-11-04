<?php
declare(strict_types=1);

/**
 * Comprueba con curl -I las cabeceras de seguridad definidas en .htaccess.
 *
 * Uso: php scripts/verificar_cabeceras.php [url]
 *      url por defecto: http://127.0.0.1/paginaweb/admin/login.php
 *
 * No envía credenciales ni modifica datos. Código de salida: 0 si todas coinciden, 1 si alguna falla.
 */

$url = (string) ($argv[1] ?? 'http://127.0.0.1/paginaweb/admin/login.php');

$esperadas = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
];

echo 'URL ' . $url . PHP_EOL;

$consulta = consultar($url);
if ($consulta['error'] !== null) {
    echo 'ERROR conexion ' . $consulta['error'] . PHP_EOL;
    echo 'RESULTADO fallido' . PHP_EOL;
    exit(1);
}

echo 'HTTP ' . $consulta['estado'] . PHP_EOL;
if ($consulta['estado'] < 200 || $consulta['estado'] >= 400) {
    echo 'ERROR respuesta HTTP inesperada' . PHP_EOL;
    echo 'RESULTADO fallido' . PHP_EOL;
    exit(1);
}

$fallos = 0;
foreach ($esperadas as $nombre => $valorEsperado) {
    $clave = strtolower($nombre);
    if (!array_key_exists($clave, $consulta['cabeceras'])) {
        echo 'AUSENTE ' . $nombre . PHP_EOL;
        echo '  esperada: ' . $valorEsperado . PHP_EOL;
        echo '  recibida: (ninguna)' . PHP_EOL;
        $fallos++;
        continue;
    }
    $recibida = $consulta['cabeceras'][$clave];
    if ($recibida !== $valorEsperado) {
        echo 'INCORRECTA ' . $nombre . PHP_EOL;
        echo '  esperada: ' . $valorEsperado . PHP_EOL;
        echo '  recibida: ' . $recibida . PHP_EOL;
        $fallos++;
        continue;
    }
    echo 'PRESENTE ' . $nombre . PHP_EOL;
    echo '  esperada: ' . $valorEsperado . PHP_EOL;
    echo '  recibida: ' . $recibida . PHP_EOL;
}

echo 'RESULTADO ' . ($fallos === 0 ? 'aprobado' : 'fallido') . PHP_EOL;
exit($fallos === 0 ? 0 : 1);

/**
 * @return array{estado:int, cabeceras:array<string, string>, error:?string}
 */
function consultar(string $url): array
{
    $vacio = ['estado' => 0, 'cabeceras' => [], 'error' => null];
    if (!preg_match('#\Ahttps?://#i', $url)) {
        $vacio['error'] = 'la URL debe empezar por http:// o https://';
        return $vacio;
    }

    $curl = curlBin();
    $cmd = $curl . ' -sSI --max-time 15 ' . escapeshellarg($url) . ' 2>&1';
    $salida = shell_exec($cmd);
    $texto = trim((string) $salida);
    if ($texto === '') {
        $vacio['error'] = 'curl no devolvió respuesta';
        return $vacio;
    }
    if (stripos($texto, 'curl:') === 0 || !preg_match('/^HTTP\//i', $texto)) {
        $vacio['error'] = preg_replace('/\s+/', ' ', $texto) ?? $texto;
        return $vacio;
    }

    $bloques = preg_split("/\r?\n\r?\n/", $texto) ?: [];
    $ultimo = '';
    foreach ($bloques as $bloque) {
        if (preg_match('/^HTTP\//i', trim($bloque))) {
            $ultimo = $bloque;
        }
    }
    if ($ultimo === '') {
        $vacio['error'] = 'la respuesta no incluye una línea de estado';
        return $vacio;
    }

    $lineas = preg_split("/\r?\n/", $ultimo) ?: [];
    $estado = 0;
    if (preg_match('/^HTTP\/\S+\s+(\d+)/', $lineas[0] ?? '', $m)) {
        $estado = (int) $m[1];
    }
    $cabeceras = [];
    foreach (array_slice($lineas, 1) as $linea) {
        $partes = explode(':', $linea, 2);
        if (count($partes) !== 2) {
            continue;
        }
        $cabeceras[strtolower(trim($partes[0]))] = trim($partes[1]);
    }

    return ['estado' => $estado, 'cabeceras' => $cabeceras, 'error' => null];
}

function curlBin(): string
{
    $sistema = stripos(PHP_OS, 'WIN') === 0 ? 'curl.exe' : 'curl';
    $donde = shell_exec(($sistema === 'curl.exe' ? 'where.exe curl' : 'command -v curl') . ' 2>NUL');
    $ruta = trim((string) strtok((string) $donde, "\r\n"));
    if ($ruta !== '' && is_file($ruta)) {
        return escapeshellarg($ruta);
    }
    return $sistema;
}
