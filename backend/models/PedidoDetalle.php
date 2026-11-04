<?php
declare(strict_types=1);

final class PedidoDetalle
{
    public function __construct(
        public readonly int $id,
        public readonly int $pedido_id,
        public readonly ?int $producto_id = null,
        public readonly string $nombre = '',
        public readonly float $precio_unit = 0.0,
        public readonly int $cantidad = 1,
        public readonly float $subtotal = 0.0,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : 0,
            pedido_id: isset($row['pedido_id']) ? (int) $row['pedido_id'] : 0,
            producto_id: array_key_exists('producto_id', $row) && $row['producto_id'] !== null && $row['producto_id'] !== ''
                ? (int) $row['producto_id']
                : null,
            nombre: (string) ($row['nombre'] ?? ''),
            precio_unit: isset($row['precio_unit']) ? (float) $row['precio_unit'] : 0.0,
            cantidad: array_key_exists('cantidad', $row) && $row['cantidad'] !== null
                ? (int) $row['cantidad']
                : 1,
            subtotal: isset($row['subtotal']) ? (float) $row['subtotal'] : 0.0,
        );
    }

    /**
     * @return array<string, float|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pedido_id' => $this->pedido_id,
            'producto_id' => $this->producto_id,
            'nombre' => $this->nombre,
            'precio_unit' => $this->precio_unit,
            'cantidad' => $this->cantidad,
            'subtotal' => $this->subtotal,
        ];
    }
}

