<?php
declare(strict_types=1);

/**
 * Pruebas portal proveedor B2B
 *   php tests/proveedor_test.php
 */

session_start();
$_SESSION = [];

require_once dirname(__DIR__) . '/includes/data.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$passed = 0;
$failed = 0;

function assert_true(bool $cond, string $msg): void
{
    global $passed, $failed;
    if ($cond) {
        echo "OK  {$msg}\n";
        $passed++;
    } else {
        echo "FAIL  {$msg}\n";
        $failed++;
    }
}

echo "=== Proveedor Mass B2B ===\n";

$r = auth_intentar('proveedor', '123456', 'proveedor');
assert_true($r['ok'], 'login proveedor OK');
auth_login($r['usuario']);

assert_true(($perfil = mass_proveedor_perfil()) && ($perfil['empresa'] ?? '') !== '', 'perfil con empresa');
assert_true(count(mass_proveedor_skus()) >= 4, 'catálogo SKU cargado');
assert_true(count(mass_proveedor_ordenes()) >= 3, 'OC demo cargadas');

$pend = mass_proveedor_ordenes('pendiente');
assert_true(count($pend) >= 1, 'hay OC pendientes');
$id = (string) $pend[0]['id'];

assert_true(!proveedor_orden_cambiar_estado($id, 'entregada')['ok'], 'no salta de pendiente a entregada');
assert_true(proveedor_orden_cambiar_estado($id, 'confirmada')['ok'], 'confirma OC pendiente');
assert_true((mass_proveedor_orden_por_id($id)['estado'] ?? '') === 'confirmada', 'estado confirmada');
assert_true(proveedor_orden_cambiar_estado($id, 'en_ruta')['ok'], 'despacha a en_ruta');
assert_true(proveedor_orden_cambiar_estado($id, 'entregada')['ok'], 'cierra como entregada');
assert_true(isset(mass_proveedor_kpis()['skusActivos']), 'KPIs proveedor');
assert_true(count(mass_proveedor_entregas()) >= 1, 'entregas demo');

auth_logout();

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
