<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ProductoAdminPersistenciaTest extends TestCase
{
    /** // P28 */
    public function test_crear_producto_valido_persiste_en_sesion_admin(): void
    {
        $antes = count(mass_admin_productos());
        $r = mass_admin_producto_crear([
            'nombre' => 'Snack QA Automatizado',
            'ean' => '7756123491111',
            'unidad' => 'Bolsa',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'Snacks',
            'descripcion' => 'Snack creado por suite automatizada PHPUnit.',
            'precio' => 4.5,
            'costo' => 2.0,
            'puntoReorden' => 10,
            'capacidadGondola' => 80,
            'abc' => 'C',
            'activo' => 1,
        ]);
        $this->assertTrue($r['ok'], implode(' · ', $r['errores']));
        $this->assertSame($antes + 1, count(mass_admin_productos()));
        $nuevo = mass_admin_producto_por_id($r['id']);
        $this->assertNotNull($nuevo);
        $this->assertSame('Snack QA Automatizado', $nuevo['nombre']);
        $this->assertTrue(!empty($nuevo['esNuevo']));
    }

    /** // P29 */
    public function test_crear_producto_vacio_no_persiste(): void
    {
        $antes = count(mass_admin_productos());
        $r = mass_admin_producto_crear([
            'nombre' => '',
            'ean' => '',
            'unidad' => '',
            'categoria' => '',
            'subcategoria' => '',
            'descripcion' => '',
            'precio' => 0,
            'costo' => 0,
            'puntoReorden' => 0,
            'capacidadGondola' => 0,
            'abc' => '',
        ]);
        $this->assertFalse($r['ok']);
        $this->assertSame($antes, count(mass_admin_productos()));
    }

    /** // P30 */
    public function test_guardar_edicion_actualiza_override_en_sesion(): void
    {
        $r = mass_admin_producto_guardar(1, [
            'nombre' => 'Aceite Primor Editado QA',
            'ean' => '7756123480001',
            'unidad' => 'Botella 900ml',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'Aceites',
            'descripcion' => 'Edición validada por prueba de regresión.',
            'precio' => 8.5,
            'costo' => 5.0,
            'puntoReorden' => 15,
            'capacidadGondola' => 120,
            'abc' => 'A',
            'activo' => 1,
        ]);
        $this->assertTrue($r['ok'], implode(' · ', $r['errores']));
        $p = mass_admin_producto_por_id(1);
        $this->assertNotNull($p);
        $this->assertSame('Aceite Primor Editado QA', $p['nombre']);
        $this->assertSame(8.5, (float) $p['precio']);
    }
}
