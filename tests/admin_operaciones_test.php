<?php
declare(strict_types=1);

/**
 * Reglas del segmento operativo WMS (inventario, picking, conteos, mermas, reportes).
 *   php tests/admin_operaciones_test.php
 */

$sessionDir = dirname(__DIR__) . '/.phpunit.cache/sessions';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0777, true);
}
ini_set('session.save_path', $sessionDir);
session_start();
$_SESSION = [];

require_once dirname(__DIR__) . '/includes/data.php';
require_once dirname(__DIR__) . '/includes/admin_operaciones.php';

$passed = 0;
$failed = 0;

function assert_true(bool $cond, string $msg): void
{
    global $passed, $failed;
    if ($cond) {
        echo "OK  {$msg}\n";
        $passed++;
        return;
    }
    echo "FAIL  {$msg}\n";
    $failed++;
}

$aceite = mass_ops_inventario_por_id(1);
$arroz = mass_ops_inventario_por_id(2);
$cafe = mass_ops_inventario_por_id(11);
$platano = mass_ops_inventario_por_id(8);
assert_true($aceite !== null && (int) $aceite['gondola'] === 18 && $aceite['estado'] === 'ok', 'aceite inicia en stock OK');
assert_true($arroz !== null && (int) $arroz['gondola'] === 6 && $arroz['estado'] === 'bajo', 'arroz inicia bajo');
assert_true($cafe !== null && (int) $cafe['gondola'] === 0 && $cafe['estado'] === 'critico', 'café inicia crítico');
assert_true($platano !== null && (int) $platano['trastienda'] === 0 && $platano['estado'] === 'critico', 'plátano sin trastienda queda crítico');

$repo = mass_ops_reponer(2);
$arroz = mass_ops_inventario_por_id(2);
assert_true($repo['ok'] && ($repo['movidas'] ?? 0) === 6, 'reponer arroz mueve 6 unidades');
assert_true($arroz !== null && (int) $arroz['gondola'] === 12 && (int) $arroz['trastienda'] === 6 && $arroz['estado'] === 'ok', 'arroz queda en stock OK');
assert_true(!mass_ops_reponer(8)['ok'], 'reponer sin trastienda se rechaza');
assert_true(mass_ops_ajustar(1, '10', '4')['ok'], 'ajuste de aceite válido');
assert_true(!mass_ops_ajustar(1, '-1', '4')['ok'], 'ajuste negativo se rechaza');
$aceite = mass_ops_inventario_por_id(1);
assert_true($aceite !== null && (int) $aceite['gondola'] === 10 && (int) $aceite['trastienda'] === 4, 'ajuste persiste en sesión');

assert_true(mass_ops_picking_activos() === 5, 'hay 5 pedidos en recolección');
$avance = mass_ops_pedido_avanzar('MASS-04573');
assert_true($avance['ok'] && ($avance['estado'] ?? '') === 'por_recoger', 'express pasa a por recoger');
$avance = mass_ops_pedido_avanzar('MASS-04573');
assert_true($avance['ok'] && ($avance['estado'] ?? '') === 'entregado', 'express se marca entregado');
assert_true(!mass_ops_pedido_avanzar('MASS-04573')['ok'], 'pedido cerrado no avanza');
$_SESSION['admin_ops_pedidos']['MASS-04576'] = 'preparacion';
assert_true((mass_ops_pedido_avanzar('MASS-04576')['estado'] ?? '') === 'por_recoger', 'retiro en preparación pasa a por recoger');
assert_true((mass_ops_pedido_avanzar('MASS-04576')['estado'] ?? '') === 'retirado', 'retiro se marca retirado');
assert_true((mass_ops_pedido_avanzar('MASS-04574')['estado'] ?? '') === 'retirado', 'retiro que ya estaba listo se cierra');
assert_true(mass_ops_picking_activos() === 2, 'la cola de picking baja al cerrar los pedidos');
$detalle = mass_ops_pedido_por_codigo('MASS-04575');
assert_true($detalle !== null && count(mass_ops_pedido_ruta($detalle)) === (int) $detalle['items'], 'la ruta de recolección tiene un paso por ítem');

$invalida = mass_ops_config_guardar([
    'umbralBajo' => '8',
    'umbralCriticoPiso' => '8',
    'diasCaducidad' => '7',
    'slaPickingMin' => '15',
    'alertasActivas' => '1',
]);
assert_true(!$invalida['ok'], 'umbral crítico igual al bajo se rechaza');
assert_true(mass_ops_config_guardar([
    'umbralBajo' => '20',
    'umbralCriticoPiso' => '2',
    'diasCaducidad' => '3',
    'slaPickingMin' => '10',
    'alertasActivas' => '1',
])['ok'], 'configuración válida se guarda');
$arroz = mass_ops_inventario_por_id(2);
assert_true($arroz !== null && $arroz['estado'] === 'bajo', 'el nuevo umbral reclasifica la góndola');
$fuera = 0;
foreach (mass_ops_pedidos() as $pedido) {
    if (!empty($pedido['fueraSla'])) {
        $fuera++;
    }
}
assert_true($fuera >= 1, 'el SLA más estricto marca pedidos fuera de tiempo');

assert_true(mass_ops_config_guardar([
    'umbralBajo' => '8',
    'umbralCriticoPiso' => '0',
    'diasCaducidad' => '7',
    'slaPickingMin' => '15',
    'alertasActivas' => '1',
])['ok'], 'se restauran los umbrales de tienda');

$conteo = mass_ops_conteo_por_id('CC-2410');
assert_true($conteo !== null && $conteo['estado'] === 'pendiente', 'el conteo de abarrotes nace pendiente');
assert_true(mass_ops_conteo_iniciar('CC-2410')['ok'], 'se inicia el conteo');
$conteo = mass_ops_conteo_por_id('CC-2410');
$contados = [];
foreach ($conteo['lineas'] as $linea) {
    $contados[(string) $linea['sku']] = (string) $linea['sistema'];
}
assert_true(mass_ops_conteo_registrar('CC-2410', $contados)['ok'], 'se registra el conteo físico');
$cierre = mass_ops_conteo_cerrar('CC-2410', true);
assert_true($cierre['ok'] && ($cierre['resultado'] ?? '') === 'cuadrado', 'auditoría cuadrada se cierra');

$frescos = mass_ops_conteo_por_id('CC-2411');
$contados = [];
$skuAjuste = '';
$sistemaAjuste = 0;
$idAjuste = 0;
foreach ($frescos['lineas'] as $i => $linea) {
    $contados[(string) $linea['sku']] = (string) $linea['sistema'];
    if ($i === 0) {
        $skuAjuste = (string) $linea['sku'];
        $sistemaAjuste = (int) $linea['sistema'];
        $idAjuste = (int) $linea['id'];
        $contados[$skuAjuste] = (string) ($sistemaAjuste + 2);
    }
}
assert_true(mass_ops_conteo_registrar('CC-2411', $contados)['ok'], 'se registra diferencia en frescos');
$cierre = mass_ops_conteo_cerrar('CC-2411', true);
$ajustado = mass_ops_inventario_por_id($idAjuste);
assert_true($cierre['ok'] && ($cierre['resultado'] ?? '') === 'diferencia', 'auditoría con diferencia se cierra');
assert_true($ajustado !== null && (int) $ajustado['gondola'] === $sistemaAjuste + 2, 'el cierre aplica el conteo a la góndola');

$antes = mass_ops_inventario_por_id(1);
$registro = mass_ops_merma_registrar([
    'producto_id' => '1',
    'cantidad' => '1',
    'tipo' => 'merma',
    'motivo' => 'Desmedro',
    'descontar' => '1',
]);
$despues = mass_ops_inventario_por_id(1);
assert_true($registro['ok'], 'se registra una merma');
assert_true($antes !== null && $despues !== null && (int) $despues['gondola'] === (int) $antes['gondola'] - 1, 'la merma descuenta la góndola');
assert_true(!mass_ops_merma_registrar([
    'producto_id' => '1',
    'cantidad' => '1',
    'tipo' => 'caducidad',
    'motivo' => 'Desmedro',
    'vence' => '2024-10-30',
    'descontar' => '',
])['ok'], 'motivo de merma no vale para caducidad');
$platano = mass_ops_inventario_por_id(8);
$retiro = mass_ops_merma_retirar('MR-2203');
$platanoLuego = mass_ops_inventario_por_id(8);
assert_true($retiro['ok'], 'se retira la merma abierta de plátano');
assert_true($platano !== null && $platanoLuego !== null && (int) $platanoLuego['gondola'] === (int) $platano['gondola'] - 1, 'retirar descuenta el piso');

$csv = mass_ops_reporte_csv('7d');
assert_true(str_contains($csv, 'ventas') && str_contains($csv, 'Últimos 7 días'), 'el CSV del reporte incluye periodo y ventas');
assert_true(mass_ops_reporte('no-existe')['periodo'] === 'hoy', 'un periodo desconocido cae en hoy');

$pagina = mass_ops_paginar(range(1, 20), 2, 8);
assert_true($pagina['total'] === 20 && $pagina['page'] === 2 && count($pagina['items']) === 8, 'la paginación corta la segunda página');
$lote = mass_ops_reponer_lote([11]);
assert_true($lote['ok'] && ($lote['movidas'] ?? 0) === 4, 'el lote repone el café con el stock de trastienda');
$antesTransfer = mass_ops_inventario_por_id(3);
$transfer = mass_ops_transferir(3, 'trastienda', '2');
$despuesTransfer = mass_ops_inventario_por_id(3);
assert_true($transfer['ok'] && $antesTransfer !== null && $despuesTransfer !== null && (int) $despuesTransfer['gondola'] === (int) $antesTransfer['gondola'] - 2, 'la transferencia devuelve unidades a trastienda');
assert_true(mass_ops_pedido_actualizar('MASS-04575', 'alta', 'Dejar en cámara')['ok'], 'se guarda prioridad y nota');
$anotado = mass_ops_pedido_por_codigo('MASS-04575');
assert_true($anotado !== null && $anotado['prioridad'] === 'alta' && $anotado['nota'] === 'Dejar en cámara', 'la nota queda en el pedido');
$nuevoConteo = mass_ops_conteo_crear('Pasillo 09 · Snacks', 'Cíclico', [1, 2]);
assert_true($nuevoConteo['ok'], 'se programa un conteo nuevo');
assert_true(mass_ops_conteo_cancelar((string) $nuevoConteo['id'])['ok'], 'se cancela el conteo nuevo');
assert_true((mass_ops_conteo_por_id((string) $nuevoConteo['id'])['estado'] ?? '') === 'cancelado', 'el conteo queda cancelado');
$baseMerma = mass_ops_inventario_por_id(3);
$altaMerma = mass_ops_merma_registrar([
    'producto_id' => '3',
    'cantidad' => '2',
    'tipo' => 'merma',
    'motivo' => 'Rotura',
    'descontar' => '1',
]);
assert_true(mass_ops_merma_anular((string) ($altaMerma['id'] ?? ''))['ok'], 'se anula la merma');
$devuelta = mass_ops_inventario_por_id(3);
assert_true($baseMerma !== null && $devuelta !== null && (int) $devuelta['gondola'] === (int) $baseMerma['gondola'], 'anular devuelve el stock a la góndola');
assert_true(str_starts_with(mass_ops_csv(['SKU'], [['SKU-0001']]), "\xEF\xBB\xBF"), 'el CSV lleva marca UTF-8 para Excel');
assert_true(mass_ops_config_guardar([
    'umbralBajo' => '12',
    'umbralCriticoPiso' => '1',
    'diasCaducidad' => '4',
    'slaPickingMin' => '9',
    'alertasActivas' => '1',
])['ok'], 'se guarda un umbral para el historial');
mass_ops_config_reset();
assert_true(mass_ops_config()['umbralBajo'] === 8 && mass_ops_config_historial() !== [], 'restaurar vuelve al umbral de tienda y deja historial');

echo "\n{$passed} ok, {$failed} fail\n";
exit($failed > 0 ? 1 : 0);
