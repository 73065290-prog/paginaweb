<?php
declare(strict_types=1);

namespace Tests\Integration;

use InventarioRepository;
use PDOException;

/**
 * InventarioRepository implementa byTienda() y updateStock(). Las altas y bajas de inventario
 * se preparan con SQL y se validan sus restricciones (FK, UNIQUE, CASCADE) en MySQL.
 */
final class InventarioRepositoryIntegrationTest extends IntegrationTestCase
{
    private int $arroz;
    private int $aceite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->insertarTienda('magdalena', 'Tienda Magdalena');
        $this->insertarTienda('brena', 'Tienda Breña');
        $this->arroz = $this->insertarProducto(['nombre' => 'Arroz Costeño', 'marca' => 'COSTEÑO']);
        $this->aceite = $this->insertarProducto(['nombre' => 'Aceite Primor', 'marca' => 'PRIMOR']);
        $this->insertar('inventario', ['producto_id' => $this->arroz, 'tienda_id' => 'magdalena', 'stock_piso' => 42, 'stock_almacen' => 24]);
        $this->insertar('inventario', ['producto_id' => $this->aceite, 'tienda_id' => 'magdalena', 'stock_piso' => 8, 'stock_almacen' => 4]);
        $this->insertar('inventario', ['producto_id' => $this->arroz, 'tienda_id' => 'brena', 'stock_piso' => 0, 'stock_almacen' => 0]);
    }

    public function test_by_tienda_filtra_por_tienda_une_producto_y_ordena_por_nombre(): void
    {
        $rows = (new InventarioRepository())->byTienda('magdalena');

        $this->assertSame([
            ['producto_id' => $this->aceite, 'tienda_id' => 'magdalena', 'stock_piso' => 8, 'stock_almacen' => 4, 'nombre' => 'Aceite Primor', 'marca' => 'PRIMOR'],
            ['producto_id' => $this->arroz, 'tienda_id' => 'magdalena', 'stock_piso' => 42, 'stock_almacen' => 24, 'nombre' => 'Arroz Costeño', 'marca' => 'COSTEÑO'],
        ], $rows);
        $this->assertCount(1, (new InventarioRepository())->byTienda('brena'));
        $this->assertSame([], (new InventarioRepository())->byTienda('no_existe'));
    }

    public function test_by_tienda_con_comillas_y_caracteres_especiales(): void
    {
        $tienda = "o'higgins \"centro\" %_\\";
        $this->insertarTienda($tienda);
        $prod = $this->insertarProducto(['nombre' => "Café \"Altomayo\" D'Oro 😀"]);
        $this->insertar('inventario', ['producto_id' => $prod, 'tienda_id' => $tienda, 'stock_piso' => 3]);
        $repo = new InventarioRepository();

        $rows = $repo->byTienda($tienda);

        $this->assertCount(1, $rows);
        $this->assertSame("Café \"Altomayo\" D'Oro 😀", $rows[0]['nombre']);
        $this->assertSame([], $repo->byTienda("magdalena' OR '1'='1"));
        $this->assertSame([], $repo->byTienda('%'));
        $this->assertSame(4, $this->contar('inventario'));
    }

    public function test_update_stock_actualiza_solo_la_fila_indicada(): void
    {
        $repo = new InventarioRepository();

        $this->assertTrue($repo->updateStock($this->arroz, 'magdalena', 15, 60));

        $this->assertSame(1, $this->contar('inventario', 'producto_id = ? AND tienda_id = ? AND stock_piso = 15 AND stock_almacen = 60', [$this->arroz, 'magdalena']));
        $this->assertSame(1, $this->contar('inventario', 'producto_id = ? AND tienda_id = ? AND stock_piso = 0', [$this->arroz, 'brena']), 'La misma SKU en otra tienda no cambia');
        $this->assertSame(1, $this->contar('inventario', 'producto_id = ? AND stock_piso = 8', [$this->aceite]));
        $porId = array_column($repo->byTienda('magdalena'), null, 'producto_id');
        $this->assertSame(15, $porId[$this->arroz]['stock_piso']);
        $this->assertSame(60, $porId[$this->arroz]['stock_almacen']);
    }

    public function test_update_stock_de_combinacion_inexistente_no_crea_filas(): void
    {
        $repo = new InventarioRepository();

        $this->assertTrue($repo->updateStock($this->aceite, 'brena', 99, 99));
        $this->assertTrue($repo->updateStock(999999, "x'; DELETE FROM inventario; --", 1, 1));

        $this->assertSame(3, $this->contar('inventario'));
        $this->assertSame(0, $this->contar('inventario', 'stock_piso = 99 OR stock_piso = 1'));
    }

    public function test_restricciones_fk_y_unique_del_inventario(): void
    {
        try {
            $this->insertar('inventario', ['producto_id' => 999999, 'tienda_id' => 'magdalena']);
            $this->fail('Se esperaba violación de fk_inventario_producto');
        } catch (PDOException $e) {
            $this->assertSame(1452, $e->errorInfo[1]);
        }
        try {
            $this->insertar('inventario', ['producto_id' => $this->arroz, 'tienda_id' => 'no_existe']);
            $this->fail('Se esperaba violación de fk_inventario_tienda');
        } catch (PDOException $e) {
            $this->assertSame(1452, $e->errorInfo[1]);
        }
        try {
            $this->insertar('inventario', ['producto_id' => $this->arroz, 'tienda_id' => 'magdalena']);
            $this->fail('Se esperaba violación de uq_inventario_prod_tienda');
        } catch (PDOException $e) {
            $this->assertSame(1062, $e->errorInfo[1]);
        }
        $this->assertSame(3, $this->contar('inventario'));
    }

    public function test_eliminar_tienda_elimina_su_inventario_en_cascada(): void
    {
        $this->assertSame(1, $this->ejecutar('DELETE FROM tiendas WHERE id = ?', ['brena']));

        $this->assertSame([], (new InventarioRepository())->byTienda('brena'));
        $this->assertCount(2, (new InventarioRepository())->byTienda('magdalena'));
        $this->assertSame(2, $this->contar('inventario'));
    }

    public function test_sin_conexion_devuelve_valores_neutros(): void
    {
        $this->desconectarBaseDeDatos();
        $repo = new InventarioRepository();

        $this->assertSame([], $repo->byTienda('magdalena'));
        $this->assertFalse($repo->updateStock($this->arroz, 'magdalena', 1, 1));
        $this->assertSame(1, $this->contar('inventario', 'producto_id = ? AND tienda_id = ? AND stock_piso = 42', [$this->arroz, 'magdalena']));
    }
}
