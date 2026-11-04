<?php
declare(strict_types=1);

namespace Tests\Unit;

use InventarioItem;
use Tests\TestCase;

final class ModelsInventarioItemTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto(): void
    {
        $item = new InventarioItem(
            id: 1,
            producto_id: 5,
            tienda_id: 'magdalena',
        );

        $this->assertSame(1, $item->id);
        $this->assertSame(5, $item->producto_id);
        $this->assertSame('magdalena', $item->tienda_id);
        $this->assertSame(0, $item->stock_piso);
        $this->assertSame(0, $item->stock_almacen);
        $this->assertNull($item->nombre);
        $this->assertNull($item->marca);
    }

    public function test_from_row_y_to_array_con_y_sin_campos_join(): void
    {
        $row = [
            'id' => '10',
            'producto_id' => '3',
            'tienda_id' => 'surco',
            'stock_piso' => '18',
            'stock_almacen' => '45',
            'nombre' => 'Leche Gloria Azul 400 g',
            'marca' => 'GLORIA',
        ];

        $item = InventarioItem::fromRow($row);
        $arr = $item->toArray();

        $this->assertSame(10, $item->id);
        $this->assertSame(3, $item->producto_id);
        $this->assertSame('surco', $item->tienda_id);
        $this->assertSame(18, $item->stock_piso);
        $this->assertSame(45, $item->stock_almacen);
        $this->assertSame('Leche Gloria Azul 400 g', $arr['nombre']);
        $this->assertSame('GLORIA', $arr['marca']);

        $rowSinJoin = [
            'id' => 11,
            'producto_id' => 4,
            'tienda_id' => 'lince',
            'stock_piso' => 8,
            'stock_almacen' => 20,
        ];
        $itemSinJoin = InventarioItem::fromRow($rowSinJoin);
        $this->assertNull($itemSinJoin->nombre);
        $this->assertNull($itemSinJoin->marca);
    }
}

