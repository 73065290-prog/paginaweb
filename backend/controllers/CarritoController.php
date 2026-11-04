<?php
declare(strict_types=1);

/**
 * Controlador de operaciones del carrito, cupones y transición de pasos del checkout (cliente_interfaces/carrito.php).
 */
final class CarritoController
{
    /**
     * Procesa las acciones del carrito y cupones delegando en CarritoService / CuponService / PedidoService.
     */
    public function handle(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/carrito.php';
    }
}

