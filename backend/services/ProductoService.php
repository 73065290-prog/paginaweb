<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/validators/ProductoValidator.php';

/**
 * Servicio de negocio del módulo de Productos (catálogo, alta/edición admin WMS, margen y validación EAN).
 */
final class ProductoService
{
    private ProductoValidator $validator;
    private ProductoRepository $repository;
    private CategoriaRepository $categoriaRepository;

    public function __construct(
        ?ProductoValidator $validator = null,
        ?ProductoRepository $repository = null,
        ?CategoriaRepository $categoriaRepository = null,
    ) {
        $this->validator = $validator ?? new ProductoValidator();
        $this->repository = $repository ?? new ProductoRepository();
        $this->categoriaRepository = $categoriaRepository ?? new CategoriaRepository();
    }

    public function validator(): ProductoValidator
    {
        return $this->validator;
    }

    /**
     * Calcula el margen bruto y el porcentaje de margen comercial sobre PVP.
     *
     * @return array{margenBruto:float,margenPct:float}
     */
    public function calcularMargen(float $precio, float $costo): array
    {
        $margenBruto = round($precio - $costo, 2);
        $margenPct = $precio > 0 ? round(($margenBruto / $precio) * 100, 2) : 0.0;
        return [
            'margenBruto' => $margenBruto,
            'margenPct' => $margenPct,
        ];
    }

    /**
     * Valida los datos de un producto administrativo mediante ProductoValidator.
     *
     * @param array<string, mixed> $data
     * @return array{ok:bool,errores:array<int,string>,data:?array<string,mixed>}
     */
    public function validar(array $data, int $idExcluir = 0): array
    {
        return $this->validator->validate($data, $idExcluir, $this->listarAdmin());
    }

    /**
     * Valida específicamente un código EAN-13.
     *
     * @return array{ok:bool,ean:string,error:?string}
     */
    public function validarEan(string $eanRaw): array
    {
        return $this->validator->validarEan($eanRaw);
    }

    /**
     * Retorna el listado de categorías desde BD (si está activa) o el fallback demo.
     *
     * @param array<int, array<string, mixed>> $fallbackDemo
     * @return array<int, array<string, mixed>>
     */
    public function listarCategorias(array $fallbackDemo = []): array
    {
        if (Database::connected()) {
            $rows = $this->categoriaRepository->all();
            if ($rows !== []) {
                return $rows;
            }
        }
        return $fallbackDemo;
    }

    /**
     * Retorna el catálogo de productos desde BD (si está activa) o el fallback demo.
     *
     * @param array<int, array<string, mixed>> $fallbackDemo
     * @return array<int, array<string, mixed>>
     */
    public function listarCatalogo(array $fallbackDemo = []): array
    {
        if (Database::connected()) {
            $rows = $this->repository->all();
            if ($rows !== []) {
                return $rows;
            }
        }
        return $fallbackDemo;
    }

    /**
     * Busca un producto del catálogo por su ID.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerPorId(int $id): ?array
    {
        foreach (mass_productos() as $p) {
            if ((int) $p['id'] === $id) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Obtiene una lista de productos a partir de sus IDs.
     *
     * @param array<int, int|string> $ids
     * @return array<int, array<string, mixed>>
     */
    public function obtenerPorIds(array $ids): array
    {
        $map = [];
        foreach (mass_productos() as $p) {
            $map[(int) $p['id']] = $p;
        }
        $out = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if (isset($map[$id])) {
                $out[] = $map[$id];
            }
        }
        return $out;
    }

    /**
     * Filtra y ordena el catálogo de tienda según criterios de búsqueda, categoría, precio, marca y chips.
     *
     * @param array<string, mixed> $filtros
     * @return array<int, array<string, mixed>>
     */
    public function filtrarCatalogo(array $filtros): array
    {
        $lista = mass_productos();
        $cat = $filtros['cat'] ?? 'todos';
        $q = mb_strtolower(trim((string) ($filtros['q'] ?? '')));
        $min = (float) ($filtros['min'] ?? 0);
        $max = (float) ($filtros['max'] ?? 999);
        $marcas = $filtros['marcas'] ?? [];
        $chip = $filtros['chip'] ?? 'todos';
        $orden = $filtros['orden'] ?? 'relevancia';
        $stockOk = !empty($filtros['stock_ok']);
        $stockBajo = !empty($filtros['stock_bajo']);

        $lista = array_values(array_filter(
            $lista,
            static function (array $p) use ($cat, $q, $min, $max, $marcas, $chip, $stockOk, $stockBajo): bool {
                if ($cat !== 'todos' && $p['categoria'] !== $cat) {
                    return false;
                }
                if ($q !== '') {
                    $hay = mb_strtolower($p['nombre'] . ' ' . $p['marca'] . ' ' . $p['categoria']);
                    if (!str_contains($hay, $q)) {
                        return false;
                    }
                }
                if ((float) $p['precio'] < $min || (float) $p['precio'] > $max) {
                    return false;
                }
                if ($marcas && !in_array($p['marca'], $marcas, true)) {
                    return false;
                }
                if ($p['stockTone'] === 'ok' && !$stockOk) {
                    return false;
                }
                if ($p['stockTone'] === 'bajo' && !$stockBajo) {
                    return false;
                }
                if ($chip === 'precio_bajo' && (float) $p['precio'] > 10) {
                    return false;
                }
                if ($chip === 'ofertas') {
                    $badge = $p['badge']['text'] ?? '';
                    if ($badge === '' || !preg_match('/OFERTA|AHORRO|-/i', $badge)) {
                        return false;
                    }
                }
                if ($chip === 'vendidos') {
                    $badge = $p['badge']['text'] ?? '';
                    if ($badge === '' || !preg_match('/VENDIDO|AHORRO/i', $badge)) {
                        return false;
                    }
                }
                return true;
            }
        ));

        usort($lista, static function (array $a, array $b) use ($orden): int {
            return match ($orden) {
                'precio_asc' => $a['precio'] <=> $b['precio'],
                'precio_desc' => $b['precio'] <=> $a['precio'],
                'nombre' => strcmp($a['nombre'], $b['nombre']),
                default => 0,
            };
        });

        return $lista;
    }

    /**
     * Aplica campos sobre una fila de producto administrativo y recalcula su margen comercial y SKU.
     *
     * @param array<string, mixed> $row
     * @param array<string, mixed> $campos
     * @return array<string, mixed>
     */
    public function aplicarCamposAdmin(array $row, array $campos): array
    {
        $row = array_merge($row, $campos);
        $row['id'] = (int) ($row['id'] ?? 0);
        $row['precio'] = (float) ($row['precio'] ?? 0);
        $row['costo'] = (float) ($row['costo'] ?? 0);
        $margen = $this->calcularMargen($row['precio'], $row['costo']);
        $row['margenBruto'] = $margen['margenBruto'];
        $row['margenPct'] = $margen['margenPct'];
        $skuNum = str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT);
        $row['skuInterno'] = $skuNum;
        $row['sku'] = 'SKU-' . $skuNum;
        return $row;
    }

    /**
     * Normaliza una fila de producto nuevo en sesión administrativa con cálculo de margen y SKU.
     *
     * @param array<string, mixed> $fila
     * @return array<string, mixed>
     */
    public function normalizarFilaAdmin(array $fila): array
    {
        $id = (int) ($fila['id'] ?? 0);
        $skuNum = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
        $precio = (float) ($fila['precio'] ?? 0);
        $costo = (float) ($fila['costo'] ?? 0);
        $margen = $this->calcularMargen($precio, $costo);
        $marca = (string) ($fila['marca'] ?? 'MASS');

        return [
            'id' => $id,
            'sku' => 'SKU-' . $skuNum,
            'skuInterno' => $skuNum,
            'ean' => (string) ($fila['ean'] ?? ''),
            'nombre' => (string) ($fila['nombre'] ?? ''),
            'marca' => $marca,
            'categoria' => (string) ($fila['categoria'] ?? 'Abarrotes'),
            'subcategoria' => (string) ($fila['subcategoria'] ?? 'General'),
            'unidad' => (string) ($fila['unidad'] ?? 'Unidad'),
            'descripcion' => (string) ($fila['descripcion'] ?? ''),
            'precio' => $precio,
            'costo' => $costo,
            'margenPct' => $margen['margenPct'],
            'margenBruto' => $margen['margenBruto'],
            'stockTone' => (string) ($fila['stockTone'] ?? 'ok'),
            'stockPiso' => (int) ($fila['stockPiso'] ?? 0),
            'stockAlmacen' => (int) ($fila['stockAlmacen'] ?? 0),
            'puntoReorden' => (int) ($fila['puntoReorden'] ?? 25),
            'capacidadGondola' => (int) ($fila['capacidadGondola'] ?? 200),
            'abc' => (string) ($fila['abc'] ?? 'B'),
            'activo' => !empty($fila['activo']),
            'img' => (string) ($fila['img'] ?? 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            'imgNombre' => (string) ($fila['imgNombre'] ?? ('nuevo_' . $id . '.jpg')),
            'esNuevo' => true,
        ];
    }

    /**
     * Retorna el catálogo administrativo WMS enriquecido con costos, margen, ABC, overrides y productos extra.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarAdmin(): array
    {
        $subs = [
            'Abarrotes' => 'Aceites y Grasas',
            'Lácteos' => 'Leches y Derivados',
            'Frutas' => 'Frescos Seleccionados',
            'Bebidas' => 'Gaseosas y Jugos',
            'Limpieza' => 'Hogar y Cuidado',
        ];
        $abcCycle = ['A', 'B', 'C', 'A', 'B'];
        $overrides = $_SESSION['admin_producto_overrides'] ?? [];
        if (!is_array($overrides)) {
            $overrides = [];
        }

        $out = [];
        foreach (mass_productos() as $p) {
            $id = (int) $p['id'];
            $skuNum = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
            $precio = (float) $p['precio'];
            $costo = round($precio * 0.68, 2);
            $margen = $this->calcularMargen($precio, $costo);
            $tone = (string) ($p['stockTone'] ?? 'ok');
            $stockPiso = $tone === 'bajo' ? 8 : ($tone === 'critico' ? 0 : 42);
            $stockAlmacen = $tone === 'bajo' ? 4 : 24;
            $abc = $abcCycle[($id - 1) % count($abcCycle)];
            $cat = (string) $p['categoria'];
            $marca = (string) $p['marca'];
            $imgNombre = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $marca)) . '_' . $id . '.jpg';

            $row = [
                'id' => $id,
                'sku' => 'SKU-' . $skuNum,
                'skuInterno' => $skuNum,
                'ean' => '7756' . str_pad((string) (123400000 + $id), 9, '0', STR_PAD_LEFT),
                'nombre' => (string) $p['nombre'],
                'marca' => $marca,
                'categoria' => $cat,
                'subcategoria' => $subs[$cat] ?? 'General',
                'unidad' => (string) ($p['unidad'] ?? 'Unidad'),
                'descripcion' => 'Artículo comercial Mass · ' . $marca . ' · presentación ' . ($p['unidad'] ?? 'unidad') . '. Listo para góndola y reposición WMS.',
                'precio' => $precio,
                'costo' => $costo,
                'margenPct' => $margen['margenPct'],
                'margenBruto' => $margen['margenBruto'],
                'stockTone' => $tone,
                'stockPiso' => $stockPiso,
                'stockAlmacen' => $stockAlmacen,
                'puntoReorden' => $tone === 'bajo' ? 40 : 25,
                'capacidadGondola' => 200,
                'abc' => $abc,
                'activo' => true,
                'img' => (string) $p['img'],
                'imgNombre' => $imgNombre,
                'esNuevo' => false,
            ];

            if (isset($overrides[$id]) && is_array($overrides[$id])) {
                $row = $this->aplicarCamposAdmin($row, $overrides[$id]);
            }

            $out[] = $row;
        }

        $extras = $_SESSION['admin_productos_extra'] ?? [];
        if (is_array($extras)) {
            foreach ($extras as $extra) {
                if (!is_array($extra) || !isset($extra['id'])) {
                    continue;
                }
                $out[] = $this->normalizarFilaAdmin($extra);
            }
        }

        return $out;
    }

    /**
     * Busca un producto administrativo por su ID.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerAdminPorId(int $id): ?array
    {
        foreach ($this->listarAdmin() as $p) {
            if ((int) $p['id'] === $id) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Calcula el siguiente ID secuencial disponible para alta de producto.
     */
    public function siguienteIdAdmin(): int
    {
        $max = 0;
        foreach (mass_productos() as $p) {
            $max = max($max, (int) $p['id']);
        }
        $extras = $_SESSION['admin_productos_extra'] ?? [];
        if (is_array($extras)) {
            foreach ($extras as $p) {
                if (is_array($p)) {
                    $max = max($max, (int) ($p['id'] ?? 0));
                }
            }
        }
        return $max + 1;
    }

    /**
     * Retorna la plantilla inicial para el formulario de creación de un nuevo producto WMS.
     *
     * @return array<string, mixed>
     */
    public function plantillaNuevaAdmin(): array
    {
        $id = $this->siguienteIdAdmin();
        $skuNum = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
        return [
            'id' => 0,
            'sku' => 'SKU-' . $skuNum,
            'skuInterno' => $skuNum,
            'ean' => '',
            'nombre' => '',
            'marca' => 'MASS',
            'categoria' => '',
            'subcategoria' => '',
            'unidad' => '',
            'descripcion' => '',
            'precio' => 0.0,
            'costo' => 0.0,
            'margenPct' => 0.0,
            'margenBruto' => 0.0,
            'stockTone' => 'ok',
            'stockPiso' => 0,
            'stockAlmacen' => 0,
            'puntoReorden' => 0,
            'capacidadGondola' => 0,
            'abc' => '',
            'activo' => true,
            'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
            'imgNombre' => 'nuevo_producto.jpg',
            'esNuevo' => true,
        ];
    }

    /**
     * Valida y persiste los cambios de edición sobre un producto existente.
     *
     * @param array<string, mixed> $data
     * @return array{ok:bool,errores:array<int,string>,id:int}
     */
    public function guardarAdmin(int $id, array $data): array
    {
        $valid = $this->validar($data, $id);
        if (!$valid['ok'] || $valid['data'] === null) {
            return ['ok' => false, 'errores' => $valid['errores'], 'id' => $id];
        }
        $campos = $valid['data'];

        $modelo = Producto::fromRow(array_merge($campos, [
            'id' => $id,
            'punto_reorden' => $campos['puntoReorden'],
            'capacidad_gondola' => $campos['capacidadGondola'],
        ]));

        $extras = $_SESSION['admin_productos_extra'] ?? [];
        if (!is_array($extras)) {
            $extras = [];
        }
        foreach ($extras as $i => $extra) {
            if (!is_array($extra) || (int) ($extra['id'] ?? 0) !== $id) {
                continue;
            }
            $extras[$i] = array_merge($extra, $campos, [
                'id' => $modelo->id,
                'esNuevo' => true,
            ]);
            $_SESSION['admin_productos_extra'] = $extras;
            return ['ok' => true, 'errores' => [], 'id' => $id];
        }

        $base = $this->obtenerPorId($id);
        if ($id < 1 || $base === null) {
            return ['ok' => false, 'errores' => ['Producto no encontrado'], 'id' => $id];
        }

        if (!isset($_SESSION['admin_producto_overrides']) || !is_array($_SESSION['admin_producto_overrides'])) {
            $_SESSION['admin_producto_overrides'] = [];
        }
        $_SESSION['admin_producto_overrides'][$id] = $campos;

        if (Database::connected()) {
            $this->repository->update($id, $campos);
        }

        return ['ok' => true, 'errores' => [], 'id' => $id];
    }

    /**
     * Valida y persiste el alta de un nuevo producto en el catálogo administrativo.
     *
     * @param array<string, mixed> $data
     * @return array{ok:bool,errores:array<int,string>,id:int}
     */
    public function crearAdmin(array $data): array
    {
        $valid = $this->validar($data, 0);
        if (!$valid['ok'] || $valid['data'] === null) {
            return ['ok' => false, 'errores' => $valid['errores'], 'id' => 0];
        }

        $id = $this->siguienteIdAdmin();
        $campos = $valid['data'];
        if (!isset($_SESSION['admin_productos_extra']) || !is_array($_SESSION['admin_productos_extra'])) {
            $_SESSION['admin_productos_extra'] = [];
        }

        $modelo = Producto::fromRow(array_merge($campos, [
            'id' => $id,
            'stock_tone' => 'ok',
            'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
            'img_nombre' => 'nuevo_' . $id . '.jpg',
            'punto_reorden' => $campos['puntoReorden'],
            'capacidad_gondola' => $campos['capacidadGondola'],
        ]));

        $payload = array_merge($campos, [
            'id' => $modelo->id,
            'marca' => $modelo->marca,
            'stockTone' => $modelo->stock_tone,
            'stockPiso' => 0,
            'stockAlmacen' => 0,
            'img' => $modelo->img,
            'imgNombre' => $modelo->img_nombre,
            'esNuevo' => true,
        ]);

        if (Database::connected()) {
            $dbId = $this->repository->create($payload);
            if ($dbId > 0) {
                return ['ok' => true, 'errores' => [], 'id' => $dbId];
            }
        }

        $_SESSION['admin_productos_extra'][] = $payload;

        return ['ok' => true, 'errores' => [], 'id' => $id];
    }

    /**
     * Filtra y pagina el listado administrativo de productos por texto, clase ABC y nivel de stock.
     *
     * @return array{items:array<int,array<string,mixed>>,total:int,totalPaginas:int,pagina:int,filtroAbc:string}
     */
    public function listarAdminFiltrado(
        string $q = '',
        string $filtroAbcRaw = 'todos',
        string $filtroStock = 'todos',
        int $pagina = 1,
        int $porPagina = 8
    ): array {
        $qNorm = mb_strtolower(trim($q));
        $abcUpper = strtoupper(trim($filtroAbcRaw));
        $filtroAbc = in_array($abcUpper, ['A', 'B', 'C'], true) ? $abcUpper : 'todos';

        $productos = $this->listarAdmin();

        if ($qNorm !== '') {
            $productos = array_values(array_filter(
                $productos,
                static function (array $p) use ($qNorm): bool {
                    $hay = mb_strtolower($p['nombre'] . ' ' . $p['sku'] . ' ' . $p['ean'] . ' ' . $p['marca'] . ' ' . $p['categoria']);
                    return str_contains($hay, $qNorm);
                }
            ));
        }

        if ($filtroAbc !== 'todos') {
            $productos = array_values(array_filter(
                $productos,
                static fn (array $p): bool => ($p['abc'] ?? '') === $filtroAbc
            ));
        }

        if (in_array($filtroStock, ['ok', 'bajo', 'critico'], true)) {
            $productos = array_values(array_filter(
                $productos,
                static fn (array $p): bool => ($p['stockTone'] ?? '') === $filtroStock
            ));
        }

        $total = count($productos);
        $totalPaginas = max(1, (int) ceil($total / max(1, $porPagina)));
        $paginaActual = min(max(1, $pagina), $totalPaginas);
        $slice = array_slice($productos, ($paginaActual - 1) * $porPagina, $porPagina);

        return [
            'items' => $slice,
            'total' => $total,
            'totalPaginas' => $totalPaginas,
            'pagina' => $paginaActual,
            'filtroAbc' => $filtroAbc,
        ];
    }

    /**
     * Prepara el estado del modal de creación o edición de producto (incluyendo datos sticky tras error).
     *
     * @param array<string, mixed> $postSticky
     * @return array{editando:?array<string,mixed>,esAlta:bool}
     */
    public function prepararModalAdmin(bool $modoNuevo, int $editarId, array $postSticky = [], int $postId = 0): array
    {
        if ($modoNuevo) {
            $editando = $this->plantillaNuevaAdmin();
            if ($postSticky !== []) {
                $editando = $this->aplicarCamposAdmin($editando, [
                    'nombre' => (string) ($postSticky['nombre'] ?? ''),
                    'ean' => (string) ($postSticky['ean'] ?? ''),
                    'unidad' => (string) ($postSticky['unidad'] ?? ''),
                    'categoria' => (string) ($postSticky['categoria'] ?? ''),
                    'subcategoria' => (string) ($postSticky['subcategoria'] ?? ''),
                    'descripcion' => (string) ($postSticky['descripcion'] ?? ''),
                    'marca' => (string) ($postSticky['marca'] ?? 'MASS'),
                    'precio' => (float) ($postSticky['precio'] ?? 0),
                    'costo' => (float) ($postSticky['costo'] ?? 0),
                    'puntoReorden' => (int) ($postSticky['puntoReorden'] ?? 0),
                    'capacidadGondola' => (int) ($postSticky['capacidadGondola'] ?? 0),
                    'abc' => (string) ($postSticky['abc'] ?? ''),
                    'activo' => !empty($postSticky['activo']) || !empty($postSticky['activo_ui']),
                ]);
                $editando['id'] = 0;
                $editando['esNuevo'] = true;
            }
            return ['editando' => $editando, 'esAlta' => true];
        }

        if ($editarId > 0) {
            $editando = $this->obtenerAdminPorId($editarId);
            if ($editando && $postSticky !== [] && $postId === $editarId) {
                $editando = $this->aplicarCamposAdmin($editando, [
                    'nombre' => (string) ($postSticky['nombre'] ?? $editando['nombre']),
                    'ean' => (string) ($postSticky['ean'] ?? $editando['ean']),
                    'unidad' => (string) ($postSticky['unidad'] ?? $editando['unidad']),
                    'categoria' => (string) ($postSticky['categoria'] ?? $editando['categoria']),
                    'subcategoria' => (string) ($postSticky['subcategoria'] ?? $editando['subcategoria']),
                    'descripcion' => (string) ($postSticky['descripcion'] ?? $editando['descripcion']),
                    'precio' => (float) ($postSticky['precio'] ?? $editando['precio']),
                    'costo' => (float) ($postSticky['costo'] ?? $editando['costo']),
                    'puntoReorden' => (int) ($postSticky['puntoReorden'] ?? $editando['puntoReorden']),
                    'capacidadGondola' => (int) ($postSticky['capacidadGondola'] ?? $editando['capacidadGondola']),
                    'abc' => (string) ($postSticky['abc'] ?? $editando['abc']),
                    'activo' => !empty($postSticky['activo']) || !empty($postSticky['activo_ui']),
                ]);
            }
            return ['editando' => $editando, 'esAlta' => false];
        }

        return ['editando' => null, 'esAlta' => false];
    }

    /**
     * Etiqueta legible del estado de stock en góndola.
     */
    public function etiquetaStockGondola(string $stockTone): string
    {
        return match ($stockTone) {
            'bajo' => 'Stock bajo en góndola',
            'critico' => 'Quiebre / crítico',
            default => 'Nivel estable',
        };
    }

    /**
     * Retorna el ranking de productos con mayor rotación diaria para el dashboard WMS.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerTopRotacion(): array
    {
        return [
            ['nombre' => 'Aceite Vegetal Primor 1L', 'unidades' => 842, 'monto' => 12630.00, 'abc' => 'A'],
            ['nombre' => 'Arroz Costeño 5kg', 'unidades' => 610, 'monto' => 10980.00, 'abc' => 'A'],
            ['nombre' => 'Leche Gloria Evaporada', 'unidades' => 1284, 'monto' => 6420.00, 'abc' => 'A'],
            ['nombre' => 'Huevos Pardos x12', 'unidades' => 496, 'monto' => 4464.00, 'abc' => 'B'],
            ['nombre' => 'Azúcar Rubia 1kg', 'unidades' => 388, 'monto' => 3104.00, 'abc' => 'B'],
        ];
    }
}

