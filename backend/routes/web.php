<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/Router.php';
require_once dirname(__DIR__) . '/controllers/AuthController.php';
require_once dirname(__DIR__) . '/controllers/TiendaController.php';
require_once dirname(__DIR__) . '/controllers/CarritoController.php';
require_once dirname(__DIR__) . '/controllers/CheckoutController.php';
require_once dirname(__DIR__) . '/controllers/AdminController.php';

/**
 * Definición centralizada y ejecutable de las rutas web de Mass Online / SGC-Mass.
 * Contrato: URL pública -> Método(s) HTTP -> Controller::acción -> Vista.
 */
$router = new Router();

// ── Rutas públicas de tienda (cliente_interfaces/) ──
$router->get(
    'cliente_interfaces/index.php',
    TiendaController::class,
    'home',
    'cliente.home',
    'frontend/views/cliente/index.php'
);

$router->get(
    'cliente_interfaces/catalogo.php',
    TiendaController::class,
    'catalogo',
    'cliente.catalogo',
    'frontend/views/cliente/catalogo.php'
);

$router->get(
    'cliente_interfaces/producto.php',
    TiendaController::class,
    'producto',
    'cliente.producto',
    'frontend/views/cliente/producto.php'
);

$router->match(
    ['GET', 'POST'],
    'cliente_interfaces/carrito.php',
    CarritoController::class,
    'handle',
    'cliente.carrito',
    'frontend/views/cliente/carrito.php'
);

$router->get(
    'cliente_interfaces/pedido.php',
    CheckoutController::class,
    'pedido',
    'cliente.pedido',
    'frontend/views/cliente/pedido.php'
);

$router->get(
    'cliente_interfaces/confirmacion.php',
    CheckoutController::class,
    'confirmacion',
    'cliente.confirmacion',
    'frontend/views/cliente/confirmacion.php'
);

$router->get(
    'cliente_interfaces/logout.php',
    AuthController::class,
    'logoutClienteView',
    'cliente.logout',
    'frontend/views/cliente/logout.php'
);

// ── Rutas del panel administrativo (admin/) ──
$router->match(
    ['GET', 'POST'],
    'admin/login.php',
    AuthController::class,
    'loginView',
    'admin.login',
    'frontend/views/admin/login.php'
);

$router->get(
    'admin/logout.php',
    AuthController::class,
    'logoutAdminView',
    'admin.logout',
    'frontend/views/admin/logout.php'
);

$router->get(
    'admin/index.php',
    AdminController::class,
    'dashboard',
    'admin.dashboard',
    'frontend/views/admin/index.php'
);

$router->match(
    ['GET', 'POST'],
    'admin/productos.php',
    AdminController::class,
    'productos',
    'admin.productos',
    'frontend/views/admin/productos.php'
);

$router->get(
    'admin/inventario.php',
    AdminController::class,
    'inventario',
    'admin.inventario',
    'frontend/views/admin/inventario.php'
);

$router->get(
    'admin/pedidos.php',
    AdminController::class,
    'pedidos',
    'admin.pedidos',
    'frontend/views/admin/pedidos.php'
);

return $router;
