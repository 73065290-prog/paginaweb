<?php
declare(strict_types=1);

namespace Tests\Integration;

use PedidoRepository;

/**
 * PedidoRepository implementa create(pedido, lineas) en transacción y findByCodigo().
 * Se valida la persistencia de cabecera y detalle, el rollback ante errores y las FK.
 */
final class PedidoRepositoryIntegrationTest extends IntegrationTestCase
{
    private int $aceite;
    private int $arroz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->insertarTienda('magdalena', 'Tienda Magdalena');
        $this->aceite = $this->insertarProducto(['nombre' => 'Aceite Primor', 'precio' => 8.90]);
        $this->arroz = $this->insertarProducto(['nombre' => 'Arroz Costeño', 'precio' => 22.90]);
    }

    /** @return array<string, mixed> */
    private function pedido(array $extra = []): array
    {
        return $extra + [
            'codigo' => 'MASS-IT-0001',
            'entrega' => 'express',
            'pago' => 'tarjeta',
            'comprobante' => 'factura',
            'nombre' => 'Ana',
            'apellido' => 'Torres',
            'documento' => '12345678',
            'telefono' => '999888777',
            'direccion' => 'Av. Arequipa 123',
            'referencia' => 'Frente al parque',
            'tienda_id' => 'magdalena',
            'horario' => 'hoy_tarde',
            'cupon' => 'CLUBMASS10',
            'subtotal' => 40.70,
            'descuento' => 5.00,
            'envio' => 4.90,
            'total' => 40.60,
            'estado' => 'confirmado',
        ];
    }

    /** @return list<array<string, mixed>> */
    private function lineas(): array
    {
        return [
            ['producto_id' => $this->aceite, 'nombre' => 'Aceite Primor', 'precio_unit' => 8.90, 'cantidad' => 2, 'subtotal' => 17.80],
            ['producto_id' => $this->arroz, 'nombre' => 'Arroz Costeño', 'precio_unit' => 22.90, 'cantidad' => 1, 'subtotal' => 22.90],
        ];
    }

    public function test_create_persiste_cabecera_y_lineas_en_transaccion(): void
    {
        $guardado = (new PedidoRepository())->create($this->pedido(), $this->lineas());

        $this->assertNotNull($guardado);
        $this->assertGreaterThan(0, $guardado['id']);
        $this->assertSame('MASS-IT-0001', $guardado['codigo']);
        $this->assertSame(1, $this->contar('pedidos'));
        $this->assertSame(2, $this->contar('pedido_detalles', 'pedido_id = ?', [$guardado['id']]));

        $detalles = $this->pdoPrueba()->query('SELECT producto_id, nombre, precio_unit, cantidad, subtotal FROM pedido_detalles ORDER BY id')->fetchAll();
        $this->assertSame([$this->aceite, $this->arroz], array_column($detalles, 'producto_id'));
        $this->assertSame(['17.80', '22.90'], array_column($detalles, 'subtotal'));
        $this->assertSame([2, 1], array_column($detalles, 'cantidad'));
    }

    public function test_find_by_codigo_devuelve_la_fila_persistida(): void
    {
        $repo = new PedidoRepository();
        $guardado = $repo->create($this->pedido(), $this->lineas());

        $fila = $repo->findByCodigo('MASS-IT-0001');

        $this->assertNotNull($fila);
        $this->assertSame($guardado['id'], $fila['id']);
        $this->assertSame('express', $fila['entrega']);
        $this->assertSame('tarjeta', $fila['pago']);
        $this->assertSame('factura', $fila['comprobante']);
        $this->assertSame('magdalena', $fila['tienda_id']);
        $this->assertSame('CLUBMASS10', $fila['cupon']);
        $this->assertSame('40.70', $fila['subtotal']);
        $this->assertSame('5.00', $fila['descuento']);
        $this->assertSame('4.90', $fila['envio']);
        $this->assertSame('40.60', $fila['total']);
        $this->assertSame('confirmado', $fila['estado']);
        $this->assertNull($fila['usuario_id']);
        $this->assertNotEmpty($fila['created_at']);
        $this->assertNull($repo->findByCodigo('MASS-NO-EXISTE'));
    }

    public function test_create_con_valores_minimos_aplica_defaults_y_tienda_vacia_como_null(): void
    {
        $repo = new PedidoRepository();

        $guardado = $repo->create(['codigo' => 'MASS-IT-MIN', 'tienda_id' => ''], []);

        $this->assertNotNull($guardado);
        $fila = $repo->findByCodigo('MASS-IT-MIN');
        $this->assertSame('retiro', $fila['entrega']);
        $this->assertSame('yape', $fila['pago']);
        $this->assertSame('boleta', $fila['comprobante']);
        $this->assertNull($fila['tienda_id']);
        $this->assertSame('0.00', $fila['total']);
        $this->assertSame(0, $this->contar('pedido_detalles'));
    }

    public function test_conserva_comillas_apostrofes_y_caracteres_especiales(): void
    {
        $repo = new PedidoRepository();
        $codigo = "MASS-'\"%_\\-01";
        $datos = [
            'codigo' => $codigo,
            'nombre' => "José O'Connor",
            'apellido' => 'Núñez "El Rápido"',
            'direccion' => "Av. \"Los Álamos\" 123 \\ Dpto 4-B'); DROP TABLE pedidos; --",
            'referencia' => 'Portón 🚪 verde <br> & 50% _',
        ];
        $lineas = [['producto_id' => $this->aceite, 'nombre' => "Aceite \"Primor\" D'Oro", 'precio_unit' => 8.90, 'cantidad' => 1, 'subtotal' => 8.90]];

        $this->assertNotNull($repo->create($this->pedido($datos), $lineas));

        $fila = $repo->findByCodigo($codigo);
        foreach ($datos as $campo => $valor) {
            $this->assertSame($valor, $fila[$campo], $campo);
        }
        $this->assertSame("Aceite \"Primor\" D'Oro", $this->pdoPrueba()->query('SELECT nombre FROM pedido_detalles')->fetchColumn());
        $this->assertNull($repo->findByCodigo("' OR '1'='1"));
        $this->assertNull($repo->findByCodigo('MASS-%'));
        $this->assertSame(1, $this->contar('pedidos'));
    }

    public function test_linea_con_producto_inexistente_hace_rollback_completo(): void
    {
        $lineas = $this->lineas();
        $lineas[] = ['producto_id' => 999999, 'nombre' => 'Fantasma', 'precio_unit' => 1, 'cantidad' => 1, 'subtotal' => 1];

        $resultado = (new PedidoRepository())->create($this->pedido(), $lineas);

        $this->assertNull($resultado);
        $this->assertSame(0, $this->contar('pedidos'), 'La cabecera se revierte');
        $this->assertSame(0, $this->contar('pedido_detalles'), 'Las líneas válidas previas se revierten');
        $this->assertFalse($this->pdoPrueba()->inTransaction());
    }

    public function test_tienda_inexistente_y_codigo_duplicado_son_rechazados(): void
    {
        $repo = new PedidoRepository();

        $this->assertNull($repo->create($this->pedido(['codigo' => 'MASS-IT-FK', 'tienda_id' => 'no_existe']), $this->lineas()));
        $this->assertNull($repo->findByCodigo('MASS-IT-FK'));

        $this->assertNotNull($repo->create($this->pedido(), $this->lineas()));
        $this->assertNull($repo->create($this->pedido(['total' => 1.00]), $this->lineas()));

        $this->assertSame(1, $this->contar('pedidos'));
        $this->assertSame(2, $this->contar('pedido_detalles'));
        $this->assertSame('40.60', $repo->findByCodigo('MASS-IT-0001')['total']);
    }

    public function test_actualizar_y_eliminar_pedido_en_mysql(): void
    {
        $repo = new PedidoRepository();
        $guardado = $repo->create($this->pedido(), $this->lineas());

        $this->ejecutar('UPDATE pedidos SET estado = ? WHERE codigo = ?', ['entregado', 'MASS-IT-0001']);
        $this->assertSame('entregado', $repo->findByCodigo('MASS-IT-0001')['estado']);

        $this->assertSame(1, $this->ejecutar('DELETE FROM pedidos WHERE id = ?', [$guardado['id']]));
        $this->assertNull($repo->findByCodigo('MASS-IT-0001'));
        $this->assertSame(0, $this->contar('pedido_detalles'), 'ON DELETE CASCADE en pedido_detalles');
    }

    public function test_eliminar_usuario_o_tienda_deja_el_pedido_con_null(): void
    {
        $usuarioId = $this->insertar('usuarios', ['usuario' => 'cliente.it', 'password_hash' => 'x', 'rol_id' => 2, 'nombre' => 'Cliente']);
        $guardado = (new PedidoRepository())->create($this->pedido(), $this->lineas());
        $this->ejecutar('UPDATE pedidos SET usuario_id = ? WHERE id = ?', [$usuarioId, $guardado['id']]);

        $this->ejecutar('DELETE FROM usuarios WHERE id = ?', [$usuarioId]);
        $this->ejecutar('DELETE FROM tiendas WHERE id = ?', ['magdalena']);

        $fila = (new PedidoRepository())->findByCodigo('MASS-IT-0001');
        $this->assertNull($fila['usuario_id'], 'ON DELETE SET NULL (usuario)');
        $this->assertNull($fila['tienda_id'], 'ON DELETE SET NULL (tienda)');
    }

    public function test_sin_conexion_no_persiste_ni_consulta(): void
    {
        $this->desconectarBaseDeDatos();
        $repo = new PedidoRepository();

        $this->assertNull($repo->create($this->pedido(), $this->lineas()));
        $this->assertNull($repo->findByCodigo('MASS-IT-0001'));
        $this->assertSame(0, $this->contar('pedidos'));
    }
}
