<?php
declare(strict_types=1);

if (!defined('MASS_ROOT')) {
    define('MASS_ROOT', dirname(__DIR__));
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once MASS_ROOT . '/backend/bootstrap.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/functions.php';

// Prefijo relativo desde admin/ o cliente_interfaces/ hacia la raíz del proyecto
if (!defined('MASS_BASE_HREF')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#/(admin|cliente_interfaces|proveedor)$#', $scriptDir)) {
        define('MASS_BASE_HREF', '../');
    } elseif (preg_match('#/frontend/views/(admin|cliente|proveedor)$#', $scriptDir)) {
        define('MASS_BASE_HREF', '../../../');
    } else {
        define('MASS_BASE_HREF', '');
    }
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (!isset($_SESSION['guardados']) || !is_array($_SESSION['guardados'])) {
    $_SESSION['guardados'] = [];
}

if (!isset($_SESSION['pedido']) || !is_array($_SESSION['pedido'])) {
    $_SESSION['pedido'] = array_merge(pedido_defaults(), [
        'cupon' => 'CLUBMASS10',
        'tienda_id' => 'magdalena',
    ]);
}

if (!empty($_SESSION['flash'])) {
    $flash = (string) $_SESSION['flash'];
    unset($_SESSION['flash']);
} else {
    $flash = '';
}
