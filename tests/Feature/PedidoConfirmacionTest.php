<?php
declare(strict_types=1);

namespace Tests\Feature;

use PDO;
use PedidoService;
use RuntimeException;
use Tests\TestCase;

final class PedidoConfirmacionTest extends TestCase
{
    /** // P26 */
    public function test_confirmar_pedido_express_guarda_orden_y_vacia_carrito(): void
    {
        $service = new PedidoService();
        carrito_agregar(1, 2);
        $horario = array_key_first($service->horariosEntrega('express')) ?: 'hoy_tarde';
        $valid = $service->validarEntrega([
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
        $service->set($valid['data'] + ['cupon' => 'CLUBMASS10']);
        $orden = $service->confirmar('tarjeta');
        $this->assertIsArray($orden);
        $this->assertStringStartsWith('MASS-', (string) ($orden['codigo'] ?? ''));
        $this->assertSame([], carrito_items());
        $this->assertNotNull($service->ultimo());
        $this->assertSame('express', $orden['pedido']['entrega'] ?? null);
        $this->assertSame(4.9, (float) ($orden['resumen']['envio'] ?? -1));
    }

    /** // P27 */
    public function test_confirmar_retiro_tiene_envio_cero(): void
    {
        $service = new PedidoService();
        carrito_agregar(2, 1);
        $horario = array_key_first($service->horariosEntrega('retiro')) ?: 'hoy_tarde';
        $valid = $service->validarEntrega([
            'entrega' => 'retiro',
            'nombre' => 'Carlos',
            'apellido' => 'Diaz',
            'documento' => '11223344',
            'telefono' => '911222333',
            'horario' => $horario,
            'tienda_id' => 'magdalena',
        ]);
        $this->assertTrue($valid['ok'], implode(' · ', $valid['errores']));
        $service->set($valid['data'] + ['cupon' => '']);
        $orden = $service->confirmar('yape');
        $this->assertIsArray($orden);
        $this->assertSame('retiro', $orden['pedido']['entrega'] ?? null);
        $this->assertSame(0.0, (float) ($orden['resumen']['envio'] ?? -1));
    }

    public function test_confirmar_persiste_en_transaccion_pdo_y_hace_rollback_ante_error(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->assertTrue(true);
            return;
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo TEXT NOT NULL,
            entrega TEXT NOT NULL,
            pago TEXT NOT NULL,
            comprobante TEXT NOT NULL,
            nombre TEXT NOT NULL,
            apellido TEXT NOT NULL,
            documento TEXT NOT NULL,
            telefono TEXT NOT NULL,
            direccion TEXT NOT NULL,
            referencia TEXT NOT NULL,
            tienda_id TEXT NOT NULL,
            horario TEXT NOT NULL,
            cupon TEXT NOT NULL,
            subtotal REAL NOT NULL,
            descuento REAL NOT NULL,
            envio REAL NOT NULL,
            total REAL NOT NULL,
            estado TEXT NOT NULL
        )');
        $pdo->exec('CREATE TABLE pedido_detalles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            producto_id INTEGER NOT NULL,
            nombre TEXT NOT NULL,
            precio_unit REAL NOT NULL,
            cantidad INTEGER NOT NULL,
            subtotal REAL NOT NULL
        )');

        $service = new PedidoService(null, null, null, null, $pdo);
        carrito_agregar(1, 2);
        carrito_agregar(2, 1);
        $horario = array_key_first($service->horariosEntrega('express')) ?: 'hoy_express';
        $valid = $service->validarEntrega([
            'entrega' => 'express',
            'nombre' => 'Lucia',
            'apellido' => 'Gomez',
            'documento' => '76543210',
            'telefono' => '988776655',
            'horario' => $horario,
            'direccion' => 'Av. Javier Prado 1500, San Isidro',
            'tienda_id' => 'magdalena',
        ]);
        $this->assertTrue($valid['ok']);
        $service->set($valid['data'] + ['cupon' => 'CLUBMASS10']);

        $orden = $service->confirmar('tarjeta');
        $this->assertIsArray($orden);
        $this->assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn());
        $this->assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM pedido_detalles')->fetchColumn());

        // Simular fallo al insertar detalles (tabla eliminada) -> debe ejecutar rollBack() de pedidos y retornar null
        $pdo->exec('DROP TABLE pedido_detalles');
        $resFallo = $service->persistirConTransaccion($pdo, [
            'codigo' => 'MASS-FAIL01',
            'entrega' => 'express',
            'pago' => 'yape',
            'comprobante' => 'boleta',
            'nombre' => 'Lucia',
            'apellido' => 'Gomez',
            'documento' => '76543210',
            'telefono' => '988776655',
            'direccion' => 'Av. Javier Prado 1500, San Isidro',
            'referencia' => '',
            'tienda_id' => 'magdalena',
            'horario' => $horario,
            'cupon' => '',
            'subtotal' => 20.0,
            'descuento' => 0.0,
            'envio' => 4.9,
            'total' => 24.9,
            'estado' => 'confirmado',
        ], [
            [
                'producto_id' => 1,
                'nombre' => 'Arroz Costeño',
                'precio_unit' => 20.0,
                'cantidad' => 1,
                'subtotal' => 20.0,
            ],
        ]);
        $this->assertNull($resFallo, 'Debe retornar null tras hacer rollBack de la transacción fallida');
        $this->assertFalse($pdo->inTransaction(), 'La transacción PDO no debe quedar abierta tras rollBack');
        $this->assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(), 'No debe persistir cabecera huérfana');
    }
}
