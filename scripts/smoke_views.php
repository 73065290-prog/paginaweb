<?php
declare(strict_types=1);

$_SERVER['SCRIPT_NAME'] = '/paginaweb/cliente_interfaces/index.php';
require dirname(__DIR__) . '/includes/init.php';

ob_start();
require MASS_ROOT . '/frontend/views/cliente/index.php';
$html = ob_get_clean();

echo 'db=' . (Database::connected() ? '1' : '0') . PHP_EOL;
echo 'asset=' . mass_asset('assets/css/styles.css') . PHP_EOL;
echo 'view_ok=' . (str_contains($html, 'hero') || str_contains($html, 'Mass') ? '1' : '0') . PHP_EOL;
echo 'len=' . strlen($html) . PHP_EOL;
