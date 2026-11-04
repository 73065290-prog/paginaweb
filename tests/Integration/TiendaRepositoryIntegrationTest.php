<?php
declare(strict_types=1);

namespace Tests\Integration;

use TiendaRepository;

/**
 * TiendaRepository implementa all() y find(id): lectura real, mapeo de columnas derivadas
 * (distancia, estadoLabel, misma) y reflejo de cambios en MySQL.
 */
final class TiendaRepositoryIntegrationTest extends IntegrationTestCase
{
    public function test_all_mapea_columnas_y_campos_derivados(): void
    {
        $this->insertar('tiendas', [
            'id' => 'magdalena', 'nombre' => 'Tienda Magdalena', 'direccion' => 'Av. Sucre 420',
            'distrito' => 'Magdalena del Mar', 'stock' => 42, 'eta' => '0.0 km', 'estado' => 'actual', 'img' => 'm.jpg',
        ]);
        $this->insertar('tiendas', ['id' => 'brena', 'nombre' => 'Tienda Breña', 'stock' => 0, 'estado' => 'agotado']);

        $rows = (new TiendaRepository())->all();
        $porId = array_column($rows, null, 'id');

        $this->assertCount(2, $rows);
        $this->assertSame([
            'id' => 'magdalena',
            'nombre' => 'Tienda Magdalena',
            'direccion' => 'Av. Sucre 420',
            'distrito' => 'Magdalena del Mar',
            'stock' => 42,
            'eta' => '0.0 km',
            'estado' => 'actual',
            'img' => 'm.jpg',
            'distancia' => '0.0 km',
            'estadoLabel' => 'actual',
            'misma' => true,
        ], $porId['magdalena']);
        $this->assertFalse($porId['brena']['misma']);
        $this->assertSame(0, $porId['brena']['stock']);
        $this->assertSame('agotado', $porId['brena']['estadoLabel']);
    }

    public function test_find_por_id_existente_e_inexistente(): void
    {
        $this->insertarTienda('san_miguel', 'Tienda San Miguel');
        $repo = new TiendaRepository();

        $this->assertSame('Tienda San Miguel', $repo->find('san_miguel')['nombre'] ?? null);
        $this->assertNull($repo->find('no_existe'));
        $this->assertNull($repo->find(''));
    }

    public function test_conserva_comillas_apostrofes_y_caracteres_especiales(): void
    {
        $id = "o'higgins\"_%";
        $direccion = "Jr. O'Higgins \"Cdra. 5\" \\ Of. 3; DELETE FROM tiendas; -- ñ ü 🏪";
        $this->insertar('tiendas', ['id' => $id, 'nombre' => "Tienda O'Higgins", 'direccion' => $direccion]);

        $tienda = (new TiendaRepository())->find($id);

        $this->assertNotNull($tienda);
        $this->assertSame($direccion, $tienda['direccion']);
        $this->assertSame("Tienda O'Higgins", $tienda['nombre']);
        $this->assertNull((new TiendaRepository())->find("' OR '1'='1"));
        $this->assertSame(1, $this->contar('tiendas'));
    }

    public function test_refleja_actualizacion_y_eliminacion_en_mysql(): void
    {
        $this->insertarTienda('pueblo_libre', 'Tienda Pueblo Libre');
        $repo = new TiendaRepository();

        $this->ejecutar('UPDATE tiendas SET stock = ?, estado = ? WHERE id = ?', [4, 'bajo', 'pueblo_libre']);
        $this->assertSame(4, $repo->find('pueblo_libre')['stock']);
        $this->assertSame('bajo', $repo->find('pueblo_libre')['estado']);

        $this->ejecutar('DELETE FROM tiendas WHERE id = ?', ['pueblo_libre']);
        $this->assertNull($repo->find('pueblo_libre'));
        $this->assertSame([], $repo->all());
    }

    public function test_sin_conexion_devuelve_vacio_y_null(): void
    {
        $this->insertarTienda('magdalena');
        $this->desconectarBaseDeDatos();
        $repo = new TiendaRepository();

        $this->assertSame([], $repo->all());
        $this->assertNull($repo->find('magdalena'));
    }
}
