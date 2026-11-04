<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * Servicio de negocio de cupones promocionales Club Mass (validación, cálculo de descuento y aplicación).
 */
final class CuponService
{
    private CuponRepository $repository;

    public function __construct(?CuponRepository $repository = null)
    {
        $this->repository = $repository ?? new CuponRepository();
    }

    public function repository(): CuponRepository
    {
        return $this->repository;
    }

    /**
     * Retorna el mapa oficial de cupones válidos Club Mass y sus montos de descuento en soles.
     *
     * @return array<string, float>
     */
    public function cuponesValidos(): array
    {
        return [
            'CLUBMASS10' => 5.0,
            'CLUBMASS60' => 6.0,
        ];
    }

    /**
     * Normaliza el código de cupón recibido (trim + mayúsculas).
     */
    public function normalizarCodigo(string $codigo): string
    {
        return strtoupper(trim($codigo));
    }

    /**
     * Obtiene el monto de descuento aplicable para un código de cupón (consultando CuponRepository o fallback).
     */
    public function descuento(string $codigo): float
    {
        $codigoNorm = $this->normalizarCodigo($codigo);
        if ($codigoNorm === '') {
            return 0.0;
        }
        if (Database::connected()) {
            $dbDesc = $this->repository->descuento($codigoNorm);
            if ($dbDesc >= 0.0) {
                $entidad = Cupon::fromRow([
                    'codigo' => $codigoNorm,
                    'descuento_fijo' => $dbDesc,
                    'activo' => $dbDesc > 0.0 ? 1 : 0,
                ]);
                return $entidad->descuento_fijo;
            }
        }
        $mapa = $this->cuponesValidos();
        return $mapa[$codigoNorm] ?? 0.0;
    }

    /**
     * Comprueba si un código de cupón es válido y otorga un descuento mayor a 0.
     */
    public function esValido(string $codigo): bool
    {
        return $this->descuento($codigo) > 0.0;
    }

    /**
     * Evalúa la aplicación de un cupón en el carrito/checkout y devuelve el estado, código, descuento y mensaje UI.
     *
     * @return array{ok:bool,codigo:string,descuento:float,mensaje:string}
     */
    public function evaluarAplicacion(string $codigoRaw): array
    {
        $codigo = $this->normalizarCodigo($codigoRaw);
        if ($codigo === '') {
            return [
                'ok' => true,
                'codigo' => '',
                'descuento' => 0.0,
                'mensaje' => 'Cupón quitado',
            ];
        }
        if ($this->esValido($codigo)) {
            $monto = $this->descuento($codigo);
            $montoFmt = function_exists('dinero') ? dinero($monto) : ('S/ ' . number_format($monto, 2, '.', ''));
            return [
                'ok' => true,
                'codigo' => $codigo,
                'descuento' => $monto,
                'mensaje' => 'Cupón ' . $codigo . ' aplicado (−' . $montoFmt . ')',
            ];
        }
        $validos = implode(' o ', array_keys($this->cuponesValidos()));
        return [
            'ok' => false,
            'codigo' => '',
            'descuento' => 0.0,
            'mensaje' => 'Cupón no válido — prueba ' . $validos,
        ];
    }
}

