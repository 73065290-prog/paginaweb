<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/repositories/InventarioRepository.php';

/**
 * Servicio de negocio del módulo de Inventario (góndolas, reorden, clasificación ABC y stock por tienda).
 */
final class InventarioService
{
    private InventarioRepository $inventarioRepository;
    private TiendaRepository $tiendaRepository;

    public function __construct(
        ?InventarioRepository $inventarioRepository = null,
        ?TiendaRepository $tiendaRepository = null,
    ) {
        $this->inventarioRepository = $inventarioRepository ?? new InventarioRepository();
        $this->tiendaRepository = $tiendaRepository ?? new TiendaRepository();
    }

    public function repository(): InventarioRepository
    {
        return $this->inventarioRepository;
    }

    /**
     * Retorna el stock por tienda desde InventarioRepository (si hay BD) mapeado mediante InventarioItem.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerStockPorTienda(string $tiendaId): array
    {
        if (Database::connected()) {
            $rows = $this->inventarioRepository->byTienda($tiendaId);
            if ($rows !== []) {
                return array_map(
                    static fn (array $r): array => InventarioItem::fromRow($r)->toArray(),
                    $rows
                );
            }
        }
        return [];
    }

    /**
     * Actualiza el stock de piso y almacén de un producto en una tienda mediante InventarioRepository.
     */
    public function actualizarStockPorTienda(int $productoId, string $tiendaId, int $piso, int $almacen): bool
    {
        if ($piso < 0 || $almacen < 0) {
            return false;
        }
        return $this->inventarioRepository->updateStock($productoId, $tiendaId, $piso, $almacen);
    }

    /**
     * Evalúa si un SKU requiere reposición según su punto de reorden y capacidad de góndola.
     *
     * @return array{requiereReorden:bool,estado:string,cantidadSugerida:int}
     */
    public function evaluarReorden(int $stockPiso, int $stockAlmacen, int $puntoReorden, int $capacidadGondola): array
    {
        $total = $stockPiso + $stockAlmacen;
        if ($stockPiso <= 0 || $total <= 0) {
            $estado = 'critico';
        } elseif ($stockPiso <= $puntoReorden || $total <= $puntoReorden) {
            $estado = 'bajo';
        } else {
            $estado = 'ok';
        }

        $requiere = $estado !== 'ok';
        $sugerida = $requiere ? max(0, $capacidadGondola - $stockPiso) : 0;

        return [
            'requiereReorden' => $requiere,
            'estado' => $estado,
            'cantidadSugerida' => $sugerida,
        ];
    }

    /**
     * Retorna el listado de tiendas habilitadas desde TiendaRepository (si hay BD) o el fallback demo.
     *
     * @param array<int, array<string, mixed>> $fallbackDemo
     * @return array<int, array<string, mixed>>
     */
    public function listarTiendas(array $fallbackDemo = []): array
    {
        if (Database::connected()) {
            $rows = $this->tiendaRepository->all();
            if ($rows !== []) {
                return $rows;
            }
        }
        return $fallbackDemo;
    }

    /**
     * Busca una tienda por su código/slug.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerTiendaPorId(string $id): ?array
    {
        foreach (mass_tiendas() as $t) {
            if (($t['id'] ?? '') === $id) {
                return $t;
            }
        }
        return null;
    }

    /**
     * Retorna los KPIs operativos del dashboard e inventario WMS.
     *
     * @return array<string, float|int|string>
     */
    public function obtenerKpis(): array
    {
        return [
            'ventaDia' => 482910.50,
            'ventaTrend' => 3.4,
            'pedidosCurso' => 142,
            'pedidosPicking' => 64,
            'pedidosRuta' => 38,
            'pedidosListos' => 40,
            'stockCritico' => 28,
            'quiebresGondola' => 6,
            'sugerenciaOc' => 19,
            'conteosPasillos' => 3,
            'mermaPct' => 0.82,
            'mermaMonto' => 3850.00,
            'inventarioTotal' => 3450000.00,
            'ventaNeta' => 482910.50,
            'boletas' => 184,
            'ticketPromedio' => 100.27,
            'pickingPct' => 72,
            'tiempoPromedioMin' => 14.2,
            'disponibilidad' => 96.4,
            'disponibilidadEstado' => 'Óptimo',
            'aprovechamiento' => 1420.50,
            'mermaEvitada' => 890.00,
            'mermaProyectada' => 320.00,
        ];
    }

    /**
     * Retorna la distribución porcentual y de SKUs por clasificación ABC.
     *
     * @return array<int, array<string, int|string>>
     */
    public function obtenerClasificacionAbc(): array
    {
        return [
            ['id' => 'a', 'nombre' => 'Clase A · Alta rotación', 'pct' => 21, 'skus' => 432, 'color' => '#006b45'],
            ['id' => 'b', 'nombre' => 'Clase B · Rotación media', 'pct' => 26, 'skus' => 526, 'color' => '#ff8a3d'],
            ['id' => 'c', 'nombre' => 'Clase C · Baja rotación', 'pct' => 53, 'skus' => 1086, 'color' => '#f5c542'],
        ];
    }

    /**
     * Retorna el total de SKUs auditados en la matriz ABC.
     */
    public function obtenerTotalAbc(): int
    {
        return 2044;
    }

    /**
     * Retorna el resumen de salud de stock global del WMS.
     *
     * @return array<int, array<string, int|string>>
     */
    public function obtenerStockGlobal(): array
    {
        return [
            ['id' => 'normal', 'label' => 'Normal / Saludable', 'cantidad' => 1824, 'color' => '#006b45'],
            ['id' => 'bajo', 'label' => 'Stock bajo', 'cantidad' => 202, 'color' => '#ff8a3d'],
            ['id' => 'agotado', 'label' => 'Agotado / Quiebre', 'cantidad' => 18, 'color' => '#e53935'],
        ];
    }

    /**
     * Retorna las alertas de reposición y quiebre de góndola enriquecidas con datos de catálogo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerAlertasGondola(): array
    {
        $productos = mass_productos();
        $map = [];
        foreach ($productos as $p) {
            $map[(int) $p['id']] = $p;
        }

        $rows = [
            [
                'id' => 2,
                'ean' => '7750243001829',
                'piso' => 4,
                'almacen' => 0,
                'minimo' => 18,
                'abc' => 'A',
                'ubicacion' => 'Góndola B4 · Pasillo 02',
                'estado' => 'critico',
                'estadoLabel' => 'Quiebre detectado',
                'accion' => 'Generar OC',
                'filtro' => 'agotados',
            ],
            [
                'id' => 1,
                'ean' => '7750885012345',
                'piso' => 8,
                'almacen' => 6,
                'minimo' => 24,
                'abc' => 'A',
                'ubicacion' => 'Góndola A2 · Pasillo 01',
                'estado' => 'bajo',
                'estadoLabel' => 'Stock crítico',
                'accion' => 'Pedir +10',
                'filtro' => 'critico',
            ],
            [
                'id' => 11,
                'ean' => '7751234009876',
                'piso' => 2,
                'almacen' => 12,
                'minimo' => 20,
                'abc' => 'B',
                'ubicacion' => 'Góndola C1 · Pasillo 05',
                'estado' => 'bajo',
                'estadoLabel' => 'Stock crítico',
                'accion' => 'Pedir +15',
                'filtro' => 'critico',
            ],
            [
                'id' => 8,
                'ean' => '7750001122334',
                'piso' => 0,
                'almacen' => 3,
                'minimo' => 16,
                'abc' => 'A',
                'ubicacion' => 'Almacén · Rack 07',
                'estado' => 'critico',
                'estadoLabel' => 'Quiebre detectado',
                'accion' => 'Solicitar lote',
                'filtro' => 'agotados',
            ],
            [
                'id' => 3,
                'ean' => '7754567890123',
                'piso' => 14,
                'almacen' => 22,
                'minimo' => 12,
                'abc' => 'B',
                'ubicacion' => 'Góndola D3 · Pasillo 04',
                'estado' => 'vencer',
                'estadoLabel' => 'Próximo a vencer',
                'accion' => 'Revisar lote',
                'filtro' => 'vencer',
            ],
            [
                'id' => 5,
                'ean' => '7759988776655',
                'piso' => 20,
                'almacen' => 40,
                'minimo' => 15,
                'abc' => 'C',
                'ubicacion' => 'Góndola E1 · Pasillo 06',
                'estado' => 'ok',
                'estadoLabel' => 'OC generada',
                'accion' => 'Ver OC',
                'filtro' => 'todos',
            ],
        ];

        $out = [];
        foreach ($rows as $r) {
            $p = $map[$r['id']] ?? null;
            if (!$p) {
                continue;
            }
            $out[] = array_merge($r, [
                'nombre' => $p['nombre'],
                'sku' => 'SKU-' . str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT),
                'img' => $p['img'],
                'categoria' => $p['categoria'] ?? ($p['marca'] ?? ''),
                'marca' => $p['marca'],
            ]);
        }
        return $out;
    }

    /**
     * Retorna las filas base de control de góndola y trastienda.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarGondola(): array
    {
        $productos = mass_productos();
        $map = [];
        foreach ($productos as $p) {
            $map[(int) $p['id']] = $p;
        }

        $rows = [
            ['id' => 1, 'gondola' => 18, 'trastienda' => 24, 'estado' => 'ok'],
            ['id' => 2, 'gondola' => 6, 'trastienda' => 12, 'estado' => 'bajo'],
            ['id' => 11, 'gondola' => 0, 'trastienda' => 4, 'estado' => 'critico'],
            ['id' => 3, 'gondola' => 22, 'trastienda' => 30, 'estado' => 'ok'],
            ['id' => 8, 'gondola' => 3, 'trastienda' => 0, 'estado' => 'critico'],
        ];

        $out = [];
        foreach ($rows as $r) {
            $p = $map[$r['id']] ?? null;
            if (!$p) {
                continue;
            }
            $out[] = array_merge($r, [
                'nombre' => $p['nombre'],
                'sku' => 'SKU-' . str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT),
                'img' => $p['img'],
                'marca' => $p['marca'],
            ]);
        }
        return $out;
    }

    /**
     * Construye y filtra el listado completo de inventario y góndolas para la vista administrativa.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarInventarioFiltrado(string $q = '', string $estado = 'todos'): array
    {
        $filas = $this->listarGondola();
        $idsGondola = array_column($filas, 'id');
        $extra = [];

        foreach (mass_productos() as $p) {
            if (in_array((int) $p['id'], $idsGondola, true)) {
                continue;
            }
            $esBajo = ($p['stockTone'] ?? 'ok') === 'bajo';
            $extra[] = [
                'id' => (int) $p['id'],
                'nombre' => $p['nombre'],
                'sku' => 'SKU-' . str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT),
                'img' => $p['img'],
                'marca' => $p['marca'],
                'gondola' => $esBajo ? 5 : 20,
                'trastienda' => $esBajo ? 3 : 15,
                'estado' => $esBajo ? 'bajo' : 'ok',
            ];
        }

        $filas = array_merge($filas, $extra);
        $qNorm = mb_strtolower(trim($q));

        if ($qNorm !== '') {
            $filas = array_values(array_filter(
                $filas,
                static function (array $r) use ($qNorm): bool {
                    $hay = mb_strtolower($r['nombre'] . ' ' . $r['sku'] . ' ' . $r['marca']);
                    return str_contains($hay, $qNorm);
                }
            ));
        }

        if (in_array($estado, ['ok', 'bajo', 'critico'], true)) {
            $filas = array_values(array_filter(
                $filas,
                static fn (array $r): bool => ($r['estado'] ?? '') === $estado
            ));
        }

        return $filas;
    }

    /**
     * Etiqueta legible para la píldora de estado de inventario.
     */
    public function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'ok' => 'Stock OK',
            'bajo' => 'Bajo',
            default => 'Crítico',
        };
    }
}

