<?php
declare(strict_types=1);

/**
 * Capacidades avanzadas del segmento WMS: consulta paginada, exportaciones,
 * lote, transferencia, conteos nuevos y mantenimiento de mermas.
 * Lo carga includes/admin_operaciones.php.
 */

/**
 * @param array<int, mixed> $rows
 * @return array{items: array<int, mixed>, page: int, pages: int, total: int, from: int, to: int}
 */
function mass_ops_paginar(array $rows, int $page, int $porPagina = 8): array
{
    $porPagina = max(1, min(50, $porPagina));
    $total = count($rows);
    $pages = max(1, (int) ceil($total / $porPagina));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $porPagina;
    return [
        'items' => array_slice($rows, $offset, $porPagina),
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
        'from' => $total === 0 ? 0 : $offset + 1,
        'to' => min($total, $offset + $porPagina),
    ];
}

/**
 * @param array<string, scalar|null> $query
 */
function mass_ops_query(array $query): string
{
    $clean = [];
    foreach ($query as $key => $value) {
        if ($value === null || $value === '' || $value === 'todos' || $value === 'todas') {
            continue;
        }
        if ($key === 'pagina' && (int) $value <= 1) {
            continue;
        }
        $clean[$key] = $value;
    }
    return http_build_query($clean);
}

/**
 * @param array<int, string> $headers
 * @param array<int, array<int, scalar|null>> $rows
 */
function mass_ops_csv(array $headers, array $rows): string
{
    $fh = fopen('php://temp', 'r+');
    if ($fh === false) {
        return '';
    }
    fputcsv($fh, $headers);
    foreach ($rows as $row) {
        fputcsv($fh, $row);
    }
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);
    return "\xEF\xBB\xBF" . ($csv === false ? '' : $csv);
}

/**
 * @param array<int, string> $headers
 * @param array<int, array<int, scalar|null>> $rows
 */
function mass_ops_xls(string $titulo, array $headers, array $rows): string
{
    $html = '<html><head><meta charset="UTF-8"><title>' . e($titulo) . '</title></head><body>';
    $html .= '<table border="1"><thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . e($header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . e((string) $cell) . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table></body></html>';
}

/**
 * @param array<int, string> $headers
 * @param array<int, array<int, scalar|null>> $rows
 */
function mass_ops_documento_html(string $titulo, string $subtitulo, array $headers, array $rows): string
{
    $tabla = '<table><thead><tr>';
    foreach ($headers as $header) {
        $tabla .= '<th>' . e($header) . '</th>';
    }
    $tabla .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $tabla .= '<tr>';
        foreach ($row as $cell) {
            $tabla .= '<td>' . e((string) $cell) . '</td>';
        }
        $tabla .= '</tr>';
    }
    $tabla .= '</tbody></table>';
    return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . e($titulo) . '</title>'
        . '<style>body{font-family:Georgia,serif;color:#143028;margin:2rem}table{border-collapse:collapse;width:100%}th,td{border:1px solid #c9ddd2;padding:.4rem .55rem;text-align:left}th{background:#e7f5ee}button{margin:.5rem 0 1rem}</style>'
        . '</head><body><button type="button" onclick="window.print()">Guardar PDF</button><h1>'
        . e($titulo) . '</h1><p>' . e($subtitulo) . '</p>' . $tabla . '</body></html>';
}

function mass_ops_descargar(string $filename, string $contentType, string $body): never
{
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '', $filename) ?: 'reporte.csv';
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    echo $body;
    exit;
}

/**
 * @param array<int, array<string, mixed>> $filas
 * @return array{ok: int, bajo: int, critico: int, unidades: int, valor: float}
 */
function mass_ops_inventario_resumen(array $filas): array
{
    $precios = [];
    foreach (mass_productos() as $producto) {
        $precios[(int) $producto['id']] = (float) $producto['precio'];
    }
    $resumen = ['ok' => 0, 'bajo' => 0, 'critico' => 0, 'unidades' => 0, 'valor' => 0.0];
    foreach ($filas as $fila) {
        $estado = (string) ($fila['estado'] ?? 'ok');
        if (isset($resumen[$estado])) {
            $resumen[$estado]++;
        }
        $uds = (int) $fila['gondola'] + (int) $fila['trastienda'];
        $resumen['unidades'] += $uds;
        $resumen['valor'] += $uds * (float) ($precios[(int) $fila['id']] ?? 0);
    }
    $resumen['valor'] = round($resumen['valor'], 2);
    return $resumen;
}

/**
 * @param array<string, mixed> $query
 * @return array<string, mixed>
 */
function mass_ops_inventario_vista(array $query): array
{
    $q = mb_strtolower(trim((string) ($query['q'] ?? '')));
    $estado = (string) ($query['estado'] ?? 'todos');
    if (!in_array($estado, ['todos', 'ok', 'bajo', 'critico'], true)) {
        $estado = 'todos';
    }
    $marca = trim((string) ($query['marca'] ?? ''));
    $orden = (string) ($query['orden'] ?? 'nombre');
    if (!in_array($orden, ['nombre', 'gondola', 'critico'], true)) {
        $orden = 'nombre';
    }
    $todos = mass_ops_inventario();
    $marcas = [];
    foreach ($todos as $fila) {
        $marcas[(string) $fila['marca']] = true;
    }
    $marcas = array_keys($marcas);
    sort($marcas);
    $filas = array_values(array_filter(
        $todos,
        static function (array $fila) use ($q, $estado, $marca): bool {
            if ($estado !== 'todos' && (string) $fila['estado'] !== $estado) {
                return false;
            }
            if ($marca !== '' && $marca !== 'todas' && (string) $fila['marca'] !== $marca) {
                return false;
            }
            if ($q === '') {
                return true;
            }
            $hay = mb_strtolower((string) $fila['nombre'] . ' ' . (string) $fila['sku'] . ' ' . (string) $fila['marca']);
            return str_contains($hay, $q);
        }
    ));
    usort($filas, static function (array $a, array $b) use ($orden): int {
        if ($orden === 'gondola') {
            return (int) $b['gondola'] <=> (int) $a['gondola'];
        }
        if ($orden === 'critico') {
            $rank = ['critico' => 0, 'bajo' => 1, 'ok' => 2];
            return ($rank[(string) $a['estado']] ?? 9) <=> ($rank[(string) $b['estado']] ?? 9);
        }
        return strcasecmp((string) $a['nombre'], (string) $b['nombre']);
    });
    $pager = mass_ops_paginar($filas, (int) ($query['pagina'] ?? 1), 8);
    return [
        'filas' => $pager['items'],
        'exportar' => $filas,
        'pager' => $pager,
        'marcas' => $marcas,
        'q' => trim((string) ($query['q'] ?? '')),
        'estado' => $estado,
        'marca' => $marca === '' ? 'todas' : $marca,
        'orden' => $orden,
        'resumen' => mass_ops_inventario_resumen($todos),
    ];
}

/**
 * @param array<int, array<string, mixed>> $filas
 * @return array<int, array<int, scalar>>
 */
function mass_ops_inventario_filas_planas(array $filas): array
{
    $out = [];
    foreach ($filas as $fila) {
        $out[] = [
            (string) $fila['sku'],
            (string) $fila['nombre'],
            (string) $fila['marca'],
            (int) $fila['gondola'],
            (int) $fila['trastienda'],
            (int) $fila['gondola'] + (int) $fila['trastienda'],
            mass_ops_estado_stock_label((string) $fila['estado']),
        ];
    }
    return $out;
}

/**
 * @param array<int, mixed> $ids
 * @return array{ok: bool, errores: string[], movidas?: int, aplicados?: int}
 */
function mass_ops_reponer_lote(array $ids): array
{
    $limpios = [];
    foreach ($ids as $id) {
        $n = (int) $id;
        if ($n > 0) {
            $limpios[$n] = $n;
        }
    }
    $limpios = array_values($limpios);
    if ($limpios === [] || count($limpios) > 30) {
        return ['ok' => false, 'errores' => ['Selecciona entre 1 y 30 SKU para reponer.']];
    }
    $aplicados = 0;
    $movidas = 0;
    $errores = [];
    foreach ($limpios as $id) {
        $resultado = mass_ops_reponer($id);
        if ($resultado['ok']) {
            $aplicados++;
            $movidas += (int) ($resultado['movidas'] ?? 0);
            continue;
        }
        $errores[] = mass_ops_sku($id) . ': ' . (string) ($resultado['errores'][0] ?? 'no se pudo reponer');
    }
    if ($aplicados === 0) {
        return ['ok' => false, 'errores' => $errores !== [] ? $errores : ['Nada que reponer.']];
    }
    return ['ok' => true, 'errores' => $errores, 'movidas' => $movidas, 'aplicados' => $aplicados];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_transferir(int $id, string $destino, mixed $cantidadRaw): array
{
    if (!in_array($destino, ['gondola', 'trastienda'], true)) {
        return ['ok' => false, 'errores' => ['El destino de la transferencia no es válido.']];
    }
    $cantidad = mass_ops_entero($cantidadRaw, 1, 9999);
    if ($cantidad === null) {
        return ['ok' => false, 'errores' => ['La cantidad a transferir debe ser un entero entre 1 y 9999.']];
    }
    $row = mass_ops_inventario_por_id($id);
    if ($row === null) {
        return ['ok' => false, 'errores' => ['El SKU no está en el control de góndola.']];
    }
    $gondola = (int) $row['gondola'];
    $trastienda = (int) $row['trastienda'];
    if ($destino === 'gondola') {
        if ($trastienda < $cantidad) {
            return ['ok' => false, 'errores' => ['La trastienda no alcanza para esa transferencia.']];
        }
        $gondola += $cantidad;
        $trastienda -= $cantidad;
        $detalle = $cantidad . ' uds hacia góndola';
    } else {
        if ($gondola < $cantidad) {
            return ['ok' => false, 'errores' => ['La góndola no alcanza para regresar esa cantidad.']];
        }
        $gondola -= $cantidad;
        $trastienda += $cantidad;
        $detalle = $cantidad . ' uds de regreso a trastienda';
    }
    mass_ops_stock_guardar($id, $gondola, $trastienda);
    mass_ops_movimiento('transferencia', (string) $row['sku'], $detalle);
    return ['ok' => true, 'errores' => []];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_pedido_actualizar(string $codigo, string $prioridad, string $nota): array
{
    $pedido = mass_ops_pedido_por_codigo($codigo);
    if ($pedido === null) {
        return ['ok' => false, 'errores' => ['Pedido no encontrado.']];
    }
    if (!in_array($prioridad, ['alta', 'media', 'baja'], true)) {
        return ['ok' => false, 'errores' => ['La prioridad debe ser alta, media o baja.']];
    }
    $nota = trim($nota);
    if (mb_strlen($nota) > 180) {
        return ['ok' => false, 'errores' => ['La nota admite hasta 180 caracteres.']];
    }
    mass_ops_pedido_meta_guardar((string) $pedido['codigo'], [
        'estado' => (string) $pedido['estado'],
        'prioridad' => $prioridad,
        'nota' => $nota,
    ]);
    return ['ok' => true, 'errores' => []];
}

/**
 * @param array<string, mixed> $query
 * @return array<string, mixed>
 */
function mass_ops_pedidos_vista(array $query): array
{
    $modo = (string) ($query['modo'] ?? 'todos');
    if (!in_array($modo, ['todos', 'express', 'retiro'], true)) {
        $modo = 'todos';
    }
    $estado = (string) ($query['estado'] ?? 'todos');
    if (!in_array($estado, ['todos', 'preparacion', 'por_recoger', 'entregado', 'retirado', 'activos'], true)) {
        $estado = 'todos';
    }
    $q = mb_strtolower(trim((string) ($query['q'] ?? '')));
    $filas = array_values(array_filter(
        mass_ops_pedidos(),
        static function (array $pedido) use ($modo, $estado, $q): bool {
            if ($modo !== 'todos' && (string) ($pedido['modo'] ?? '') !== $modo) {
                return false;
            }
            if ($estado === 'activos' && !in_array((string) $pedido['estado'], ['preparacion', 'por_recoger'], true)) {
                return false;
            }
            if (!in_array($estado, ['todos', 'activos'], true) && (string) $pedido['estado'] !== $estado) {
                return false;
            }
            if ($q === '') {
                return true;
            }
            $hay = mb_strtolower((string) $pedido['codigo'] . ' ' . (string) $pedido['cliente'] . ' ' . (string) ($pedido['nota'] ?? ''));
            return str_contains($hay, $q);
        }
    ));
    $pager = mass_ops_paginar($filas, (int) ($query['pagina'] ?? 1), 6);
    $embudo = ['preparacion' => 0, 'por_recoger' => 0, 'cerrados' => 0, 'fuera' => 0];
    foreach (mass_ops_pedidos() as $pedido) {
        if ((string) $pedido['estado'] === 'preparacion') {
            $embudo['preparacion']++;
        } elseif ((string) $pedido['estado'] === 'por_recoger') {
            $embudo['por_recoger']++;
        } else {
            $embudo['cerrados']++;
        }
        if (!empty($pedido['fueraSla'])) {
            $embudo['fuera']++;
        }
    }
    return [
        'filas' => $pager['items'],
        'exportar' => $filas,
        'pager' => $pager,
        'tablero' => mass_ops_pedidos(),
        'modo' => $modo,
        'estado' => $estado,
        'q' => trim((string) ($query['q'] ?? '')),
        'embudo' => $embudo,
    ];
}

/**
 * @param array<int, mixed> $ids
 * @return array{ok: bool, errores: string[], id?: string}
 */
function mass_ops_conteo_crear(string $pasillo, string $tipo, array $ids): array
{
    $pasillo = trim($pasillo);
    if (mb_strlen($pasillo) < 4 || mb_strlen($pasillo) > 60) {
        return ['ok' => false, 'errores' => ['El pasillo debe tener entre 4 y 60 caracteres.']];
    }
    if (!in_array($tipo, ['Cíclico', 'Auditoría'], true)) {
        return ['ok' => false, 'errores' => ['El tipo debe ser cíclico o auditoría.']];
    }
    $inventario = [];
    foreach (mass_ops_inventario() as $fila) {
        $inventario[(int) $fila['id']] = $fila;
    }
    $lineas = [];
    foreach ($ids as $id) {
        $n = (int) $id;
        if (isset($inventario[$n])) {
            $lineas[$n] = mass_ops_linea_conteo($inventario[$n]);
        }
    }
    $lineas = array_values($lineas);
    if (count($lineas) < 1 || count($lineas) > 8) {
        return ['ok' => false, 'errores' => ['Selecciona entre 1 y 8 SKU para el conteo.']];
    }
    $lista = mass_ops_conteos();
    $nuevo = 'CC-' . str_pad((string) (2500 + count($lista) + 1), 4, '0', STR_PAD_LEFT);
    array_unshift($lista, [
        'id' => $nuevo,
        'pasillo' => $pasillo,
        'tipo' => $tipo,
        'estado' => 'pendiente',
        'responsable' => 'Sin asignar',
        'programado' => mass_ops_hoy() . ' ' . date('H:i'),
        'cerradoEn' => null,
        'resultado' => null,
        'lineas' => $lineas,
    ]);
    mass_ops_conteos_guardar($lista);
    mass_ops_movimiento('conteo', $nuevo, 'Programado en ' . $pasillo);
    return ['ok' => true, 'errores' => [], 'id' => $nuevo];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_conteo_cancelar(string $id): array
{
    $lista = mass_ops_conteos();
    foreach ($lista as $i => $conteo) {
        if ((string) $conteo['id'] !== $id) {
            continue;
        }
        if (!in_array((string) $conteo['estado'], ['pendiente', 'en_curso'], true)) {
            return ['ok' => false, 'errores' => ['Solo se cancela un conteo pendiente o en curso.']];
        }
        $conteo['estado'] = 'cancelado';
        $lista[$i] = $conteo;
        mass_ops_conteos_guardar($lista);
        mass_ops_movimiento('conteo', $id, 'Conteo cancelado');
        return ['ok' => true, 'errores' => []];
    }
    return ['ok' => false, 'errores' => ['Conteo no encontrado.']];
}

/**
 * @param array<string, mixed> $data
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_merma_editar(string $id, array $data): array
{
    $lista = mass_ops_mermas();
    foreach ($lista as $i => $merma) {
        if ((string) ($merma['id'] ?? '') !== $id) {
            continue;
        }
        if ((string) ($merma['estado'] ?? '') !== 'abierta') {
            return ['ok' => false, 'errores' => ['Solo se edita un registro abierto.']];
        }
        $cantidad = mass_ops_entero($data['cantidad'] ?? null, 1, 999);
        $tipo = (string) ($merma['tipo'] ?? '');
        $motivo = trim((string) ($data['motivo'] ?? ''));
        $motivos = $tipo === 'caducidad'
            ? ['Vencido', 'Próximo a vencer']
            : ['Daño en góndola', 'Desmedro', 'Rotura'];
        $errores = [];
        if ($cantidad === null) {
            $errores[] = 'La cantidad debe ser un entero entre 1 y 999.';
        }
        if (!in_array($motivo, $motivos, true)) {
            $errores[] = 'Selecciona un motivo válido para este registro.';
        }
        $vence = (string) ($merma['vence'] ?? '');
        if ($tipo === 'caducidad') {
            $vence = mass_ops_fecha($data['vence'] ?? null) ?? '';
            if ($vence === '') {
                $errores[] = 'La caducidad necesita una fecha válida.';
            }
        }
        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores];
        }
        $anterior = (int) $merma['cantidad'];
        if (!empty($merma['descontada']) && $cantidad !== $anterior) {
            $delta = $cantidad - $anterior;
            if ($delta > 0) {
                $descuento = mass_ops_descontar_gondola((int) $merma['productoId'], $delta);
                if (!$descuento['ok']) {
                    return $descuento;
                }
            } else {
                $row = mass_ops_inventario_por_id((int) $merma['productoId']);
                if ($row === null) {
                    return ['ok' => false, 'errores' => ['El SKU ya no está en inventario.']];
                }
                mass_ops_stock_guardar((int) $row['id'], (int) $row['gondola'] + abs($delta), (int) $row['trastienda']);
            }
        }
        $merma['cantidad'] = $cantidad;
        $merma['motivo'] = $motivo;
        $merma['vence'] = $vence;
        $lista[$i] = $merma;
        mass_ops_mermas_guardar($lista);
        mass_ops_movimiento('merma', $id, 'Registro actualizado');
        return ['ok' => true, 'errores' => []];
    }
    return ['ok' => false, 'errores' => ['Registro de merma no encontrado.']];
}

/**
 * @return array{ok: bool, errores: string[]}
 */
function mass_ops_merma_anular(string $id): array
{
    $lista = mass_ops_mermas();
    foreach ($lista as $i => $merma) {
        if ((string) ($merma['id'] ?? '') !== $id) {
            continue;
        }
        if ((string) ($merma['estado'] ?? '') === 'anulada') {
            return ['ok' => false, 'errores' => ['Ese registro ya está anulado.']];
        }
        if (!empty($merma['descontada'])) {
            $row = mass_ops_inventario_por_id((int) $merma['productoId']);
            if ($row === null) {
                return ['ok' => false, 'errores' => ['No se puede devolver el stock: el SKU no está en góndola.']];
            }
            mass_ops_stock_guardar((int) $row['id'], (int) $row['gondola'] + (int) $merma['cantidad'], (int) $row['trastienda']);
            $merma['descontada'] = false;
        }
        $merma['estado'] = 'anulada';
        $lista[$i] = $merma;
        mass_ops_mermas_guardar($lista);
        mass_ops_movimiento('merma', $id, 'Registro anulado');
        return ['ok' => true, 'errores' => []];
    }
    return ['ok' => false, 'errores' => ['Registro de merma no encontrado.']];
}
