<?php
declare(strict_types=1);

/**
 * Ejecuta todas las suites CLI de Mass Online / SGC-Mass.
 *   php tests/run_all.php
 */

$root = dirname(__DIR__);
$suites = [
    'auth_test.php',
    'negocio_test.php',
    'productos_admin_test.php',
    'carrito_test.php',
    'csrf_test.php',
    'redirect_test.php',
];

$failed = 0;
$ran = 0;

echo "=== Mass Online · suite completa ===\n\n";

foreach ($suites as $suite) {
    $path = $root . '/tests/' . $suite;
    if (!is_file($path)) {
        echo "SKIP  {$suite} (no existe en esta rama)\n";
        continue;
    }
    $ran++;
    echo "── {$suite} ──\n";
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path), $code);
    if ($code !== 0) {
        $failed++;
        echo "⇒ FALLÓ {$suite} (exit {$code})\n\n";
    } else {
        echo "⇒ OK {$suite}\n\n";
    }
}

echo "Resumen: {$ran} ejecutadas, {$failed} con error\n";
exit($failed > 0 ? 1 : 0);
