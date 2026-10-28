<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class PedidoConfirmacionTest extends TestCase
{
    /** // P26 */
    public function test_confirmar_pedido_express_guarda_orden_y_vacia_carrito(): void
    {
        carrito_agregar(1, 2);
        $horario = array_key_first(mass_horarios_entrega('express')) ?: 'hoy_tarde';
        $valid = pedido_validar_entrega([
            'entrega' => 'express',
            'nombre' => 'Mariana',
            'apellido' => 'Quispe',
            'documento' => '45678912',
            'telefono' => '999888777',
            'horario' => $horario,
            'direccion' => 'Av. Primavera 1234, Surco',
            'tienda_id' => 'magdalena',
        ]);
        $this->assertTrue($valid['ok'], implode(' · ', $valid['errores']));
        pedido_set($valid['data'] + ['cupon' => 'CLUBMASS10']);
        $orden = pedido_confirmar('tarjeta');
        $this->assertIsArray($orden);
        $this->assertStringStartsWith('MASS-', (string) ($orden['codigo'] ?? ''));
        $this->assertSame([], carrito_items());
        $this->assertNotNull(pedido_ultimo());
        $this->assertSame('express', $orden['pedido']['entrega'] ?? null);
        $this->assertSame(4.9, (float) ($orden['resumen']['envio'] ?? -1));
    }

    /** // P27 */
    public function test_confirmar_retiro_tiene_envio_cero(): void
    {
        carrito_agregar(2, 1);
        $horario = array_key_first(mass_horarios_entrega('retiro')) ?: 'hoy_tarde';
        $valid = pedido_validar_entrega([
            'entrega' => 'retiro',
            'nombre' => 'Carlos',
            'apellido' => 'Diaz',
            'documento' => '11223344',
            'telefono' => '911222333',
            'horario' => $horario,
            'tienda_id' => 'magdalena',
        ]);
        $this->assertTrue($valid['ok'], implode(' · ', $valid['errores']));
        pedido_set($valid['data'] + ['cupon' => '']);
        $orden = pedido_confirmar('yape');
        $this->assertIsArray($orden);
        $this->assertSame('retiro', $orden['pedido']['entrega'] ?? null);
        $this->assertSame(0.0, (float) ($orden['resumen']['envio'] ?? -1));
    }
}
