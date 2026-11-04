<?php
declare(strict_types=1);

namespace Tests\Unit;

use Cupon;
use Tests\TestCase;

final class ModelsCuponTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto(): void
    {
        $cupon = new Cupon(codigo: 'CLUBMASS10');

        $this->assertSame('CLUBMASS10', $cupon->codigo);
        $this->assertSame(0.0, $cupon->descuento_fijo);
        $this->assertTrue($cupon->activo);
    }

    public function test_from_row_y_to_array_con_tipos_esperados(): void
    {
        $row = [
            'codigo' => 'CLUBMASS10',
            'descuento_fijo' => '10.00',
            'activo' => '1',
        ];

        $cupon = Cupon::fromRow($row);
        $arr = $cupon->toArray();

        $this->assertSame('CLUBMASS10', $cupon->codigo);
        $this->assertIsFloat($cupon->descuento_fijo);
        $this->assertSame(10.0, $cupon->descuento_fijo);
        $this->assertTrue($cupon->activo);
        $this->assertSame(10.0, $arr['descuento_fijo']);
        $this->assertTrue($arr['activo']);

        $inactivo = Cupon::fromRow([
            'codigo' => 'EXPIRADO',
            'descuento_fijo' => 5.0,
            'activo' => 0,
        ]);
        $this->assertFalse($inactivo->activo);
    }
}

