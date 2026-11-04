<?php
declare(strict_types=1);

namespace Tests\Integration;

use PDOException;
use ProductoRepository;

/**
 * ProductoRepository implementa all(), find(), create() y update(). No implementa borrado:
 * la eliminación se valida a nivel de MySQL (ON DELETE CASCADE / SET NULL) y su efecto en find().
 */
final class ProductoRepositoryIntegrationTest extends IntegrationTestCase
{
    /** @return array<string, mixed> */
    private function payload(array $extra = []): array
    {
        return $extra + [
            'nombre' => 'Aceite Vegetal Primor 900 ml',
            'marca' => 'PRIMOR',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'Aceites y Grasas',
            'unidad' => 'Botella 900 ml',
            'descripcion' => 'Aceite para cocina',
            'ean' => '7756123400001',
            'precio' => 8.90,
            'costo' => 6.05,
            'stockTone' => 'ok',
            'img' => 'https://img.test/aceite.jpg',
            'imgNombre' => 'primor_1.jpg',
            'puntoReorden' => 25,
            'capacidadGondola' => 200,
            'abc' => 'A',
            'activo' => true,
        ];
    }

    public function test_create_inserta_y_find_devuelve_valores_persistidos(): void
    {
        $repo = new ProductoRepository();

        $id = $repo->create($this->payload());

        $this->assertGreaterThan(0, $id);
        $this->assertSame(1, $this->contar('productos', 'id = ?', [$id]));
        $p = $repo->find($id);
        $this->assertNotNull($p);
        $this->assertSame($id, $p['id']);
        $this->assertSame('Aceite Vegetal Primor 900 ml', $p['nombre']);
        $this->assertSame('PRIMOR', $p['marca']);
        $this->assertSame('Abarrotes', $p['categoria']);
        $this->assertSame('Aceites y Grasas', $p['subcategoria']);
        $this->assertSame('Botella 900 ml', $p['unidad']);
        $this->assertSame('7756123400001', $p['ean']);
        $this->assertSame(8.90, $p['precio']);
        $this->assertSame(6.05, $p['costo']);
        $this->assertNull($p['precioAntes']);
        $this->assertNull($p['badge']);
        $this->assertSame('primor_1.jpg', $p['imgNombre']);
        $this->assertSame(25, $p['puntoReorden']);
        $this->assertSame(200, $p['capacidadGondola']);
        $this->assertSame('A', $p['abc']);
        $this->assertTrue($p['activo']);
        $this->assertFalse($p['favorito']);
        $this->assertSame('SKU-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT), $p['sku']);
    }

    public function test_create_con_solo_nombre_aplica_valores_por_defecto(): void
    {
        $repo = new ProductoRepository();

        $id = $repo->create(['nombre' => 'Producto mínimo']);
        $p = $repo->find($id);

        $this->assertSame('MASS', $p['marca']);
        $this->assertSame('', $p['ean']);
        $this->assertSame(0.0, $p['precio']);
        $this->assertSame('ok', $p['stockTone']);
        $this->assertSame(10, $p['puntoReorden']);
        $this->assertSame(50, $p['capacidadGondola']);
        $this->assertSame('B', $p['abc']);
        $this->assertFalse($p['activo'], 'Sin la clave activo el repositorio guarda activo = 0');
        $this->assertSame(1, $this->contar('productos', 'id = ? AND ean IS NULL', [$id]));
    }

    public function test_create_y_find_conservan_comillas_apostrofes_y_caracteres_especiales(): void
    {
        $nombre = "Galletas \"Soda\" D'Ángelo'); DROP TABLE productos; --";
        $descripcion = "Línea 1 \\n con barra invertida \\ y 50% _comodín_ — ñandú 😀 <b>html</b>";
        $repo = new ProductoRepository();

        $id = $repo->create($this->payload(['nombre' => $nombre, 'marca' => "O'HIGGINS", 'descripcion' => $descripcion, 'ean' => null]));
        $p = $repo->find($id);

        $this->assertSame($nombre, $p['nombre']);
        $this->assertSame("O'HIGGINS", $p['marca']);
        $this->assertSame($descripcion, $p['descripcion']);
        $this->assertSame(1, $this->contar('productos'), 'La tabla sigue existiendo y solo tiene la fila creada');
    }

    public function test_all_devuelve_todos_ordenados_por_id(): void
    {
        $repo = new ProductoRepository();
        $this->assertSame([], $repo->all());

        $a = $repo->create($this->payload(['nombre' => 'Zanahoria', 'ean' => '7750000000001']));
        $b = $repo->create($this->payload(['nombre' => 'Arroz', 'ean' => '7750000000002']));
        $c = $repo->create($this->payload(['nombre' => 'Mango', 'ean' => '7750000000003']));

        $rows = $repo->all();
        $this->assertSame([$a, $b, $c], array_column($rows, 'id'));
        $this->assertSame(['Zanahoria', 'Arroz', 'Mango'], array_column($rows, 'nombre'));
    }

    public function test_find_inexistente_devuelve_null(): void
    {
        $this->insertarProducto();

        $this->assertNull((new ProductoRepository())->find(999999));
        $this->assertNull((new ProductoRepository())->find(0));
    }

    public function test_find_mapea_badge_precio_antes_y_favorito(): void
    {
        $id = $this->insertarProducto([
            'precio_antes' => 11.50, 'badge_text' => "-22% \"OFERTA\"", 'badge_tone' => 'oferta',
            'favorito' => 1, 'stock_label' => "¡Bajo stock! Solo 6 mallas", 'stock_tone' => 'bajo',
        ]);

        $p = (new ProductoRepository())->find($id);

        $this->assertSame(11.50, $p['precioAntes']);
        $this->assertSame(['text' => "-22% \"OFERTA\"", 'tone' => 'oferta'], $p['badge']);
        $this->assertTrue($p['favorito']);
        $this->assertSame('¡Bajo stock! Solo 6 mallas', $p['stockLabel']);
        $this->assertSame('bajo', $p['stockTone']);
    }

    public function test_update_modifica_campos_y_conserva_los_no_editables(): void
    {
        $repo = new ProductoRepository();
        $id = $repo->create($this->payload(['stockTone' => 'bajo']));

        $ok = $repo->update($id, $this->payload([
            'nombre' => "Aceite \"Premium\" O'Brien",
            'precio' => 9.40,
            'costo' => 6.20,
            'abc' => 'C',
            'puntoReorden' => 30,
            'capacidadGondola' => 150,
            'ean' => '7756123400099',
            'activo' => false,
            'stockTone' => 'ok',
            'img' => 'otra.jpg',
        ]));

        $this->assertTrue($ok);
        $p = $repo->find($id);
        $this->assertSame("Aceite \"Premium\" O'Brien", $p['nombre']);
        $this->assertSame(9.40, $p['precio']);
        $this->assertSame(6.20, $p['costo']);
        $this->assertSame('C', $p['abc']);
        $this->assertSame(30, $p['puntoReorden']);
        $this->assertSame(150, $p['capacidadGondola']);
        $this->assertSame('7756123400099', $p['ean']);
        $this->assertFalse($p['activo']);
        $this->assertSame('bajo', $p['stockTone'], 'update() no modifica stock_tone');
        $this->assertSame('https://img.test/aceite.jpg', $p['img'], 'update() no modifica img');
        $this->assertNotNull(
            $this->pdoPrueba()->query("SELECT updated_at FROM productos WHERE id = {$id}")->fetchColumn(),
            'ON UPDATE CURRENT_TIMESTAMP registra la modificación'
        );
    }

    public function test_update_de_id_inexistente_no_crea_ni_modifica_filas(): void
    {
        $repo = new ProductoRepository();
        $id = $repo->create($this->payload());

        $this->assertTrue($repo->update(999999, $this->payload(['nombre' => 'Fantasma', 'ean' => '7750000009999'])));

        $this->assertSame(1, $this->contar('productos'));
        $this->assertNull($repo->find(999999));
        $this->assertSame('Aceite Vegetal Primor 900 ml', $repo->find($id)['nombre']);
    }

    public function test_ean_duplicado_viola_unique_en_create_y_update(): void
    {
        $repo = new ProductoRepository();
        $repo->create($this->payload(['ean' => '7750000000111']));
        $otro = $repo->create($this->payload(['nombre' => 'Otro', 'ean' => '7750000000222']));

        try {
            $repo->create($this->payload(['nombre' => 'Duplicado', 'ean' => '7750000000111']));
            $this->fail('Se esperaba violación de uq_productos_ean en create()');
        } catch (PDOException $e) {
            $this->assertSame(1062, $e->errorInfo[1]);
        }
        try {
            $repo->update($otro, $this->payload(['nombre' => 'Otro', 'ean' => '7750000000111']));
            $this->fail('Se esperaba violación de uq_productos_ean en update()');
        } catch (PDOException $e) {
            $this->assertSame(1062, $e->errorInfo[1]);
        }

        $this->assertSame(2, $this->contar('productos'));
        $this->assertSame('7750000000222', $repo->find($otro)['ean']);
    }

    public function test_eliminar_producto_propaga_cascade_y_set_null(): void
    {
        $repo = new ProductoRepository();
        $id = $repo->create($this->payload());
        $this->insertarTienda('magdalena');
        $this->insertar('inventario', ['producto_id' => $id, 'tienda_id' => 'magdalena', 'stock_piso' => 5]);
        $pedidoId = $this->insertar('pedidos', ['codigo' => 'MASS-DEL-1']);
        $this->insertar('pedido_detalles', ['pedido_id' => $pedidoId, 'producto_id' => $id, 'nombre' => 'Aceite']);

        $this->assertSame(1, $this->ejecutar('DELETE FROM productos WHERE id = ?', [$id]));

        $this->assertNull($repo->find($id));
        $this->assertSame(0, $this->contar('inventario', 'producto_id = ?', [$id]), 'ON DELETE CASCADE en inventario');
        $this->assertSame(1, $this->contar('pedido_detalles', 'pedido_id = ? AND producto_id IS NULL', [$pedidoId]), 'ON DELETE SET NULL en pedido_detalles');
    }

    public function test_sin_conexion_devuelve_valores_neutros(): void
    {
        $id = $this->insertarProducto();
        $this->desconectarBaseDeDatos();
        $repo = new ProductoRepository();

        $this->assertSame([], $repo->all());
        $this->assertNull($repo->find($id));
        $this->assertSame(0, $repo->create($this->payload()));
        $this->assertFalse($repo->update($id, $this->payload()));
    }
}
