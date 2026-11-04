<?php
declare(strict_types=1);

/**
 * Operaciones del menú WMS: inventario y góndolas, picking, conteos,
 * mermas/caducidades, reportes y configuración de umbrales.
 * El estado mutable vive en sesión, igual que los overrides de producto.
 */

function mass_ops_hoy(): string
{
    return '2024-10-24';
}

function mass_ops_sku(int $id): string
{
    return 'SKU-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
}

/**
 * @return array{umbralBajo: int, umbralCriticoPiso: int, diasCaducidad: int, slaPickingMin: int, alertasActivas: bool}
 */
function mass_ops_config_defaults(): array
{
    return [
        'umbralBajo' => 8,
        'umbralCriticoPiso' => 0,
        'diasCaducidad' => 7,
        'slaPickingMin' => 15,
        'alertasActivas' => true,
    ];
}

/**
 * @return array{umbralBajo: int, umbralCriticoPiso: int, diasCaducidad: int, slaPickingMin: int, alertasActivas: bool}
 */
function mass_ops_config(): array
{
    $cfg = mass_ops_config_defaults();
    $saved = $_SESSION['admin_ops_config'] ?? null;
    if (!is_array($saved)) {
        return $cfg;
    }
    foreach (['umbralBajo', 'umbralCriticoPiso', 'diasCaducidad', 'slaPickingMin'] as $key) {
        if (isset($saved[$key]) && is_numeric($saved[$key])) {
            $cfg[$key] = (int) $saved[$key];
        }
    }
    if (array_key_exists('alertasActivas', $saved)) {
        $cfg['alertasActivas'] = (bool) $saved['alertasActivas'];
    }
    return $cfg;
}

/**
 * @param array<string, mixed> $data
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_config_guardar(array $data): array
{
    $bajo = mass_ops_entero($data['umbralBajo'] ?? null, 1, 200);
    $crit = mass_ops_entero($data['umbralCriticoPiso'] ?? null, 0, 50);
    $dias = mass_ops_entero($data['diasCaducidad'] ?? null, 1, 90);
    $sla = mass_ops_entero($data['slaPickingMin'] ?? null, 1, 180);
    $errores = [];
    if ($bajo === null) {
        $errores[] = 'El umbral de stock bajo debe estar entre 1 y 200 unidades en góndola.';
    }
    if ($crit === null) {
        $errores[] = 'El umbral crítico de góndola debe estar entre 0 y 50.';
    }
    if ($bajo !== null && $crit !== null && $crit >= $bajo) {
        $errores[] = 'El umbral crítico debe ser menor que el umbral de stock bajo.';
    }
    if ($dias === null) {
        $errores[] = 'Los días de alerta de caducidad deben estar entre 1 y 90.';
    }
    if ($sla === null) {
        $errores[] = 'El SLA de picking debe estar entre 1 y 180 minutos.';
    }
    if ($errores !== []) {
        return ['ok' => false, 'errores' => $errores];
    }
    $_SESSION['admin_ops_config'] = [
        'umbralBajo' => $bajo,
        'umbralCriticoPiso' => $crit,
        'diasCaducidad' => $dias,
        'slaPickingMin' => $sla,
        'alertasActivas' => (string) ($data['alertasActivas'] ?? '') === '1',
    ];
    return ['ok' => true, 'errores' => []];
}

function mass_ops_entero(mixed $value, int $min, int $max): ?int
{
    if (is_int($value)) {
        $n = $value;
    } elseif (is_string($value) && preg_match('/^\d+$/', $value) === 1) {
        $n = (int) $value;
    } else {
        return null;
    }
    if ($n < $min || $n > $max) {
        return null;
    }
    return $n;
}

function mass_ops_fecha(mixed $value): ?string
{
    $value = trim((string) $value);
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    if (!$dt || $dt->format('Y-m-d') !== $value) {
        return null;
    }
    return $value;
}

function mass_ops_estado_stock(int $gondola, int $trastienda): string
{
    $cfg = mass_ops_config();
    $bajo = (int) $cfg['umbralBajo'];
    $crit = (int) $cfg['umbralCriticoPiso'];
    if ($gondola <= $crit || ($gondola + $trastienda) <= 0) {
        return 'critico';
    }
    if ($trastienda <= 0 && $gondola < $bajo) {
        return 'critico';
    }
    if ($gondola < $bajo) {
        return 'bajo';
    }
    return 'ok';
}

function mass_ops_estado_stock_label(string $estado): string
{
    return match ($estado) {
        'ok' => 'Stock OK',
        'bajo' => 'Bajo',
        default => 'Crítico',
    };
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_ops_inventario(): array
{
    $filas = mass_admin_gondola();
    $ids = array_map(static fn (array $r): int => (int) $r['id'], $filas);
    foreach (mass_productos() as $p) {
        $id = (int) $p['id'];
        if (in_array($id, $ids, true)) {
            continue;
        }
        $tone = (string) ($p['stockTone'] ?? 'ok');
        $filas[] = [
            'id' => $id,
            'nombre' => $p['nombre'],
            'sku' => mass_ops_sku($id),
            'img' => $p['img'],
            'marca' => $p['marca'],
            'gondola' => $tone === 'bajo' ? 5 : 20,
            'trastienda' => $tone === 'bajo' ? 3 : 15,
            'estado' => $tone === 'bajo' ? 'bajo' : 'ok',
        ];
    }

    $overrides = $_SESSION['admin_ops_stock'] ?? [];
    if (!is_array($overrides)) {
        $overrides = [];
    }

    $out = [];
    foreach ($filas as $row) {
        $id = (int) $row['id'];
        $over = $overrides[$id] ?? $overrides[(string) $id] ?? null;
        if (is_array($over)) {
            $row['gondola'] = max(0, (int) ($over['gondola'] ?? 0));
            $row['trastienda'] = max(0, (int) ($over['trastienda'] ?? 0));
        }
        $row['id'] = $id;
        $row['gondola'] = (int) $row['gondola'];
        $row['trastienda'] = (int) $row['trastienda'];
        $row['sku'] = (string) ($row['sku'] ?? mass_ops_sku($id));
        $row['estado'] = mass_ops_estado_stock($row['gondola'], $row['trastienda']);
        $out[] = $row;
    }
    return $out;
}

/**
 * @return array<string, mixed>|null
 */
function mass_ops_inventario_por_id(int $id): ?array
{
    foreach (mass_ops_inventario() as $row) {
        if ((int) $row['id'] === $id) {
            return $row;
        }
    }
    return null;
}

function mass_ops_stock_guardar(int $id, int $gondola, int $trastienda): void
{
    if (!isset($_SESSION['admin_ops_stock']) || !is_array($_SESSION['admin_ops_stock'])) {
        $_SESSION['admin_ops_stock'] = [];
    }
    $_SESSION['admin_ops_stock'][$id] = [
        'gondola' => max(0, $gondola),
        'trastienda' => max(0, $trastienda),
    ];
}

/**
 * @return array{ok: bool, errores: string[], movidas?: int}
 */
function mass_ops_reponer(int $id): array
{
    $row = mass_ops_inventario_por_id($id);
    if ($row === null) {
        return ['ok' => false, 'errores' => ['El SKU no está en el control de góndola.']];
    }
    $objetivo = (int) mass_ops_config()['umbralBajo'] + 4;
    $gondola = (int) $row['gondola'];
    $trastienda = (int) $row['trastienda'];
    $falta = max(0, $objetivo - $gondola);
    if ($falta === 0) {
        return ['ok' => false, 'errores' => ['La góndola ya cubre el umbral de reposición.']];
    }
    if ($trastienda <= 0) {
        return ['ok' => false, 'errores' => ['No hay stock en trastienda para reponer.']];
    }
    $mover = min($trastienda, $falta);
    mass_ops_stock_guardar($id, $gondola + $mover, $trastienda - $mover);
    return ['ok' => true, 'errores' => [], 'movidas' => $mover];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_ajustar(int $id, mixed $gondolaRaw, mixed $trastiendaRaw): array
{
    $row = mass_ops_inventario_por_id($id);
    if ($row === null) {
        return ['ok' => false, 'errores' => ['El SKU no está en el control de góndola.']];
    }
    $gondola = mass_ops_entero($gondolaRaw, 0, 9999);
    $trastienda = mass_ops_entero($trastiendaRaw, 0, 9999);
    $errores = [];
    if ($gondola === null) {
        $errores[] = 'La cantidad de góndola debe ser un entero entre 0 y 9999.';
    }
    if ($trastienda === null) {
        $errores[] = 'La cantidad de trastienda debe ser un entero entre 0 y 9999.';
    }
    if ($errores !== []) {
        return ['ok' => false, 'errores' => $errores];
    }
    mass_ops_stock_guardar($id, $gondola, $trastienda);
    return ['ok' => true, 'errores' => []];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_descontar_gondola(int $id, int $cantidad): array
{
    if ($cantidad < 1) {
        return ['ok' => false, 'errores' => ['La cantidad a descontar debe ser al menos 1.']];
    }
    $row = mass_ops_inventario_por_id($id);
    if ($row === null) {
        return ['ok' => false, 'errores' => ['El SKU no está en el control de góndola.']];
    }
    if ((int) $row['gondola'] < $cantidad) {
        return ['ok' => false, 'errores' => ['No hay suficiente stock en góndola para descontar ' . $cantidad . ' unidades.']];
    }
    mass_ops_stock_guardar($id, (int) $row['gondola'] - $cantidad, (int) $row['trastienda']);
    return ['ok' => true, 'errores' => []];
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_ops_pedidos(): array
{
    $overrides = $_SESSION['admin_ops_pedidos'] ?? [];
    if (!is_array($overrides)) {
        $overrides = [];
    }
    $labels = [
        'preparacion' => 'En preparación',
        'por_recoger' => 'Por recoger',
        'entregado' => 'Entregado',
        'retirado' => 'Retirado',
    ];
    $cfg = mass_ops_config();
    $out = [];
    foreach (mass_admin_pedidos() as $pedido) {
        $codigo = strtoupper((string) ($pedido['codigo'] ?? ''));
        $estado = (string) ($pedido['estado'] ?? '');
        if (isset($overrides[$codigo], $labels[$overrides[$codigo]])) {
            $estado = (string) $overrides[$codigo];
            $pedido['estado'] = $estado;
            $pedido['estadoLabel'] = $labels[$estado];
            if (!in_array($estado, ['preparacion', 'por_recoger'], true)) {
                $pedido['mins'] = 0;
            }
        }
        $pedido['codigo'] = $codigo;
        $pedido['fueraSla'] = (bool) $cfg['alertasActivas']
            && in_array($estado, ['preparacion', 'por_recoger'], true)
            && (int) ($pedido['mins'] ?? 0) > (int) $cfg['slaPickingMin'];
        $out[] = $pedido;
    }
    return $out;
}

function mass_ops_picking_activos(): int
{
    return count(array_filter(
        mass_ops_pedidos(),
        static fn (array $p): bool => in_array($p['estado'], ['preparacion', 'por_recoger'], true)
    ));
}

function mass_ops_pedido_accion_label(array $pedido): ?string
{
    $modo = (string) ($pedido['modo'] ?? '');
    return match ((string) ($pedido['estado'] ?? '')) {
        'preparacion' => 'Iniciar picking',
        'por_recoger' => $modo === 'express' ? 'Marcar entregado' : 'Marcar retirado',
        default => null,
    };
}

/**
 * @return array<string, mixed>|null
 */
function mass_ops_pedido_por_codigo(string $codigo): ?array
{
    $codigo = strtoupper(trim($codigo));
    if ($codigo === '') {
        return null;
    }
    foreach (mass_ops_pedidos() as $pedido) {
        if ((string) $pedido['codigo'] === $codigo) {
            return $pedido;
        }
    }
    return null;
}

function mass_ops_ubicacion(int $id): string
{
    foreach (mass_admin_alertas_gondola() as $alerta) {
        if ((int) $alerta['id'] === $id) {
            return (string) $alerta['ubicacion'];
        }
    }
    foreach (mass_productos() as $producto) {
        if ((int) $producto['id'] === $id) {
            return 'Góndola · ' . (string) ($producto['categoria'] ?? 'General');
        }
    }
    return 'Góndola general';
}

/**
 * @param array<string, mixed> $pedido
 * @return array<int, array<string, mixed>>
 */
function mass_ops_pedido_ruta(array $pedido): array
{
    $productos = array_values(mass_productos());
    if ($productos === []) {
        return [];
    }
    $n = max(1, min(12, (int) ($pedido['items'] ?? 1)));
    $lineas = [];
    for ($i = 0; $i < $n; $i++) {
        $producto = $productos[$i % count($productos)];
        $id = (int) $producto['id'];
        $inv = mass_ops_inventario_por_id($id);
        $lineas[] = [
            'id' => $id,
            'sku' => mass_ops_sku($id),
            'nombre' => (string) $producto['nombre'],
            'img' => (string) ($producto['img'] ?? ''),
            'cantidad' => 1,
            'ubicacion' => mass_ops_ubicacion($id),
            'gondola' => (int) ($inv['gondola'] ?? 0),
        ];
    }
    return $lineas;
}

/**
 * @return array{ok: bool, errores: string[], estado?: string}
 */
function mass_ops_pedido_avanzar(string $codigo): array
{
    $pedido = mass_ops_pedido_por_codigo($codigo);
    if ($pedido === null) {
        return ['ok' => false, 'errores' => ['Pedido no encontrado.']];
    }
    $modo = (string) ($pedido['modo'] ?? 'retiro');
    $siguiente = match ((string) $pedido['estado']) {
        'preparacion' => 'por_recoger',
        'por_recoger' => $modo === 'express' ? 'entregado' : 'retirado',
        default => null,
    };
    if ($siguiente === null) {
        return ['ok' => false, 'errores' => ['Este pedido ya está cerrado.']];
    }
    if (!isset($_SESSION['admin_ops_pedidos']) || !is_array($_SESSION['admin_ops_pedidos'])) {
        $_SESSION['admin_ops_pedidos'] = [];
    }
    $_SESSION['admin_ops_pedidos'][(string) $pedido['codigo']] = $siguiente;
    return ['ok' => true, 'errores' => [], 'estado' => $siguiente];
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function mass_ops_linea_conteo(array $row, mixed $contado = null): array
{
    return [
        'id' => (int) $row['id'],
        'sku' => (string) $row['sku'],
        'nombre' => (string) $row['nombre'],
        'sistema' => (int) $row['gondola'],
        'contado' => $contado === null ? null : (int) $contado,
    ];
}

/**
 * @param array<int, array<string, mixed>> $inventario
 * @param array<int, int> $ids
 * @return array<int, array<string, mixed>>
 */
function mass_ops_lineas_conteo(array $inventario, array $ids): array
{
    $map = [];
    foreach ($inventario as $row) {
        $map[(int) $row['id']] = $row;
    }
    $lineas = [];
    foreach ($ids as $id) {
        if (isset($map[$id])) {
            $lineas[] = mass_ops_linea_conteo($map[$id]);
        }
    }
    if ($lineas === []) {
        foreach (array_slice($inventario, 0, 3) as $row) {
            $lineas[] = mass_ops_linea_conteo($row);
        }
    }
    return $lineas;
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_ops_conteos_seed(): array
{
    $inventario = mass_ops_inventario();
    $abarrotes = mass_ops_lineas_conteo($inventario, [1, 2, 11]);
    $frescos = mass_ops_lineas_conteo($inventario, [8, 4]);
    $lacteos = mass_ops_lineas_conteo($inventario, [3, 7]);
    if (isset($frescos[0])) {
        $frescos[0]['contado'] = (int) $frescos[0]['sistema'];
    }
    foreach ($lacteos as $i => $linea) {
        $lacteos[$i]['contado'] = (int) $linea['sistema'];
    }

    return [
        [
            'id' => 'CC-2410',
            'pasillo' => 'Pasillo 01 · Abarrotes',
            'tipo' => 'Cíclico',
            'estado' => 'pendiente',
            'responsable' => 'Sin asignar',
            'programado' => '2024-10-24 08:00',
            'cerradoEn' => null,
            'resultado' => null,
            'lineas' => $abarrotes,
        ],
        [
            'id' => 'CC-2411',
            'pasillo' => 'Pasillo 04 · Frescos',
            'tipo' => 'Cíclico',
            'estado' => 'en_curso',
            'responsable' => 'Ing. Roberto Silva',
            'programado' => '2024-10-24 10:30',
            'cerradoEn' => null,
            'resultado' => null,
            'lineas' => $frescos,
        ],
        [
            'id' => 'CC-2408',
            'pasillo' => 'Pasillo 02 · Lácteos',
            'tipo' => 'Auditoría',
            'estado' => 'cerrado',
            'responsable' => 'Ing. Roberto Silva',
            'programado' => '2024-10-23 16:00',
            'cerradoEn' => '2024-10-23 17:10',
            'resultado' => 'cuadrado',
            'lineas' => $lacteos,
        ],
    ];
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_ops_conteos(): array
{
    $lista = $_SESSION['admin_ops_conteos'] ?? null;
    if (!is_array($lista)) {
        $lista = mass_ops_conteos_seed();
        $_SESSION['admin_ops_conteos'] = $lista;
    }
    return $lista;
}

/**
 * @param array<int, array<string, mixed>> $lista
 */
function mass_ops_conteos_guardar(array $lista): void
{
    $_SESSION['admin_ops_conteos'] = array_values($lista);
}

/**
 * @return array<string, mixed>|null
 */
function mass_ops_conteo_por_id(string $id): ?array
{
    foreach (mass_ops_conteos() as $conteo) {
        if ((string) ($conteo['id'] ?? '') === $id) {
            return $conteo;
        }
    }
    return null;
}

/**
 * @param array<string, mixed> $conteo
 * @return array{pendientes: int, diferencias: int}
 */
function mass_ops_conteo_resumen(array $conteo): array
{
    $pendientes = 0;
    $diferencias = 0;
    foreach ($conteo['lineas'] as $linea) {
        if ($linea['contado'] === null || $linea['contado'] === '') {
            $pendientes++;
            continue;
        }
        if ((int) $linea['contado'] !== (int) $linea['sistema']) {
            $diferencias++;
        }
    }
    return ['pendientes' => $pendientes, 'diferencias' => $diferencias];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_conteo_iniciar(string $id): array
{
    $lista = mass_ops_conteos();
    foreach ($lista as $i => $conteo) {
        if ((string) $conteo['id'] !== $id) {
            continue;
        }
        if ((string) $conteo['estado'] !== 'pendiente') {
            return ['ok' => false, 'errores' => ['Solo se puede iniciar un conteo pendiente.']];
        }
        $conteo['estado'] = 'en_curso';
        $conteo['responsable'] = (string) (mass_admin_usuario()['nombre'] ?? 'Jefe de Operaciones');
        $lista[$i] = $conteo;
        mass_ops_conteos_guardar($lista);
        return ['ok' => true, 'errores' => []];
    }
    return ['ok' => false, 'errores' => ['Conteo no encontrado.']];
}

/**
 * @param array<string, mixed> $contados
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_conteo_registrar(string $id, array $contados): array
{
    $lista = mass_ops_conteos();
    foreach ($lista as $i => $conteo) {
        if ((string) $conteo['id'] !== $id) {
            continue;
        }
        if ((string) $conteo['estado'] !== 'en_curso') {
            return ['ok' => false, 'errores' => ['El conteo debe estar en curso para registrar unidades.']];
        }
        $errores = [];
        foreach ($conteo['lineas'] as $n => $linea) {
            $sku = (string) $linea['sku'];
            if (!array_key_exists($sku, $contados) || trim((string) $contados[$sku]) === '') {
                continue;
            }
            $valor = mass_ops_entero($contados[$sku], 0, 9999);
            if ($valor === null) {
                $errores[] = 'La cantidad contada de ' . $sku . ' debe ser un entero entre 0 y 9999.';
                continue;
            }
            $conteo['lineas'][$n]['contado'] = $valor;
        }
        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores];
        }
        $lista[$i] = $conteo;
        mass_ops_conteos_guardar($lista);
        return ['ok' => true, 'errores' => []];
    }
    return ['ok' => false, 'errores' => ['Conteo no encontrado.']];
}

/**
 * @return array{ok: bool, errores: string[], resultado?: string}
 */
function mass_ops_conteo_cerrar(string $id, bool $aplicarAjuste): array
{
    $lista = mass_ops_conteos();
    foreach ($lista as $i => $conteo) {
        if ((string) $conteo['id'] !== $id) {
            continue;
        }
        if ((string) $conteo['estado'] !== 'en_curso') {
            return ['ok' => false, 'errores' => ['Solo se cierra un conteo que está en curso.']];
        }
        $resumen = mass_ops_conteo_resumen($conteo);
        if ($resumen['pendientes'] > 0) {
            return ['ok' => false, 'errores' => ['Faltan ' . $resumen['pendientes'] . ' líneas por contar.']];
        }
        if ($aplicarAjuste) {
            foreach ($conteo['lineas'] as $linea) {
                $row = mass_ops_inventario_por_id((int) $linea['id']);
                if ($row === null) {
                    continue;
                }
                mass_ops_stock_guardar((int) $linea['id'], (int) $linea['contado'], (int) $row['trastienda']);
            }
        }
        $conteo['estado'] = 'cerrado';
        $conteo['cerradoEn'] = mass_ops_hoy() . ' ' . date('H:i');
        $conteo['resultado'] = $resumen['diferencias'] > 0 ? 'diferencia' : 'cuadrado';
        $lista[$i] = $conteo;
        mass_ops_conteos_guardar($lista);
        return ['ok' => true, 'errores' => [], 'resultado' => (string) $conteo['resultado']];
    }
    return ['ok' => false, 'errores' => ['Conteo no encontrado.']];
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_ops_mermas_seed(): array
{
    $porId = [];
    foreach (mass_ops_inventario() as $row) {
        $porId[(int) $row['id']] = $row;
    }
    $fila = static function (int $id, array $extra) use ($porId): ?array {
        $row = $porId[$id] ?? null;
        if ($row === null) {
            return null;
        }
        return array_merge([
            'id' => '',
            'productoId' => (int) $row['id'],
            'sku' => (string) $row['sku'],
            'nombre' => (string) $row['nombre'],
            'img' => (string) ($row['img'] ?? ''),
            'tipo' => 'merma',
            'motivo' => 'Desmedro',
            'cantidad' => 1,
            'vence' => '',
            'estado' => 'abierta',
            'descontada' => false,
            'registrada' => mass_ops_hoy(),
        ], $extra);
    };
    $rows = array_values(array_filter([
        $fila(11, [
            'id' => 'MR-2201',
            'tipo' => 'caducidad',
            'motivo' => 'Vencido',
            'cantidad' => 2,
            'vence' => '2024-10-22',
        ]),
        $fila(3, [
            'id' => 'MR-2202',
            'tipo' => 'caducidad',
            'motivo' => 'Próximo a vencer',
            'cantidad' => 3,
            'vence' => '2024-10-28',
        ]),
        $fila(8, [
            'id' => 'MR-2203',
            'tipo' => 'merma',
            'motivo' => 'Daño en góndola',
            'cantidad' => 1,
        ]),
        $fila(1, [
            'id' => 'MR-2204',
            'tipo' => 'merma',
            'motivo' => 'Desmedro',
            'cantidad' => 2,
            'estado' => 'retirada',
            'descontada' => true,
        ]),
    ]));
    return $rows;
}

/**
 * @return array<int, array<string, mixed>>
 */
function mass_ops_mermas(): array
{
    $lista = $_SESSION['admin_ops_mermas'] ?? null;
    if (!is_array($lista)) {
        $lista = mass_ops_mermas_seed();
        $_SESSION['admin_ops_mermas'] = $lista;
    }
    return $lista;
}

/**
 * @param array<int, array<string, mixed>> $lista
 */
function mass_ops_mermas_guardar(array $lista): void
{
    $_SESSION['admin_ops_mermas'] = array_values($lista);
}

function mass_ops_merma_clase(array $merma): string
{
    if ((string) ($merma['tipo'] ?? '') !== 'caducidad') {
        return 'merma';
    }
    $vence = (string) ($merma['vence'] ?? '');
    if ($vence !== '' && $vence < mass_ops_hoy()) {
        return 'vencida';
    }
    $limite = (new DateTimeImmutable(mass_ops_hoy()))
        ->modify('+' . (int) mass_ops_config()['diasCaducidad'] . ' days')
        ->format('Y-m-d');
    if ($vence !== '' && $vence <= $limite) {
        return 'por_vencer';
    }
    return 'vigente';
}

function mass_ops_merma_clase_label(string $clase): string
{
    return match ($clase) {
        'vencida' => 'Vencida',
        'por_vencer' => 'Por vencer',
        'vigente' => 'Vigente',
        default => 'Merma',
    };
}

/**
 * @param array<string, mixed> $data
 * @return array{ok: bool, errores: string[], id?: string}
 */
function mass_ops_merma_registrar(array $data): array
{
    $productoId = mass_ops_entero($data['producto_id'] ?? null, 1, 999999);
    $cantidad = mass_ops_entero($data['cantidad'] ?? null, 1, 999);
    $tipo = (string) ($data['tipo'] ?? '');
    $motivo = trim((string) ($data['motivo'] ?? ''));
    $descontar = (string) ($data['descontar'] ?? '') === '1';
    $errores = [];
    $row = $productoId === null ? null : mass_ops_inventario_por_id($productoId);
    if ($row === null) {
        $errores[] = 'Selecciona un producto del inventario.';
    }
    if ($cantidad === null) {
        $errores[] = 'La cantidad debe ser un entero entre 1 y 999.';
    }
    if (!in_array($tipo, ['merma', 'caducidad'], true)) {
        $errores[] = 'Indica si el registro es merma o caducidad.';
    }
    $motivos = $tipo === 'caducidad'
        ? ['Vencido', 'Próximo a vencer']
        : ['Daño en góndola', 'Desmedro', 'Rotura'];
    if (!in_array($motivo, $motivos, true)) {
        $errores[] = 'Selecciona un motivo válido para el tipo elegido.';
    }
    $vence = '';
    if ($tipo === 'caducidad') {
        $vence = mass_ops_fecha($data['vence'] ?? null) ?? '';
        if ($vence === '') {
            $errores[] = 'La caducidad necesita una fecha válida.';
        }
    }
    if ($errores !== []) {
        return ['ok' => false, 'errores' => $errores];
    }
    if ($descontar) {
        $descuento = mass_ops_descontar_gondola((int) $row['id'], (int) $cantidad);
        if (!$descuento['ok']) {
            return $descuento;
        }
    }
    $lista = mass_ops_mermas();
    $nuevoId = 'MR-' . str_pad((string) (2200 + count($lista) + 1), 4, '0', STR_PAD_LEFT);
    array_unshift($lista, [
        'id' => $nuevoId,
        'productoId' => (int) $row['id'],
        'sku' => (string) $row['sku'],
        'nombre' => (string) $row['nombre'],
        'img' => (string) ($row['img'] ?? ''),
        'tipo' => $tipo,
        'motivo' => $motivo,
        'cantidad' => (int) $cantidad,
        'vence' => $vence,
        'estado' => 'abierta',
        'descontada' => $descontar,
        'registrada' => mass_ops_hoy(),
    ]);
    mass_ops_mermas_guardar($lista);
    return ['ok' => true, 'errores' => [], 'id' => $nuevoId];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_merma_retirar(string $id): array
{
    $lista = mass_ops_mermas();
    foreach ($lista as $i => $merma) {
        if ((string) ($merma['id'] ?? '') !== $id) {
            continue;
        }
        if ((string) ($merma['estado'] ?? '') !== 'abierta') {
            return ['ok' => false, 'errores' => ['Ese registro ya fue retirado de góndola.']];
        }
        if (empty($merma['descontada'])) {
            $descuento = mass_ops_descontar_gondola((int) $merma['productoId'], (int) $merma['cantidad']);
            if (!$descuento['ok']) {
                return $descuento;
            }
            $merma['descontada'] = true;
        }
        $merma['estado'] = 'retirada';
        $lista[$i] = $merma;
        mass_ops_mermas_guardar($lista);
        return ['ok' => true, 'errores' => []];
    }
    return ['ok' => false, 'errores' => ['Registro de merma no encontrado.']];
}

/**
 * @return array<string, mixed>
 */
function mass_ops_reporte(string $periodo): array
{
    $periodo = in_array($periodo, ['hoy', '7d', '30d'], true) ? $periodo : 'hoy';
    $factor = ['hoy' => 1.0, '7d' => 6.8, '30d' => 26.0][$periodo];
    $kpis = mass_admin_kpis();
    $horaria = [];
    foreach (mass_admin_venta_horaria() as $punto) {
        $horaria[] = [
            'hora' => $punto['hora'],
            'venta' => round((float) $punto['venta'] * ($periodo === 'hoy' ? 1 : $factor / 8), 2),
            'objetivo' => round((float) $punto['objetivo'] * ($periodo === 'hoy' ? 1 : $factor / 8), 2),
        ];
    }
    $mermaUnidades = 0;
    $mermaMonto = 0.0;
    $precios = [];
    foreach (mass_productos() as $producto) {
        $precios[(int) $producto['id']] = (float) $producto['precio'];
    }
    foreach (mass_ops_mermas() as $merma) {
        $mermaUnidades += (int) $merma['cantidad'];
        $mermaMonto += (int) $merma['cantidad'] * (float) ($precios[(int) $merma['productoId']] ?? 0);
    }
    $conteosAbiertos = 0;
    foreach (mass_ops_conteos() as $conteo) {
        if ((string) $conteo['estado'] !== 'cerrado') {
            $conteosAbiertos++;
        }
    }
    $fueraSla = 0;
    $activos = 0;
    foreach (mass_ops_pedidos() as $pedido) {
        if (in_array($pedido['estado'], ['preparacion', 'por_recoger'], true)) {
            $activos++;
        }
        if (!empty($pedido['fueraSla'])) {
            $fueraSla++;
        }
    }
    $stock = ['ok' => 0, 'bajo' => 0, 'critico' => 0];
    foreach (mass_ops_inventario() as $row) {
        $estado = (string) $row['estado'];
        if (isset($stock[$estado])) {
            $stock[$estado]++;
        }
    }

    return [
        'periodo' => $periodo,
        'etiqueta' => ['hoy' => 'Hoy', '7d' => 'Últimos 7 días', '30d' => 'Últimos 30 días'][$periodo],
        'venta' => round((float) $kpis['ventaDia'] * $factor, 2),
        'ticket' => (float) $kpis['ticketPromedio'],
        'disponibilidad' => (float) $kpis['disponibilidad'],
        'horaria' => $horaria,
        'canales' => mass_admin_canales(),
        'top' => mass_admin_top_rotacion(),
        'mermaUnidades' => $mermaUnidades,
        'mermaMonto' => round($mermaMonto, 2),
        'conteosAbiertos' => $conteosAbiertos,
        'pedidosActivos' => $activos,
        'fueraSla' => $fueraSla,
        'stock' => $stock,
    ];
}

function mass_ops_reporte_csv(string $periodo): string
{
    $reporte = mass_ops_reporte($periodo);
    $lineas = [
        ['indicador', 'valor'],
        ['periodo', (string) $reporte['etiqueta']],
        ['ventas', number_format((float) $reporte['venta'], 2, '.', '')],
        ['ticket_promedio', number_format((float) $reporte['ticket'], 2, '.', '')],
        ['disponibilidad_pct', number_format((float) $reporte['disponibilidad'], 1, '.', '')],
        ['merma_unidades', (string) $reporte['mermaUnidades']],
        ['merma_monto', number_format((float) $reporte['mermaMonto'], 2, '.', '')],
        ['conteos_abiertos', (string) $reporte['conteosAbiertos']],
        ['pedidos_activos', (string) $reporte['pedidosActivos']],
        ['pedidos_fuera_sla', (string) $reporte['fueraSla']],
        ['skus_ok', (string) $reporte['stock']['ok']],
        ['skus_bajo', (string) $reporte['stock']['bajo']],
        ['skus_critico', (string) $reporte['stock']['critico']],
    ];
    $fh = fopen('php://temp', 'r+');
    if ($fh === false) {
        return '';
    }
    foreach ($lineas as $linea) {
        fputcsv($fh, $linea);
    }
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);
    return $csv === false ? '' : $csv;
}

/**
 * @param array<string, scalar|null> $query
 */
function mass_ops_volver(string $script, array $query = []): never
{
    $clean = [];
    foreach ($query as $key => $value) {
        if ($value === null || $value === '' || $value === 'todos') {
            continue;
        }
        $clean[$key] = $value;
    }
    $qs = http_build_query($clean);
    redirect($script . ($qs !== '' ? '?' . $qs : ''));
}
