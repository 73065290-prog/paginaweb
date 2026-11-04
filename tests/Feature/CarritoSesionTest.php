<?php
declare(strict_types=1);

namespace Tests\Feature;

use CarritoService;
use Tests\TestCase;

final class CarritoSesionTest extends TestCase
{
    /** // P21 */
    public function test_agregar_producto_persiste_en_sesion_y_calcula_totales(): void
    {
        $service = new CarritoService();
        $p = producto_por_id(1);
        $this->assertNotNull($p);
        $service->agregar(1, 3);
        $this->assertSame(3, $service->totalUnidades());
        $this->assertSame(3, carrito_total_unidades());
        $this->assertEqualsWithDelta((float) $p['precio'] * 3, $service->totalMonto(), 0.001);
        $this->assertCount(1, $_SESSION['carrito']);
    }

    /** // P22 */
    public function test_cambiar_cantidad_y_eliminar_linea(): void
    {
        $service = new CarritoService();
        $service->agregar(1, 2);
        $service->cambiar(1, 1);
        $this->assertSame(3, $service->totalUnidades());
        $service->setCantidad(1, 0);
        $this->assertSame(0, $service->totalUnidades());
        $this->assertSame([], $service->items());
    }

    /** // P23 */
    public function test_no_agrega_producto_inexistente_ni_cantidad_invalida(): void
    {
        $service = new CarritoService();
        $service->agregar(99999, 1);
        $service->agregar(1, 0);
        $this->assertSame(0, $service->totalUnidades());
    }

    public function test_guardar_para_despues_y_recuperar_guardado(): void
    {
        $service = new CarritoService();
        $service->agregar(1, 4);
        $service->guardarParaDespues(1);

        $this->assertSame([], $service->items());
        $this->assertCount(1, $service->guardados());
        $this->assertSame(4, $service->guardados()[0]['cantidad']);

        $service->recuperarGuardado(1);
        $this->assertSame([], $service->guardados());
        $this->assertSame(4, $service->totalUnidades());
    }
}
