<?php
declare(strict_types=1);

/**
 * Mapa de rutas lógicas → vistas (referencia para shims Apache/XAMPP).
 * Las URLs públicas siguen siendo admin/ y cliente_interfaces/ por compatibilidad.
 */
return [
    'cliente.home' => 'frontend/views/cliente/index.php',
    'cliente.catalogo' => 'frontend/views/cliente/catalogo.php',
    'cliente.producto' => 'frontend/views/cliente/producto.php',
    'cliente.carrito' => 'frontend/views/cliente/carrito.php',
    'cliente.pedido' => 'frontend/views/cliente/pedido.php',
    'cliente.confirmacion' => 'frontend/views/cliente/confirmacion.php',
    'admin.login' => 'frontend/views/admin/login.php',
    'admin.dashboard' => 'frontend/views/admin/index.php',
    'admin.productos' => 'frontend/views/admin/productos.php',
    'admin.inventario' => 'frontend/views/admin/inventario.php',
    'admin.pedidos' => 'frontend/views/admin/pedidos.php',
];
