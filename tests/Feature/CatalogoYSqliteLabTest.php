<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\SqliteLabStore;
use Tests\TestCase;

final class CatalogoYSqliteLabTest extends TestCase
{
    /** // P31 */
    public function test_filtrar_productos_por_categoria_y_busqueda(): void
    {
        // Misma semántica que catalogo.php: por defecto ambos tonos de stock activos
        $lacteos = filtrar_productos([
            'cat' => 'Lácteos',
            'stock_ok' => true,
            'stock_bajo' => true,
        ]);
        $this->assertNotEmpty($lacteos);
        foreach ($lacteos as $p) {
            $this->assertSame('Lácteos', $p['categoria']);
        }

        $q = filtrar_productos([
            'q' => 'primor',
            'stock_ok' => true,
            'stock_bajo' => true,
        ]);
        $this->assertNotEmpty($q);
        $hayPrimor = false;
        foreach ($q as $p) {
            if (str_contains(mb_strtolower((string) $p['nombre']), 'primor')
                || str_contains(mb_strtolower((string) $p['marca']), 'primor')) {
                $hayPrimor = true;
                break;
            }
        }
        $this->assertTrue($hayPrimor);
    }

    /** // P32 */
    public function test_sqlite_laboratorio_guarda_y_lee_en_memoria_aislada(): void
    {
        $lab = new SqliteLabStore();
        $id = $lab->guardarLinea(1, 4);
        $this->assertGreaterThan(0, $id);
        $fila = $lab->leerLinea($id);
        $this->assertNotNull($fila);
        $this->assertSame(1, $fila['producto_id']);
        $this->assertSame(4, $fila['cantidad']);
        $this->assertSame(4, $lab->totalUnidades());
    }
}
