<?php
declare(strict_types=1);

namespace Tests\Unit;

use Categoria;
use Tests\TestCase;

final class ModelsCategoriaTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto(): void
    {
        $cat = new Categoria(
            id: 'abarrotes',
            nombre: 'Abarrotes básicos',
        );

        $this->assertSame('abarrotes', $cat->id);
        $this->assertSame('Abarrotes básicos', $cat->nombre);
        $this->assertSame('', $cat->badge);
        $this->assertSame('', $cat->badge_tone);
        $this->assertSame(0, $cat->count_hint);
        $this->assertSame('', $cat->cat_filtro);
        $this->assertSame('', $cat->img);
    }

    public function test_from_row_convierte_tipos_y_mapea_columnas_de_schema(): void
    {
        $row = [
            'id' => 'lacteos',
            'nombre' => 'Lácteos y huevos',
            'badge' => 'Frescos',
            'badge_tone' => 'green',
            'count_hint' => '24',
            'cat_filtro' => 'Lácteos y huevos',
            'img' => 'https://example.com/lacteos.jpg',
        ];

        $cat = Categoria::fromRow($row);

        $this->assertSame('lacteos', $cat->id);
        $this->assertSame('Lácteos y huevos', $cat->nombre);
        $this->assertSame('Frescos', $cat->badge);
        $this->assertSame('green', $cat->badge_tone);
        $this->assertIsInt($cat->count_hint);
        $this->assertSame(24, $cat->count_hint);
        $this->assertSame('Lácteos y huevos', $cat->cat_filtro);
        $this->assertSame('https://example.com/lacteos.jpg', $cat->img);
    }

    public function test_to_array_conserva_estructura_y_tipos(): void
    {
        $row = [
            'id' => 'bebidas',
            'nombre' => 'Bebidas y aguas',
            'badge' => 'Pack ahorro',
            'badge_tone' => 'blue',
            'count_hint' => 18,
            'cat_filtro' => 'Bebidas y aguas',
            'img' => 'assets/img/bebidas.png',
        ];

        $cat = Categoria::fromRow($row);
        $this->assertSame($row, $cat->toArray());
    }
}

