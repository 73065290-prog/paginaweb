<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
$router = require MASS_ROOT . '/backend/routes/web.php';
$router->dispatch('admin/pedidos.php');
