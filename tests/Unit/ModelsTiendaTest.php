<?php
declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Tienda;

final class ModelsTiendaTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto(): void
    {
        $tienda = new Tienda(
            id: 'magdalena',
            nombre: 'Mass Magdalena - Av. Brasil',
        );

        $this->assertSame('magdalena', $tienda->id);
        $this->assertSame('Mass Magdalena - Av. Brasil', $tienda->nombre);
        $this->assertSame('', $tienda->direccion);
        $this->assertSame('', $tienda->distrito);
        $this->assertSame(0, $tienda->stock);
        $this->assertSame('', $tienda->eta);
        $this->assertSame('ok', $tienda->estado);
        $this->assertSame('', $tienda->img);
    }

    public function test_from_row_y_to_array_conservan_tipos(): void
    {
        $row = [
            'id' => 'miraflores',
            'nombre' => 'Mass Miraflores - Schell',
            'direccion' => 'Calle Schell 340',
            'distrito' => 'Miraflores',
            'stock' => '42',
            'eta' => 'Listo en 20 min',
            'estado' => 'ok',
            'img' => 'assets/img/tienda-miraflores.jpg',
        ];

        $tienda = Tienda::fromRow($row);
        $arr = $tienda->toArray();

        $this->assertIsInt($tienda->stock);
        $this->assertSame(42, $tienda->stock);
        $this->assertSame('Miraflores', $arr['distrito']);
        $this->assertSame(42, $arr['stock']);
        $this->assertSame('ok', $arr['estado']);
    }
}

