<?php
declare(strict_types=1);

/**
 * Validador centralizado de productos WMS (alta, edición, EAN-13, margen, reorden y ABC).
 */
final class ProductoValidator
{
    /**
     * Limpia y valida un código EAN-13 (exactamente 13 dígitos numéricos).
     *
     * @return array{ok:bool,ean:string,error:?string}
     */
    public function validarEan(string $eanRaw): array
    {
        $ean = preg_replace('/\D+/', '', $eanRaw) ?? '';
        if (strlen($ean) !== 13) {
            return [
                'ok' => false,
                'ean' => $ean,
                'error' => 'El EAN-13 debe tener exactamente 13 dígitos',
            ];
        }
        return [
            'ok' => true,
            'ean' => $ean,
            'error' => null,
        ];
    }

    /**
     * Valida las reglas de costo, PVP y margen comercial positivo (PVP > costo > 0).
     *
     * @return array<int, string>
     */
    public function validarPreciosYMargen(float $precio, float $costo): array
    {
        $errores = [];
        if ($costo <= 0) {
            $errores[] = 'El costo unitario debe ser mayor a 0';
        }
        if ($precio <= 0) {
            $errores[] = 'El PVP debe ser mayor a 0';
        }
        if ($precio > 0 && $costo > 0 && $precio <= $costo) {
            $errores[] = 'El PVP debe ser mayor al costo (margen comercial positivo)';
        }
        return $errores;
    }

    /**
     * Valida parámetros de reorden y capacidad máxima de góndola (reorden >= 1, capacidad >= 1, reorden < capacidad).
     *
     * @return array<int, string>
     */
    public function validarReordenYCapacidad(int $puntoReorden, int $capacidad): array
    {
        $errores = [];
        if ($puntoReorden < 1) {
            $errores[] = 'El punto de reorden debe ser al menos 1';
        }
        if ($capacidad < 1) {
            $errores[] = 'La capacidad de góndola debe ser al menos 1';
        }
        if ($capacidad > 0 && $puntoReorden >= $capacidad) {
            $errores[] = 'El punto de reorden debe ser menor a la capacidad máxima de góndola';
        }
        return $errores;
    }

    /**
     * Valida que la clasificación ABC sea A, B o C.
     */
    public function validarAbc(string $abcRaw): ?string
    {
        $abc = strtoupper(trim($abcRaw));
        if (!in_array($abc, ['A', 'B', 'C'], true)) {
            return 'Selecciona una clasificación ABC (A, B o C)';
        }
        return null;
    }

    /**
     * Valida todos los campos de alta/edición de un producto WMS, incluyendo unicidad de EAN-13 y nombre.
     *
     * @param array<string, mixed> $data
     * @param array<int, array<string, mixed>>|null $catalogoExistente
     * @return array{ok:bool,errores:array<int,string>,data:?array<string,mixed>}
     */
    public function validate(array $data, int $idExcluir = 0, ?array $catalogoExistente = null): array
    {
        $errores = [];
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $eanInfo = $this->validarEan((string) ($data['ean'] ?? ''));
        $ean = $eanInfo['ean'];
        $unidad = trim((string) ($data['unidad'] ?? ''));
        $categoria = trim((string) ($data['categoria'] ?? ''));
        $subcategoria = trim((string) ($data['subcategoria'] ?? ''));
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $marca = trim((string) ($data['marca'] ?? 'MASS'));
        $precio = (float) ($data['precio'] ?? 0);
        $costo = (float) ($data['costo'] ?? 0);
        $puntoReorden = (int) ($data['puntoReorden'] ?? 0);
        $capacidad = (int) ($data['capacidadGondola'] ?? 0);
        $abc = strtoupper(trim((string) ($data['abc'] ?? '')));
        $activo = !empty($data['activo']) || !empty($data['activo_ui']);

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errores[] = 'El nombre comercial debe tener al menos 3 caracteres';
        }
        if (!$eanInfo['ok'] && $eanInfo['error'] !== null) {
            $errores[] = $eanInfo['error'];
        }
        if ($unidad === '') {
            $errores[] = 'Indica la unidad de medida';
        }
        if ($categoria === '') {
            $errores[] = 'Indica la categoría';
        }
        if ($subcategoria === '') {
            $errores[] = 'Indica la subcategoría';
        }
        if ($descripcion === '' || mb_strlen($descripcion) < 10) {
            $errores[] = 'La descripción de góndola debe tener al menos 10 caracteres';
        }

        foreach ($this->validarPreciosYMargen($precio, $costo) as $err) {
            $errores[] = $err;
        }
        foreach ($this->validarReordenYCapacidad($puntoReorden, $capacidad) as $err) {
            $errores[] = $err;
        }
        $errAbc = $this->validarAbc($abc);
        if ($errAbc !== null) {
            $errores[] = $errAbc;
        }

        // Unicidad EAN / nombre (alta y edición) — un solo recorrido del catálogo
        $debeValidarUnicidad = $errores === [] || strlen($ean) === 13 || mb_strlen($nombre) >= 3;
        if ($debeValidarUnicidad) {
            $catalogo = $catalogoExistente ?? (function_exists('mass_admin_productos') ? mass_admin_productos() : []);
            $nombreNorm = mb_strtolower($nombre);
            $eanDuplicado = false;
            $nombreDuplicado = false;
            foreach ($catalogo as $p) {
                if ((int) ($p['id'] ?? 0) === $idExcluir) {
                    continue;
                }
                if (!$eanDuplicado && strlen($ean) === 13 && (string) ($p['ean'] ?? '') === $ean) {
                    $errores[] = 'El EAN-13 ya está registrado en otro SKU del WMS';
                    $eanDuplicado = true;
                }
                if (
                    !$nombreDuplicado
                    && $nombreNorm !== ''
                    && mb_strtolower((string) ($p['nombre'] ?? '')) === $nombreNorm
                ) {
                    $errores[] = 'Ya existe un producto con el mismo nombre comercial';
                    $nombreDuplicado = true;
                }
                if ($eanDuplicado && $nombreDuplicado) {
                    break;
                }
            }
        }

        $errores = array_values(array_unique($errores));

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores, 'data' => null];
        }

        return [
            'ok' => true,
            'errores' => [],
            'data' => [
                'nombre' => $nombre,
                'ean' => $ean,
                'unidad' => $unidad,
                'categoria' => $categoria,
                'subcategoria' => $subcategoria,
                'descripcion' => $descripcion,
                'marca' => $marca !== '' ? $marca : 'MASS',
                'precio' => round($precio, 2),
                'costo' => round($costo, 2),
                'puntoReorden' => $puntoReorden,
                'capacidadGondola' => $capacidad,
                'abc' => $abc,
                'activo' => $activo,
            ],
        ];
    }

    /**
     * Determina la clase CSS de error para un campo del formulario modal de productos.
     *
     * @param array<string, mixed> $p
     * @param array<int, string> $formErrores
     */
    public function claseCampoInvalido(string $campo, array $p, array $formErrores): string
    {
        if ($formErrores === []) {
            return '';
        }
        $v = $p[$campo] ?? '';
        if ($campo === 'ean' && !$this->validarEan((string) $v)['ok']) {
            return ' is-invalid';
        }
        if (in_array($campo, ['precio', 'costo'], true) && (float) $v <= 0) {
            return ' is-invalid';
        }
        if (in_array($campo, ['puntoReorden', 'capacidadGondola'], true) && (int) $v < 1) {
            return ' is-invalid';
        }
        if ($campo === 'descripcion' && mb_strlen(trim((string) $v)) < 10) {
            return ' is-invalid';
        }
        if ($campo === 'nombre' && mb_strlen(trim((string) $v)) < 3) {
            return ' is-invalid';
        }
        if (in_array($campo, ['unidad', 'categoria', 'subcategoria'], true) && trim((string) $v) === '') {
            return ' is-invalid';
        }
        if (
            $campo === 'precio'
            && (float) ($p['precio'] ?? 0) > 0
            && (float) ($p['costo'] ?? 0) > 0
            && (float) $p['precio'] <= (float) $p['costo']
        ) {
            return ' is-invalid';
        }
        return '';
    }

    /**
     * Determina si el selector ABC debe marcarse como inválido tras un envío con errores.
     *
     * @param array<string, mixed> $p
     * @param array<int, string> $formErrores
     */
    public function esAbcInvalido(array $p, array $formErrores): bool
    {
        return $formErrores !== [] && $this->validarAbc((string) ($p['abc'] ?? '')) !== null;
    }
}
