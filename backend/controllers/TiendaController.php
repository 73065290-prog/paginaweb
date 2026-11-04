<?php
declare(strict_types=1);

/**
 * Controlador de vistas públicas de la tienda Mass Online (portada, catálogo y ficha de producto).
 */
final class TiendaController
{
    /**
     * Renderiza la portada principal de la tienda (cliente_interfaces/index.php).
     */
    public function home(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/index.php';
    }

    /**
     * Renderiza el catálogo general con filtros de categoría, búsqueda, precio y marca (cliente_interfaces/catalogo.php).
     */
    public function catalogo(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/catalogo.php';
    }

    /**
     * Renderiza la ficha de detalle de producto o redirige al catálogo si el ID no existe (cliente_interfaces/producto.php).
     */
    public function producto(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/producto.php';
    }
}

