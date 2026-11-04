<?php
declare(strict_types=1);

final class Producto
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $marca = 'MASS',
        public readonly string $categoria = '',
        public readonly string $subcategoria = '',
        public readonly string $unidad = '',
        public readonly ?string $descripcion = null,
        public readonly ?string $ean = null,
        public readonly float $precio = 0.0,
        public readonly ?float $precio_antes = null,
        public readonly float $costo = 0.0,
        public readonly string $stock_label = '',
        public readonly string $stock_tone = 'ok',
        public readonly ?string $badge_text = null,
        public readonly ?string $badge_tone = null,
        public readonly string $img = '',
        public readonly string $img_nombre = '',
        public readonly bool $favorito = false,
        public readonly int $punto_reorden = 10,
        public readonly int $capacidad_gondola = 50,
        public readonly string $abc = 'B',
        public readonly bool $activo = true,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : 0,
            nombre: (string) ($row['nombre'] ?? ''),
            marca: array_key_exists('marca', $row) && $row['marca'] !== null ? (string) $row['marca'] : 'MASS',
            categoria: (string) ($row['categoria'] ?? ''),
            subcategoria: (string) ($row['subcategoria'] ?? ''),
            unidad: (string) ($row['unidad'] ?? ''),
            descripcion: array_key_exists('descripcion', $row) && $row['descripcion'] !== null
                ? (string) $row['descripcion']
                : null,
            ean: array_key_exists('ean', $row) && $row['ean'] !== null
                ? (string) $row['ean']
                : null,
            precio: isset($row['precio']) ? (float) $row['precio'] : 0.0,
            precio_antes: array_key_exists('precio_antes', $row) && $row['precio_antes'] !== null
                ? (float) $row['precio_antes']
                : null,
            costo: isset($row['costo']) ? (float) $row['costo'] : 0.0,
            stock_label: (string) ($row['stock_label'] ?? ''),
            stock_tone: array_key_exists('stock_tone', $row) && $row['stock_tone'] !== null
                ? (string) $row['stock_tone']
                : 'ok',
            badge_text: array_key_exists('badge_text', $row) && $row['badge_text'] !== null
                ? (string) $row['badge_text']
                : null,
            badge_tone: array_key_exists('badge_tone', $row) && $row['badge_tone'] !== null
                ? (string) $row['badge_tone']
                : null,
            img: (string) ($row['img'] ?? ''),
            img_nombre: (string) ($row['img_nombre'] ?? ''),
            favorito: array_key_exists('favorito', $row) ? (bool) (int) $row['favorito'] : false,
            punto_reorden: array_key_exists('punto_reorden', $row) && $row['punto_reorden'] !== null
                ? (int) $row['punto_reorden']
                : 10,
            capacidad_gondola: array_key_exists('capacidad_gondola', $row) && $row['capacidad_gondola'] !== null
                ? (int) $row['capacidad_gondola']
                : 50,
            abc: array_key_exists('abc', $row) && $row['abc'] !== null
                ? (string) $row['abc']
                : 'B',
            activo: array_key_exists('activo', $row) ? (bool) (int) $row['activo'] : true,
            created_at: array_key_exists('created_at', $row) && $row['created_at'] !== null
                ? (string) $row['created_at']
                : null,
            updated_at: array_key_exists('updated_at', $row) && $row['updated_at'] !== null
                ? (string) $row['updated_at']
                : null,
        );
    }

    /**
     * @return array<string, bool|float|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'marca' => $this->marca,
            'categoria' => $this->categoria,
            'subcategoria' => $this->subcategoria,
            'unidad' => $this->unidad,
            'descripcion' => $this->descripcion,
            'ean' => $this->ean,
            'precio' => $this->precio,
            'precio_antes' => $this->precio_antes,
            'costo' => $this->costo,
            'stock_label' => $this->stock_label,
            'stock_tone' => $this->stock_tone,
            'badge_text' => $this->badge_text,
            'badge_tone' => $this->badge_tone,
            'img' => $this->img,
            'img_nombre' => $this->img_nombre,
            'favorito' => $this->favorito,
            'punto_reorden' => $this->punto_reorden,
            'capacidad_gondola' => $this->capacidad_gondola,
            'abc' => $this->abc,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

