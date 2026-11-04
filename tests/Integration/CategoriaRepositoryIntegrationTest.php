<?php
declare(strict_types=1);

namespace Tests\Integration;

use CategoriaRepository;

/**
 * CategoriaRepository implementa solo all(): se valida la lectura real, el mapeo de columnas
 * y que refleje altas, cambios y bajas hechas en MySQL.
 */
final class CategoriaRepositoryIntegrationTest extends IntegrationTestCase
{
    public function test_all_sin_registros_devuelve_lista_vacia(): void
    {
        $this->assertSame([], (new CategoriaRepository())->all());
    }

    public function test_all_mapea_columnas_y_ordena_por_nombre(): void
    {
        $this->insertar('categorias', [
            'id' => 'panaderia', 'nombre' => 'Panadería', 'badge' => 'Diario', 'badge_tone' => 'o',
            'count_hint' => 12, 'cat_filtro' => 'Abarrotes', 'img' => 'https://img.test/pan.jpg',
        ]);
        $this->insertar('categorias', ['id' => 'bebidas', 'nombre' => 'Bebidas', 'count_hint' => 3]);

        $rows = (new CategoriaRepository())->all();

        $this->assertSame(['Bebidas', 'Panadería'], array_column($rows, 'nombre'));
        $this->assertSame([
            'id' => 'panaderia',
            'nombre' => 'Panadería',
            'badge' => 'Diario',
            'badgeTone' => 'o',
            'count' => 12,
            'cat' => 'Abarrotes',
            'img' => 'https://img.test/pan.jpg',
        ], $rows[1]);
        $this->assertSame(['id' => 'bebidas', 'nombre' => 'Bebidas', 'badge' => '', 'badgeTone' => '', 'count' => 3, 'cat' => '', 'img' => ''], $rows[0]);
    }

    public function test_conserva_comillas_apostrofes_y_caracteres_especiales(): void
    {
        $nombre = "D'Onofrio \"Helados\" 100% <frío> \\ ñandú 🍦'; DROP TABLE categorias; --";
        $this->insertar('categorias', ['id' => "cat'esp", 'nombre' => $nombre, 'badge' => "O'k", 'cat_filtro' => 'Snacks_%']);

        $rows = (new CategoriaRepository())->all();

        $this->assertCount(1, $rows);
        $this->assertSame("cat'esp", $rows[0]['id']);
        $this->assertSame($nombre, $rows[0]['nombre']);
        $this->assertSame("O'k", $rows[0]['badge']);
        $this->assertSame('Snacks_%', $rows[0]['cat']);
        $this->assertSame(1, $this->contar('categorias'));
    }

    public function test_refleja_actualizacion_y_eliminacion_en_mysql(): void
    {
        $this->insertar('categorias', ['id' => 'lacteos', 'nombre' => 'Lácteos', 'count_hint' => 5]);
        $this->insertar('categorias', ['id' => 'frutas', 'nombre' => 'Frutas']);
        $repo = new CategoriaRepository();

        $this->ejecutar('UPDATE categorias SET nombre = ?, count_hint = ? WHERE id = ?', ['Lácteos y Huevos', 9, 'lacteos']);
        $actualizada = array_values(array_filter($repo->all(), static fn (array $c): bool => $c['id'] === 'lacteos'))[0];
        $this->assertSame('Lácteos y Huevos', $actualizada['nombre']);
        $this->assertSame(9, $actualizada['count']);

        $this->assertSame(1, $this->ejecutar('DELETE FROM categorias WHERE id = ?', ['frutas']));
        $this->assertSame(['lacteos'], array_column($repo->all(), 'id'));
    }

    public function test_clave_primaria_impide_ids_duplicados(): void
    {
        $this->insertar('categorias', ['id' => 'bebidas', 'nombre' => 'Bebidas']);

        try {
            $this->insertar('categorias', ['id' => 'bebidas', 'nombre' => 'Duplicada']);
            $this->fail('Se esperaba violación de PRIMARY KEY');
        } catch (\PDOException $e) {
            $this->assertSame(1062, $e->errorInfo[1]);
        }
        $this->assertSame(['Bebidas'], array_column((new CategoriaRepository())->all(), 'nombre'));
    }

    public function test_sin_conexion_devuelve_lista_vacia(): void
    {
        $this->insertar('categorias', ['id' => 'bebidas', 'nombre' => 'Bebidas']);
        $this->desconectarBaseDeDatos();

        $this->assertSame([], (new CategoriaRepository())->all());
    }
}
