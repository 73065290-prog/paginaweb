<?php
declare(strict_types=1);

/**
 * Pruebas autenticación — roles admin / cliente / proveedor
 *   php tests/auth_test.php
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

echo "=== Auth SGC-Mass (3 roles) ===\n";

$r = auth_intentar('', '', 'admin');
assert_true(!$r['ok'], 'usuario/password vacíos falla');
assert_true(count($r['errores']) >= 2, 'reporta ambos errores');

$r = auth_intentar('admin', 'wrong', 'admin');
assert_true(!$r['ok'], 'password incorrecta falla');

$r = auth_intentar('admin', '123456', 'admin');
assert_true($r['ok'], 'admin / 123456 OK');
assert_true(($r['usuario']['rol'] ?? '') === 'admin', 'rol admin');

$r = auth_intentar('admin.operaciones@mass.pe', '123456', 'admin');
assert_true($r['ok'], 'correo corporativo OK');

$r = auth_intentar('admin', '123456', 'cliente');
assert_true(!$r['ok'], 'admin no entra en modo cliente');

$r = auth_intentar('cliente', '123456', 'cliente');
assert_true($r['ok'], 'cliente / 123456 OK');
assert_true(($r['usuario']['rol'] ?? '') === 'cliente', 'rol cliente');

$r = auth_intentar('proveedor@mass.pe', '123456', 'cliente');
assert_true(!$r['ok'], 'proveedor no entra como cliente');

$r = auth_intentar('proveedor', '123456', 'proveedor');
assert_true($r['ok'], 'proveedor / 123456 OK');
assert_true(($r['usuario']['rol'] ?? '') === 'proveedor', 'rol proveedor');
assert_true(($r['usuario']['empresa'] ?? '') !== '', 'proveedor trae empresa');

auth_login($r['usuario'], true);
assert_true(auth_es_proveedor(), 'sesión proveedor activa');
assert_true(!auth_es_cliente(), 'proveedor no es cliente');
assert_true(!auth_es_admin(), 'proveedor no es admin');
assert_true(auth_destino_post_login('proveedor') === '../proveedor/index.php', 'destino proveedor');
assert_true(auth_destino_post_login('cliente') === '../cliente_interfaces/index.php', 'destino cliente');
auth_logout();
assert_true(auth_usuario() === null, 'logout limpia sesión');

$r = auth_intentar('cliente', '123456', 'cliente');
auth_login($r['usuario']);
assert_true(auth_es_cliente(), 'sesión cliente activa');
assert_true(!auth_es_proveedor(), 'cliente no es proveedor');
auth_logout();

$r = auth_intentar('admin', '123456', 'admin');
auth_login($r['usuario']);
assert_true(auth_es_admin(), 'sesión admin activa');
assert_true(auth_usuario() !== null, 'sesión válida tras regenerate_id');

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
