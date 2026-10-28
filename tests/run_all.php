<?php
declare(strict_types=1);

/**
 * Ejecutor simple de la suite PHPUnit (alternativa a composer test).
 */
$root = dirname(__DIR__);
$phpunit = $root . '/vendor/bin/phpunit';
if (!is_file($phpunit)) {
    fwrite(STDERR, "PHPUnit no encontrado. Ejecuta: composer install\n");
    exit(1);
}

passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phpunit), $code);
exit($code);
