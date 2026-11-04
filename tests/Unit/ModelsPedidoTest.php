<?php
declare(strict_types=1);

namespace Tests\Unit;

use Pedido;
use Tests\TestCase;

final class ModelsPedidoTest extends TestCase
{
    public function test_creacion_directa_y_valores_nulos_por_defecto(): void
    {
        $pedido = new Pedido(
            id: 1,
            codigo: 'MASS-2026-1001',
        );

        $this->assertSame(1, $pedido->id);
        $this->assertSame('MASS-2026-1001', $pedido->codigo);
        $this->assertNull($pedido->usuario_id);
        $this->assertSame('retiro', $pedido->entrega);
        $this->assertSame('yape', $pedido->pago);
        $this->assertSame('boleta', $pedido->comprobante);
        $this->assertNull($pedido->tienda_id);
        $this->assertSame(0.0, $pedido->subtotal);
        $this->assertSame(0.0, $pedido->descuento);
        $this->assertSame(0.0, $pedido->envio);
        $this->assertSame(0.0, $pedido->total);
        $this->assertSame('confirmado', $pedido->estado);
        $this->assertNull($pedido->created_at);
    }

    public function test_from_row_y_to_array_con_pedido_retiro_y_express(): void
    {
        $rowRetiro = [
            'id' => '15',
            'codigo' => 'MASS-2026-2045',
            'usuario_id' => '2',
            'entrega' => 'retiro',
            'pago' => 'yape',
            'comprobante' => 'boleta',
            'nombre' => 'Ana',
            'apellido' => 'Quispe',
            'documento' => '71234567',
            'telefono' => '987654321',
            'direccion' => '',
            'referencia' => '',
            'tienda_id' => 'magdalena',
            'horario' => 'Hoy · 4:00 p. m. - 6:00 p. m.',
            'cupon' => 'CLUBMASS10',
            'subtotal' => '45.50',
            'descuento' => '10.00',
            'envio' => '0.00',
            'total' => '35.50',
            'estado' => 'confirmado',
            'created_at' => '2026-10-27 16:30:00',
        ];

        $pedido = Pedido::fromRow($rowRetiro);
        $arr = $pedido->toArray();

        $this->assertSame(15, $pedido->id);
        $this->assertSame(2, $pedido->usuario_id);
        $this->assertSame('magdalena', $pedido->tienda_id);
        $this->assertSame(45.50, $pedido->subtotal);
        $this->assertSame(10.00, $pedido->descuento);
        $this->assertSame(0.0, $pedido->envio);
        $this->assertSame(35.50, $pedido->total);
        $this->assertSame('2026-10-27 16:30:00', $arr['created_at']);

        $rowExpress = $rowRetiro;
        $rowExpress['usuario_id'] = null;
        $rowExpress['entrega'] = 'express';
        $rowExpress['tienda_id'] = null;
        $rowExpress['envio'] = '5.90';
        $rowExpress['total'] = '41.40';

        $pedidoExpress = Pedido::fromRow($rowExpress);
        $this->assertNull($pedidoExpress->usuario_id);
        $this->assertNull($pedidoExpress->tienda_id);
        $this->assertSame(5.90, $pedidoExpress->envio);
        $this->assertSame(41.40, $pedidoExpress->total);
    }
}

