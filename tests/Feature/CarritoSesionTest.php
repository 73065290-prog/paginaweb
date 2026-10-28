<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class CarritoSesionTest extends TestCase
{
    /** // P21 */
    public function test_agregar_producto_persiste_en_sesion_y_calcula_totales(): void
    {
        $p = producto_por_id(1);
        $this->assertNotNull($p);
        carrito_agregar(1, 3);
        $this->assertSame(3, carrito_total_unidades());
        $this->assertEqualsWithDelta((float) $p['precio'] * 3, carrito_total_monto(), 0.001);
        $this->assertCount(1, $_SESSION['carrito']);
    }

    /** // P22 */
    public function test_cambiar_cantidad_y_eliminar_linea(): void
    {
        carrito_agregar(1, 2);
        carrito_cambiar(1, 1);
        $this->assertSame(3, carrito_total_unidades());
        carrito_set_cantidad(1, 0);
        $this->assertSame(0, carrito_total_unidades());
        $this->assertSame([], carrito_items());
    }

    /** // P23 */
    public function test_no_agrega_producto_inexistente_ni_cantidad_invalida(): void
    {
        carrito_agregar(99999, 1);
        carrito_agregar(1, 0);
        $this->assertSame(0, carrito_total_unidades());
    }
}
