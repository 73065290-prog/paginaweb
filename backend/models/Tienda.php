<?php
declare(strict_types=1);

final class Tienda
{
    public function __construct(
        public readonly string $id,
        public readonly string $nombre,
        public readonly string $direccion = '',
        public readonly string $distrito = '',
        public readonly int $stock = 0,
        public readonly string $eta = '',
        public readonly string $estado = 'ok',
        public readonly string $img = '',
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (string) ($row['id'] ?? ''),
            nombre: (string) ($row['nombre'] ?? ''),
            direccion: (string) ($row['direccion'] ?? ''),
            distrito: (string) ($row['distrito'] ?? ''),
            stock: isset($row['stock']) ? (int) $row['stock'] : 0,
            eta: (string) ($row['eta'] ?? ''),
            estado: array_key_exists('estado', $row) && $row['estado'] !== null
                ? (string) $row['estado']
                : 'ok',
            img: (string) ($row['img'] ?? ''),
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'distrito' => $this->distrito,
            'stock' => $this->stock,
            'eta' => $this->eta,
            'estado' => $this->estado,
            'img' => $this->img,
        ];
    }
}

