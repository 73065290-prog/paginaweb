<?php
declare(strict_types=1);

namespace Tests\Feature;

use Database;
use PedidoRepository;
use ProductoRepository;
use Tests\TestCase;

final class MysqlDisponibilidadTest extends TestCase
{
    public function test_mysql_persistencia_pedido_si_hay_conexion(): void
    {
        putenv('MASS_USE_DB=1');
        $_ENV['MASS_USE_DB'] = '1';
        Database::reset();

        if (!Database::connected()) {
            $this->markTestSkipped('MySQL no configurado (copia backend/config/database.local.php y importa schema/seed).');
        }

        $productos = (new ProductoRepository())->all();
        $this->assertNotEmpty($productos, 'Debe haber productos sembrados en mass_online');

        $codigo = 'MASS-TEST' . strtoupper(bin2hex(random_bytes(2)));
        $saved = (new PedidoRepository())->create([
            'codigo' => $codigo,
            'entrega' => 'retiro',
            'pago' => 'yape',
            'comprobante' => 'boleta',
            'nombre' => 'Test',
            'apellido' => 'Mass',
            'documento' => '12345678',
            'telefono' => '999888777',
            'direccion' => 'Av. Test 123',
            'referencia' => '',
            'tienda_id' => 'magdalena',
            'horario' => 'hoy_tarde',
            'cupon' => 'CLUBMASS10',
            'subtotal' => 10,
            'descuento' => 5,
            'envio' => 0,
            'total' => 5,
            'estado' => 'confirmado',
        ], [[
            'producto_id' => (int) $productos[0]['id'],
            'nombre' => (string) $productos[0]['nombre'],
            'precio_unit' => (float) $productos[0]['precio'],
            'cantidad' => 1,
            'subtotal' => (float) $productos[0]['precio'],
        ]]);

        $this->assertNotNull($saved);
        $found = (new PedidoRepository())->findByCodigo($codigo);
        $this->assertNotNull($found);
        $this->assertSame($codigo, $found['codigo']);
    }
}
