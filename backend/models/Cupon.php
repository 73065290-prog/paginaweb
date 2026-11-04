<?php
declare(strict_types=1);

final class Cupon
{
    public function __construct(
        public readonly string $codigo,
        public readonly float $descuento_fijo = 0.0,
        public readonly bool $activo = true,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            codigo: (string) ($row['codigo'] ?? ''),
            descuento_fijo: isset($row['descuento_fijo']) ? (float) $row['descuento_fijo'] : 0.0,
            activo: array_key_exists('activo', $row) ? (bool) (int) $row['activo'] : true,
        );
    }

    /**
     * @return array<string, bool|float|string>
     */
    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'descuento_fijo' => $this->descuento_fijo,
            'activo' => $this->activo,
        ];
    }
}

