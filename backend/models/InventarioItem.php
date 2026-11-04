<?php
declare(strict_types=1);

final class InventarioItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $producto_id,
        public readonly string $tienda_id,
        public readonly int $stock_piso = 0,
        public readonly int $stock_almacen = 0,
        public readonly ?string $nombre = null,
        public readonly ?string $marca = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : 0,
            producto_id: isset($row['producto_id']) ? (int) $row['producto_id'] : 0,
            tienda_id: (string) ($row['tienda_id'] ?? ''),
            stock_piso: isset($row['stock_piso']) ? (int) $row['stock_piso'] : 0,
            stock_almacen: isset($row['stock_almacen']) ? (int) $row['stock_almacen'] : 0,
            nombre: array_key_exists('nombre', $row) && $row['nombre'] !== null
                ? (string) $row['nombre']
                : null,
            marca: array_key_exists('marca', $row) && $row['marca'] !== null
                ? (string) $row['marca']
                : null,
        );
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'tienda_id' => $this->tienda_id,
            'stock_piso' => $this->stock_piso,
            'stock_almacen' => $this->stock_almacen,
            'nombre' => $this->nombre,
            'marca' => $this->marca,
        ];
    }
}

