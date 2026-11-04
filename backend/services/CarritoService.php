<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/ProductoService.php';

/**
 * Servicio de negocio del carrito de compras (límites de 99 unidades, totales y guardados para después).
 */
final class CarritoService
{
    public const CANTIDAD_MAXIMA = 99;

    private ProductoService $productoService;

    public function __construct(?ProductoService $productoService = null)
    {
        $this->productoService = $productoService ?? new ProductoService();
    }

    /**
     * Tope máximo de unidades permitido por línea del carrito.
     */
    public function cantidadMaxima(): int
    {
        return self::CANTIDAD_MAXIMA;
    }

    /**
     * Retorna las líneas actuales del carrito en sesión.
     *
     * @return array<int, array{id:int,cantidad:int}>
     */
    public function items(): array
    {
        $items = $_SESSION['carrito'] ?? [];
        return is_array($items) ? $items : [];
    }

    /**
     * Retorna los productos guardados para después en sesión.
     *
     * @return array<int, array{id:int,cantidad:int}>
     */
    public function guardados(): array
    {
        $guardados = $_SESSION['guardados'] ?? [];
        return is_array($guardados) ? $guardados : [];
    }

    /**
     * Suma el total de unidades en el carrito activo.
     */
    public function totalUnidades(): int
    {
        $total = 0;
        foreach ($this->items() as $item) {
            $total += (int) ($item['cantidad'] ?? 0);
        }
        return $total;
    }

    /**
     * Calcula el monto subtotal bruto de todas las líneas válidas del carrito.
     */
    public function totalMonto(): float
    {
        $total = 0.0;
        foreach ($this->items() as $item) {
            $p = $this->productoService->obtenerPorId((int) ($item['id'] ?? 0));
            if ($p !== null) {
                $total += (float) $p['precio'] * (int) ($item['cantidad'] ?? 0);
            }
        }
        return $total;
    }

    /**
     * Consulta informativa del stock disponible de un SKU (sin alterar la regla comercial de tope 99).
     */
    public function stockDisponible(int $id): int
    {
        if (function_exists('mass_producto_detalle')) {
            $detalle = mass_producto_detalle($id);
            if (is_array($detalle) && isset($detalle['stockCantidad'])) {
                return max(0, (int) $detalle['stockCantidad']);
            }
        }
        $p = $this->productoService->obtenerPorId($id);
        if ($p === null) {
            return 0;
        }
        return (($p['stockTone'] ?? 'ok') === 'bajo') ? 8 : 24;
    }

    /**
     * Agrega un producto al carrito respetando la existencia del SKU y el tope de 99 unidades.
     */
    public function agregar(int $id, int $cantidad = 1): void
    {
        if ($cantidad < 1 || $this->productoService->obtenerPorId($id) === null) {
            return;
        }
        if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        $max = $this->cantidadMaxima();
        $cantidad = min($max, $cantidad);
        foreach ($_SESSION['carrito'] as &$item) {
            if ((int) $item['id'] === $id) {
                $item['cantidad'] = min($max, (int) $item['cantidad'] + $cantidad);
                return;
            }
        }
        unset($item);
        $_SESSION['carrito'][] = ['id' => $id, 'cantidad' => $cantidad];
    }

    /**
     * Modifica la cantidad de una línea del carrito según un delta positivo o negativo.
     */
    public function cambiar(int $id, int $delta): void
    {
        if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
            return;
        }
        foreach ($_SESSION['carrito'] as $i => $item) {
            if ((int) $item['id'] !== $id) {
                continue;
            }
            $nueva = (int) $item['cantidad'] + $delta;
            if ($nueva <= 0) {
                unset($_SESSION['carrito'][$i]);
                $_SESSION['carrito'] = array_values($_SESSION['carrito']);
            } else {
                $_SESSION['carrito'][$i]['cantidad'] = min($this->cantidadMaxima(), $nueva);
            }
            return;
        }
    }

    /**
     * Establece una cantidad exacta para una línea del carrito (si es < 1, la elimina; capea a 99).
     */
    public function setCantidad(int $id, int $cantidad): void
    {
        if ($cantidad < 1) {
            $this->eliminar($id);
            return;
        }
        if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
            return;
        }
        foreach ($_SESSION['carrito'] as $i => $item) {
            if ((int) $item['id'] === $id) {
                $_SESSION['carrito'][$i]['cantidad'] = min($this->cantidadMaxima(), $cantidad);
                return;
            }
        }
    }

    /**
     * Elimina una línea del carrito por ID de producto.
     */
    public function eliminar(int $id): void
    {
        $_SESSION['carrito'] = array_values(array_filter(
            $this->items(),
            static fn (array $item): bool => (int) ($item['id'] ?? 0) !== $id
        ));
    }

    /**
     * Vacía por completo el carrito activo.
     */
    public function vaciar(): void
    {
        $_SESSION['carrito'] = [];
    }

    /**
     * Traslada un producto del carrito a la lista de guardados para después.
     */
    public function guardarParaDespues(int $id): void
    {
        $p = $this->productoService->obtenerPorId($id);
        if ($p === null) {
            return;
        }
        $qty = 1;
        foreach ($this->items() as $item) {
            if ((int) $item['id'] === $id) {
                $qty = (int) $item['cantidad'];
                break;
            }
        }
        $this->eliminar($id);
        if (!isset($_SESSION['guardados']) || !is_array($_SESSION['guardados'])) {
            $_SESSION['guardados'] = [];
        }
        foreach ($_SESSION['guardados'] as &$g) {
            if ((int) $g['id'] === $id) {
                $g['cantidad'] = (int) $g['cantidad'] + $qty;
                return;
            }
        }
        unset($g);
        $_SESSION['guardados'][] = ['id' => $id, 'cantidad' => $qty];
    }

    /**
     * Recupera un producto guardado para después y lo devuelve al carrito activo.
     */
    public function recuperarGuardado(int $id): void
    {
        $guardados = $this->guardados();
        $qty = 0;
        $restantes = [];
        foreach ($guardados as $g) {
            if ((int) ($g['id'] ?? 0) === $id) {
                $qty = max(1, (int) ($g['cantidad'] ?? 1));
            } else {
                $restantes[] = $g;
            }
        }
        $_SESSION['guardados'] = $restantes;
        if ($qty > 0) {
            $this->agregar($id, $qty);
        }
    }
}

