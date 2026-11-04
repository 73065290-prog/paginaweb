<?php
declare(strict_types=1);

/**
 * Controlador del panel administrativo SGC-Mass (dashboard, productos, inventario y pedidos).
 */
final class AdminController
{
    /**
     * Renderiza el dashboard general de ventas e inventario (admin/index.php).
     * Requiere sesión activa con rol admin.
     */
    public function dashboard(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/admin/index.php';
    }

    /**
     * Gestiona el listado, alta y edición de productos WMS usando ProductoService (admin/productos.php).
     * Requiere sesión activa con rol admin.
     */
    public function productos(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/admin/productos.php';
    }

    /**
     * Renderiza el control de inventario y góndolas usando InventarioService (admin/inventario.php).
     * Requiere sesión activa con rol admin.
     */
    public function inventario(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/admin/inventario.php';
    }

    /**
     * Renderiza el listado y filtrado de pedidos y cola de picking (admin/pedidos.php).
     * Requiere sesión activa con rol admin.
     */
    public function pedidos(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/admin/pedidos.php';
    }
}

