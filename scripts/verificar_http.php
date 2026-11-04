<?php
declare(strict_types=1);

/**
 * Comprueba por HTTP que las rutas sensibles responden 403 o 404
 * (PROMPT 19, INT-004-BOLT-011).
 *
 * Uso: php scripts/verificar_http.php [base]
 *      base por defecto: http://127.0.0.1/paginaweb
 *
 * No imprime cuerpos de respuesta. Código de salida: 0 si todas cumplen, 1 si alguna no.
 */

$base = rtrim((string) ($argv[1] ?? 'http://127.0.0.1/paginaweb'), '/');

$rutas = [
    '/database/schema.sql',
    '/backend/config/database.local.php',
    '/backend/bootstrap.php',
    '/tests/',
    '/tests/auth_test.php',
];

$permitidos = ['403', '404'];
$fallos = 0;

$control = codigoHttp($base . '/admin/login.php');
echo 'CONTROL ' . $base . '/admin/login.php ' . $control . PHP_EOL;
if ($control !== '200') {
    echo 'ERROR la aplicación no respondió 200. La base no apunta al proyecto publicado.' . PHP_EOL;
    exit(1);
}

foreach ($rutas as $ruta) {
    $url = $base . $ruta;
    $codigo = codigoHttp($url);
    $ok = in_array($codigo, $permitidos, true);
    echo ($ok ? 'OK  ' : 'FALLO ') . $codigo . ' ' . $url . PHP_EOL;
    if (!$ok) {
        $fallos++;
    }
}

echo 'RESULTADO fallos=' . $fallos . PHP_EOL;
exit($fallos === 0 ? 0 : 1);

function codigoHttp(string $url): string
{
    $curl = curlBin();
    $cmd = $curl . ' -s -o NUL -w "%{http_code}" --max-time 15 ' . escapeshellarg($url);
    $salida = shell_exec($cmd);
    $codigo = trim((string) $salida);
    if ($codigo === '') {
        return '000';
    }
    return $codigo;
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
