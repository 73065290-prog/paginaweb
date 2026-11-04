<?php
declare(strict_types=1);

namespace Tests\Unit;

use PedidoDetalle;
use Tests\TestCase;

final class ModelsPedidoDetalleTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto(): void
    {
        $det = new PedidoDetalle(
            id: 1,
            pedido_id: 15,
            producto_id: 3,
            nombre: 'Arroz Extra Costeño 750 g',
            precio_unit: 4.50,
            cantidad: 2,
            subtotal: 9.00,
        );

        $this->assertSame(1, $det->id);
        $this->assertSame(15, $det->pedido_id);
        $this->assertSame(3, $det->producto_id);
        $this->assertSame('Arroz Extra Costeño 750 g', $det->nombre);
        $this->assertSame(4.50, $det->precio_unit);
        $this->assertSame(2, $det->cantidad);
        $this->assertSame(9.00, $det->subtotal);
    }

    public function test_from_row_y_to_array_con_producto_id_nulo(): void
    {
        $row = [
            'id' => '8',
            'pedido_id' => '15',
            'producto_id' => null,
            'nombre' => 'Producto descontinuado',
            'precio_unit' => '6.20',
            'cantidad' => '3',
            'subtotal' => '18.60',
        ];

        $det = PedidoDetalle::fromRow($row);
        $arr = $det->toArray();

        $this->assertSame(8, $det->id);
        $this->assertSame(15, $det->pedido_id);
        $this->assertNull($det->producto_id);
        $this->assertSame(6.20, $det->precio_unit);
        $this->assertSame(3, $det->cantidad);
        $this->assertSame(18.60, $det->subtotal);
        $this->assertNull($arr['producto_id']);
        $this->assertSame(18.60, $arr['subtotal']);
    }
}

