<?php
declare(strict_types=1);

/**
 * Ejecuta PHPUnit y, si existen, las suites CLI legacy de Mass Online.
 *   php tests/run_all.php
 */

$root = dirname(__DIR__);
$failed = 0;

echo "=== Mass Online · suite completa ===\n\n";

$phpunit = $root . '/vendor/bin/phpunit';
if (is_file($phpunit)) {
    echo "── PHPUnit ──\n";
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phpunit), $code);
    if ($code !== 0) {
        $failed++;
        echo "⇒ FALLÓ PHPUnit (exit {$code})\n\n";
    } else {
        echo "⇒ OK PHPUnit\n\n";
    }
} else {
    echo "SKIP  PHPUnit (composer install pendiente)\n\n";
}

$suites = [
    'auth_test.php',
    'proveedor_test.php',
    'negocio_test.php',
    'productos_admin_test.php',
    'carrito_test.php',
    'csrf_test.php',
    'redirect_test.php',
];

foreach ($suites as $suite) {
    $path = $root . '/tests/' . $suite;
    if (!is_file($path)) {
        echo "SKIP  {$suite} (no existe)\n";
        continue;
    }
    echo "── {$suite} ──\n";
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path), $code);
    if ($code !== 0) {
        $failed++;
        echo "⇒ FALLÓ {$suite} (exit {$code})\n\n";
    } else {
        echo "⇒ OK {$suite}\n\n";
    }
}

echo "Resumen: fallos={$failed}\n";
exit($failed > 0 ? 1 : 0);
