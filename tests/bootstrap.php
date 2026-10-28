<?php
declare(strict_types=1);

/**
 * Bootstrap PHPUnit — sesión aislada (sin tocar BD de desarrollo).
 * Persistencia de la app: $_SESSION (carrito, auth, overrides admin).
 */

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$_SESSION = [];

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Estado mínimo que normalmente prepara includes/init.php
$_SESSION['carrito'] = [];
$_SESSION['guardados'] = [];
$_SESSION['pedido'] = function_exists('pedido_defaults') ? pedido_defaults() : [];
$_SESSION['admin_producto_overrides'] = [];
$_SESSION['admin_productos_extra'] = [];
unset($_SESSION['auth'], $_SESSION['flash'], $_SESSION['ultimo_pedido']);
