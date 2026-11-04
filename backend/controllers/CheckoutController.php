<?php
declare(strict_types=1);

/**
 * Controlador del flujo de checkout por pasos y confirmación/tracking de pedido.
 */
final class CheckoutController
{
    /**
     * Renderiza el checkout en sus 3 pasos (carrito, entrega/retiro, pago) usando PedidoService y CarritoService.
     */
    public function pedido(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/pedido.php';
    }

    /**
     * Renderiza la pantalla de confirmación y tracking del pedido usando PedidoService.
     */
    public function confirmacion(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/confirmacion.php';
    }
}

