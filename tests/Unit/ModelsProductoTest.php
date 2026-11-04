<?php
declare(strict_types=1);

namespace Tests\Unit;

use Producto;
use Tests\TestCase;

final class ModelsProductoTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto_segun_schema(): void
    {
        $prod = new Producto(
            id: 1,
            nombre: 'Arroz Extra Costeño 750 g',
        );

        $this->assertSame(1, $prod->id);
        $this->assertSame('Arroz Extra Costeño 750 g', $prod->nombre);
        $this->assertSame('MASS', $prod->marca);
        $this->assertNull($prod->descripcion);
        $this->assertNull($prod->ean);
        $this->assertSame(0.0, $prod->precio);
        $this->assertNull($prod->precio_antes);
        $this->assertSame(0.0, $prod->costo);
        $this->assertSame('ok', $prod->stock_tone);
        $this->assertNull($prod->badge_text);
        $this->assertNull($prod->badge_tone);
        $this->assertFalse($prod->favorito);
        $this->assertSame(10, $prod->punto_reorden);
        $this->assertSame(50, $prod->capacidad_gondola);
        $this->assertSame('B', $prod->abc);
        $this->assertTrue($prod->activo);
        $this->assertNull($prod->created_at);
        $this->assertNull($prod->updated_at);
    }

    public function test_from_row_convierte_tipos_numericos_booleanos_y_campos_nulos(): void
    {
        $row = [
            'id' => '5',
            'nombre' => 'Aceite Vegetal Mass 900 ml',
            'marca' => 'MASS',
            'categoria' => 'Abarrotes básicos',
            'subcategoria' => 'Aceites',
            'unidad' => 'Botella 900 ml',
            'descripcion' => 'Aceite 100% vegetal.',
            'ean' => '7750100000051',
            'precio' => '7.50',
            'precio_antes' => '8.90',
            'costo' => '5.40',
            'stock_label' => 'En stock',
            'stock_tone' => 'ok',
            'badge_text' => 'Imbatible',
            'badge_tone' => 'yellow',
            'img' => 'assets/img/aceite.jpg',
            'img_nombre' => 'aceite.jpg',
            'favorito' => '1',
            'punto_reorden' => '15',
            'capacidad_gondola' => '60',
            'abc' => 'A',
            'activo' => '1',
            'created_at' => '2026-10-27 10:00:00',
            'updated_at' => null,
        ];

        $prod = Producto::fromRow($row);

        $this->assertSame(5, $prod->id);
        $this->assertSame('Aceite Vegetal Mass 900 ml', $prod->nombre);
        $this->assertSame('7750100000051', $prod->ean);
        $this->assertSame(7.50, $prod->precio);
        $this->assertSame(8.90, $prod->precio_antes);
        $this->assertSame(5.40, $prod->costo);
        $this->assertSame('Imbatible', $prod->badge_text);
        $this->assertSame('yellow', $prod->badge_tone);
        $this->assertTrue($prod->favorito);
        $this->assertSame(15, $prod->punto_reorden);
        $this->assertSame(60, $prod->capacidad_gondola);
        $this->assertSame('A', $prod->abc);
        $this->assertTrue($prod->activo);
        $this->assertSame('2026-10-27 10:00:00', $prod->created_at);
        $this->assertNull($prod->updated_at);
    }

    public function test_tratamiento_de_nulos_y_to_array(): void
    {
        $row = [
            'id' => 12,
            'nombre' => 'Azúcar Rubia Mass 1 kg',
            'marca' => 'MASS',
            'categoria' => 'Abarrotes básicos',
            'subcategoria' => 'Endulzantes',
            'unidad' => 'Bolsa 1 kg',
            'descripcion' => null,
            'ean' => null,
            'precio' => 4.20,
            'precio_antes' => null,
            'costo' => 3.10,
            'stock_label' => 'Pocas unidades',
            'stock_tone' => 'low',
            'badge_text' => null,
            'badge_tone' => null,
            'img' => '',
            'img_nombre' => '',
            'favorito' => 0,
            'punto_reorden' => 12,
            'capacidad_gondola' => 40,
            'abc' => 'B',
            'activo' => 0,
            'created_at' => '2026-10-27 12:00:00',
            'updated_at' => '2026-10-28 08:30:00',
        ];

        $prod = Producto::fromRow($row);
        $arr = $prod->toArray();

        $this->assertNull($arr['descripcion']);
        $this->assertNull($arr['ean']);
        $this->assertNull($arr['precio_antes']);
        $this->assertNull($arr['badge_text']);
        $this->assertNull($arr['badge_tone']);
        $this->assertFalse($arr['favorito']);
        $this->assertFalse($arr['activo']);
        $this->assertSame('2026-10-28 08:30:00', $arr['updated_at']);
    }
}

