<?php
declare(strict_types=1);

/**
 * Validador de reglas de entrega, horarios, modalidad y datos de checkout de pedidos.
 */
final class PedidoValidator
{
    public const ENTREGA_RETIRO = 'retiro';
    public const ENTREGA_EXPRESS = 'express';

    /** @var array<int, string> */
    public const METODOS_PAGO = ['yape', 'tarjeta', 'contra'];

    /** @var array<int, string> */
    public const TIPOS_COMPROBANTE = ['boleta', 'factura'];

    public const DIRECCION_MIN_LEN = 10;
    public const DOCUMENTO_MIN_DIGITOS = 8;
    public const TELEFONO_MIN_DIGITOS = 9;

    /**
     * Normaliza la modalidad de entrega a 'retiro' o 'express'.
     */
    public function normalizarEntrega(string $modo): string
    {
        return $modo === self::ENTREGA_EXPRESS ? self::ENTREGA_EXPRESS : self::ENTREGA_RETIRO;
    }

    /**
     * Normaliza el método de pago ('yape' | 'tarjeta' | 'contra').
     */
    public function normalizarPago(string $pago): string
    {
        return in_array($pago, self::METODOS_PAGO, true) ? $pago : 'yape';
    }

    /**
     * Normaliza el tipo de comprobante ('boleta' | 'factura').
     */
    public function normalizarComprobante(string $comprobante): string
    {
        return $comprobante === 'factura' ? 'factura' : 'boleta';
    }

    /**
     * Devuelve el mapa de horarios permitidos según la modalidad.
     *
     * @return array<string, string>
     */
    public function horariosPermitidos(string $modo): array
    {
        $entrega = $this->normalizarEntrega($modo);
        if (function_exists('mass_horarios_entrega')) {
            return mass_horarios_entrega($entrega);
        }

        if ($entrega === self::ENTREGA_EXPRESS) {
            return [
                'hoy_express' => 'Hoy · Express 45–60 min',
                'hoy_tarde' => 'Hoy · 16:00–18:00',
                'hoy_noche' => 'Hoy · 18:00–20:00',
                'manana_manana' => 'Mañana · 09:00–13:00',
            ];
        }

        return [
            'hoy_tarde' => 'Hoy · 16:00–20:00 (retiro en tienda)',
            'manana_manana' => 'Mañana · 09:00–13:00',
            'manana_tarde' => 'Mañana · 16:00–20:00',
            'pasado_manana' => 'Pasado mañana · 09:00–13:00',
        ];
    }

    /**
     * Valida los datos de entrega y facturación según la modalidad ('retiro' o 'express').
     *
     * @param array<string, mixed> $data
     * @return array{ok:bool, errores:array<int,string>, data:array<string,mixed>}
     */
    public function validateEntrega(array $data): array
    {
        $errores = [];
        $entrega = $this->normalizarEntrega((string) ($data['entrega'] ?? self::ENTREGA_RETIRO));
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $apellido = trim((string) ($data['apellido'] ?? ''));
        $documento = preg_replace('/\D+/', '', (string) ($data['documento'] ?? '')) ?? '';
        $telefono = trim((string) ($data['telefono'] ?? ''));
        $horario = (string) ($data['horario'] ?? '');
        $horariosOk = array_keys($this->horariosPermitidos($entrega));

        if ($nombre === '' || mb_strlen($nombre) < 2) {
            $errores[] = 'Ingresa tus nombres';
        }
        if ($apellido === '' || mb_strlen($apellido) < 2) {
            $errores[] = 'Ingresa tus apellidos';
        }
        if (strlen($documento) < self::DOCUMENTO_MIN_DIGITOS) {
            $errores[] = 'Documento inválido (mín. 8 dígitos)';
        }
        if ($telefono === '' || strlen(preg_replace('/\D+/', '', $telefono) ?? '') < self::TELEFONO_MIN_DIGITOS) {
            $errores[] = 'Teléfono inválido (mín. 9 dígitos)';
        }
        if ($horario === '' || !in_array($horario, $horariosOk, true)) {
            $errores[] = 'Selecciona un horario válido para ' . ($entrega === self::ENTREGA_EXPRESS ? 'delivery' : 'retiro');
        }

        $tiendaId = (string) ($data['tienda_id'] ?? 'magdalena');
        $direccion = trim((string) ($data['direccion'] ?? ''));
        $referencia = trim((string) ($data['referencia'] ?? ''));
        $tienda = function_exists('mass_tienda_por_id') ? mass_tienda_por_id($tiendaId) : null;

        if ($entrega === self::ENTREGA_RETIRO) {
            if ($tienda === null) {
                $errores[] = 'Selecciona una tienda Mass para el retiro';
            } elseif (($tienda['estado'] ?? '') === 'agotado' || (int) ($tienda['stock'] ?? 0) <= 0) {
                $errores[] = 'La tienda seleccionada no tiene stock para retiro';
            } else {
                $direccion = (string) $tienda['direccion'];
            }
        } else {
            // Delivery: tienda de origen = misma tienda de inventario (default Magdalena)
            if ($tienda === null || ($tienda['estado'] ?? '') === 'agotado') {
                $tiendaId = 'magdalena';
                $tienda = function_exists('mass_tienda_por_id') ? mass_tienda_por_id($tiendaId) : null;
            }
            if (mb_strlen($direccion) < self::DIRECCION_MIN_LEN) {
                $errores[] = 'Ingresa una dirección de entrega completa';
            }
        }

        $comprobante = $this->normalizarComprobante((string) ($data['comprobante'] ?? 'boleta'));

        return [
            'ok' => $errores === [],
            'errores' => $errores,
            'data' => [
                'entrega' => $entrega,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'documento' => $documento,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'referencia' => $referencia,
                'tienda_id' => $tiendaId,
                'horario' => $horario !== '' ? $horario : ($horariosOk[0] ?? 'hoy_tarde'),
                'comprobante' => $comprobante,
            ],
        ];
    }
}
