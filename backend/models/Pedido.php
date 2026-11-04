<?php
declare(strict_types=1);

final class Pedido
{
    public function __construct(
        public readonly int $id,
        public readonly string $codigo,
        public readonly ?int $usuario_id = null,
        public readonly string $entrega = 'retiro',
        public readonly string $pago = 'yape',
        public readonly string $comprobante = 'boleta',
        public readonly string $nombre = '',
        public readonly string $apellido = '',
        public readonly string $documento = '',
        public readonly string $telefono = '',
        public readonly string $direccion = '',
        public readonly string $referencia = '',
        public readonly ?string $tienda_id = null,
        public readonly string $horario = '',
        public readonly string $cupon = '',
        public readonly float $subtotal = 0.0,
        public readonly float $descuento = 0.0,
        public readonly float $envio = 0.0,
        public readonly float $total = 0.0,
        public readonly string $estado = 'confirmado',
        public readonly ?string $created_at = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: isset($row['id']) ? (int) $row['id'] : 0,
            codigo: (string) ($row['codigo'] ?? ''),
            usuario_id: array_key_exists('usuario_id', $row) && $row['usuario_id'] !== null && $row['usuario_id'] !== ''
                ? (int) $row['usuario_id']
                : null,
            entrega: array_key_exists('entrega', $row) && $row['entrega'] !== null
                ? (string) $row['entrega']
                : 'retiro',
            pago: array_key_exists('pago', $row) && $row['pago'] !== null
                ? (string) $row['pago']
                : 'yape',
            comprobante: array_key_exists('comprobante', $row) && $row['comprobante'] !== null
                ? (string) $row['comprobante']
                : 'boleta',
            nombre: (string) ($row['nombre'] ?? ''),
            apellido: (string) ($row['apellido'] ?? ''),
            documento: (string) ($row['documento'] ?? ''),
            telefono: (string) ($row['telefono'] ?? ''),
            direccion: (string) ($row['direccion'] ?? ''),
            referencia: (string) ($row['referencia'] ?? ''),
            tienda_id: array_key_exists('tienda_id', $row) && $row['tienda_id'] !== null && $row['tienda_id'] !== ''
                ? (string) $row['tienda_id']
                : null,
            horario: (string) ($row['horario'] ?? ''),
            cupon: (string) ($row['cupon'] ?? ''),
            subtotal: isset($row['subtotal']) ? (float) $row['subtotal'] : 0.0,
            descuento: isset($row['descuento']) ? (float) $row['descuento'] : 0.0,
            envio: isset($row['envio']) ? (float) $row['envio'] : 0.0,
            total: isset($row['total']) ? (float) $row['total'] : 0.0,
            estado: array_key_exists('estado', $row) && $row['estado'] !== null
                ? (string) $row['estado']
                : 'confirmado',
            created_at: array_key_exists('created_at', $row) && $row['created_at'] !== null
                ? (string) $row['created_at']
                : null,
        );
    }

    /**
     * @return array<string, float|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'usuario_id' => $this->usuario_id,
            'entrega' => $this->entrega,
            'pago' => $this->pago,
            'comprobante' => $this->comprobante,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'documento' => $this->documento,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'referencia' => $this->referencia,
            'tienda_id' => $this->tienda_id,
            'horario' => $this->horario,
            'cupon' => $this->cupon,
            'subtotal' => $this->subtotal,
            'descuento' => $this->descuento,
            'envio' => $this->envio,
            'total' => $this->total,
            'estado' => $this->estado,
            'created_at' => $this->created_at,
        ];
    }
}

