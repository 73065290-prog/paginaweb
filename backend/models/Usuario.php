<?php
declare(strict_types=1);

final class Usuario
{
    public function __construct(
        public readonly int $id,
        public readonly string $usuario,
        public readonly string $password_hash,
        public readonly int $rol_id,
        public readonly string $nombre,
        public readonly string $cargo = '',
        public readonly bool $activo = true,
        public readonly ?string $created_at = null,
        public readonly ?string $rol = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : 0,
            usuario: (string) ($row['usuario'] ?? ''),
            password_hash: (string) ($row['password_hash'] ?? ''),
            rol_id: isset($row['rol_id']) ? (int) $row['rol_id'] : 0,
            nombre: (string) ($row['nombre'] ?? ''),
            cargo: (string) ($row['cargo'] ?? ''),
            activo: array_key_exists('activo', $row) ? (bool) (int) $row['activo'] : true,
            created_at: array_key_exists('created_at', $row) && $row['created_at'] !== null
                ? (string) $row['created_at']
                : null,
            rol: array_key_exists('rol', $row) && $row['rol'] !== null
                ? (string) $row['rol']
                : null,
        );
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario' => $this->usuario,
            'password_hash' => $this->password_hash,
            'rol_id' => $this->rol_id,
            'nombre' => $this->nombre,
            'cargo' => $this->cargo,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'rol' => $this->rol,
        ];
    }
}

