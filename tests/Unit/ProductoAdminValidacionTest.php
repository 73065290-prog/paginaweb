<?php
declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class ProductoAdminValidacionTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payloadValido(array $override = []): array
    {
        return array_merge([
            'nombre' => 'Aceite Lab QA 900ml',
            'ean' => '7756123490001',
            'unidad' => 'Botella 900ml',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'Aceites',
            'descripcion' => 'Producto de laboratorio para pruebas QA Mass.',
            'precio' => 9.90,
            'costo' => 5.50,
            'puntoReorden' => 20,
            'capacidadGondola' => 100,
            'abc' => 'A',
            'activo' => 1,
        ], $override);
    }

    /** // P16 */
    public function test_validar_rechaza_nombre_corto_y_ean_invalido(): void
    {
        $r = producto_admin_validar($this->payloadValido([
            'nombre' => 'Ab',
            'ean' => '123',
        ]));
        $this->assertFalse($r['ok']);
        $joined = implode(' ', $r['errores']);
        $this->assertStringContainsString('nombre', mb_strtolower($joined));
        $this->assertStringContainsString('EAN', $joined);
    }

    /** // P17 */
    public function test_validar_rechaza_pvp_menor_o_igual_a_costo(): void
    {
        $r = producto_admin_validar($this->payloadValido([
            'precio' => 5.0,
            'costo' => 5.0,
        ]));
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('PVP', implode(' ', $r['errores']));
    }

    /** // P18 */
    public function test_validar_rechaza_reorden_mayor_igual_capacidad(): void
    {
        $r = producto_admin_validar($this->payloadValido([
            'puntoReorden' => 100,
            'capacidadGondola' => 50,
        ]));
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('reorden', mb_strtolower(implode(' ', $r['errores'])));
    }

    /** // P19 */
    public function test_validar_rechaza_abc_no_seleccionado(): void
    {
        $r = producto_admin_validar($this->payloadValido(['abc' => '']));
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('ABC', implode(' ', $r['errores']));
    }

    /** // P20 */
    public function test_validar_acepta_payload_completo_valido(): void
    {
        $r = producto_admin_validar($this->payloadValido(), 0);
        $this->assertTrue($r['ok'], implode(' · ', $r['errores']));
        $this->assertNotNull($r['data']);
        $this->assertSame('7756123490001', $r['data']['ean']);
    }
}
