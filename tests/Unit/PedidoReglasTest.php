<?php
declare(strict_types=1);

namespace Tests\Unit;

use CuponService;
use PedidoService;
use PedidoValidator;
use Tests\TestCase;

final class PedidoReglasTest extends TestCase
{
    /** // P03 */
    public function test_normaliza_entrega_express_y_default_retiro(): void
    {
        $service = new PedidoService();
        $validator = new PedidoValidator();

        $this->assertSame('express', $service->normalizarEntrega('express'));
        $this->assertSame('retiro', $service->normalizarEntrega('delivery'));
        $this->assertSame('retiro', $service->normalizarEntrega('retiro'));
        $this->assertSame('express', $validator->normalizarEntrega('express'));
        $this->assertSame('retiro', $validator->normalizarEntrega('otro'));

        // Compatibilidad de funciones delegadoras
        $this->assertSame('express', pedido_normalizar_entrega('express'));
        $this->assertSame('retiro', pedido_normalizar_entrega('delivery'));
        $this->assertSame('retiro', pedido_normalizar_entrega('retiro'));
    }

    /** // P04 */
    public function test_costo_envio_retiro_gratis_y_express_4_90(): void
    {
        $service = new PedidoService();
        $this->assertSame(0.0, $service->costoEnvio('retiro'));
        $this->assertSame(4.9, $service->costoEnvio('express'));

        // Compatibilidad de funciones delegadoras
        $this->assertSame(0.0, pedido_costo_envio('retiro'));
        $this->assertSame(4.9, pedido_costo_envio('express'));
    }

    /** // P05 */
    public function test_descuento_cupones_validos_e_invalidos(): void
    {
        $cuponService = new CuponService();
        $this->assertSame(5.0, $cuponService->descuento('CLUBMASS10'));
        $this->assertSame(6.0, $cuponService->descuento('clubmass60'));
        $this->assertSame(0.0, $cuponService->descuento('INVALIDO'));

        // Compatibilidad de funciones delegadoras
        $this->assertSame(5.0, pedido_descuento_cupon('CLUBMASS10'));
        $this->assertSame(6.0, pedido_descuento_cupon('clubmass60'));
        $this->assertSame(0.0, pedido_descuento_cupon('INVALIDO'));

        $evalOk = $cuponService->evaluarAplicacion('clubmass10');
        $this->assertTrue($evalOk['ok']);
        $this->assertSame('CLUBMASS10', $evalOk['codigo']);
        $this->assertSame(5.0, $evalOk['descuento']);

        $evalBad = $cuponService->evaluarAplicacion('NOEXISTE');
        $this->assertFalse($evalBad['ok']);
        $this->assertStringContainsString('CLUBMASS10', $evalBad['mensaje']);
    }

    /** // P06 */
    public function test_validar_entrega_falla_campos_obligatorios_vacios(): void
    {
        $service = new PedidoService();
        $r = $service->validarEntrega([
            'entrega' => 'retiro',
            'nombre' => '',
            'apellido' => '',
            'documento' => '',
            'telefono' => '',
            'horario' => '',
            'tienda_id' => 'magdalena',
        ]);
        $this->assertFalse($r['ok']);
        $this->assertNotEmpty($r['errores']);
        $this->assertTrue(
            count($r['errores']) >= 4,
            'Debe reportar varios campos obligatorios'
        );
    }

    /** // P07 */
    public function test_validar_entrega_express_requiere_direccion_completa(): void
    {
        $validator = new PedidoValidator();
        $r = $validator->validateEntrega([
            'entrega' => 'express',
            'nombre' => 'Ana',
            'apellido' => 'Torres',
            'documento' => '12345678',
            'telefono' => '987654321',
            'horario' => array_key_first(mass_horarios_entrega('express')) ?: 'hoy_tarde',
            'direccion' => 'Corta',
            'tienda_id' => 'magdalena',
        ]);
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('dirección', implode(' ', $r['errores']));
    }

    /** // P08 */
    public function test_validar_retiro_ok_en_tienda_con_stock(): void
    {
        $service = new PedidoService();
        $horario = array_key_first($service->horariosEntrega('retiro')) ?: 'hoy_tarde';
        $r = $service->validarEntrega([
            'entrega' => 'retiro',
            'nombre' => 'Luis',
            'apellido' => 'Ramos',
            'documento' => '87654321',
            'telefono' => '912345678',
            'horario' => $horario,
            'tienda_id' => 'magdalena',
        ]);
        $this->assertTrue($r['ok'], implode(' · ', $r['errores']));
        $this->assertSame('retiro', $r['data']['entrega']);
        $this->assertNotSame('', $r['data']['direccion']);

        // Rechaza tienda agotada en modo retiro
        $rAgotada = $service->validarEntrega([
            'entrega' => 'retiro',
            'nombre' => 'Luis',
            'apellido' => 'Ramos',
            'documento' => '87654321',
            'telefono' => '912345678',
            'horario' => $horario,
            'tienda_id' => 'brena',
        ]);
        $this->assertFalse($rAgotada['ok']);
        $this->assertStringContainsString('stock', implode(' ', $rAgotada['errores']));
    }

    /** // P09 */
    public function test_resumen_express_suma_envio_respecto_retiro(): void
    {
        $service = new PedidoService();
        carrito_agregar(1, 2);
        $retiro = $service->resumen(null, ['cupon' => '', 'entrega' => 'retiro']);
        $express = $service->resumen(null, ['cupon' => '', 'entrega' => 'express']);
        $this->assertSame(0.0, $retiro['envio']);
        $this->assertSame(4.9, $express['envio']);
        $this->assertEqualsWithDelta($retiro['total'] + 4.9, $express['total'], 0.001);
    }

    /** // P10 */
    public function test_tracking_express_incluye_en_camino_retiro_no(): void
    {
        $service = new PedidoService();
        $trExpress = $service->trackingPasos('express', 'gondola');
        $trRetiro = $service->trackingPasos('retiro', 'gondola');
        $keysExpress = array_column($trExpress, 'key');
        $keysRetiro = array_column($trRetiro, 'key');
        $this->assertContains('camino', $keysExpress);
        $this->assertContains('listo', $keysRetiro);
        $this->assertNotContains('camino', $keysRetiro);
    }
}
