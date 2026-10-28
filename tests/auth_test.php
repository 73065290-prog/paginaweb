<?php
declare(strict_types=1);

/**
 * Pruebas autenticación SGC-Mass (Figma 30-9006)
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

echo "=== Auth SGC-Mass ===\n";

// Vacío
$r = auth_intentar('', '', 'admin');
assert_true(!$r['ok'], 'usuario/password vacíos falla');
assert_true(count($r['errores']) >= 2, 'reporta ambos errores');

// Credenciales malas
$r = auth_intentar('admin', 'wrong', 'admin');
assert_true(!$r['ok'], 'password incorrecta falla');

// Admin OK con usuario corto
$r = auth_intentar('admin', '123456', 'admin');
assert_true($r['ok'], 'admin / 123456 OK');
assert_true(($r['usuario']['rol'] ?? '') === 'admin', 'rol admin');

// Admin OK con correo
$r = auth_intentar('admin.operaciones@mass.pe', '123456', 'admin');
assert_true($r['ok'], 'correo corporativo OK');

// Rol cruzado: admin en modo cliente
$r = auth_intentar('admin', '123456', 'cliente');
assert_true(!$r['ok'], 'admin no entra en modo cliente');

// Cliente OK
$r = auth_intentar('cliente', '123456', 'cliente');
assert_true($r['ok'], 'cliente / 123456 OK');
assert_true(($r['usuario']['rol'] ?? '') === 'cliente', 'rol cliente');

// Proveedor OK
$r = auth_intentar('proveedor@mass.pe', '123456', 'cliente');
assert_true($r['ok'], 'proveedor correo OK');

// Sesión login/logout
auth_login($r['usuario'], true);
assert_true(auth_es_cliente(), 'sesión cliente activa');
assert_true(!auth_es_admin(), 'no es admin');
auth_logout();
assert_true(auth_usuario() === null, 'logout limpia sesión');

$r = auth_intentar('admin', '123456', 'admin');
auth_login($r['usuario']);
assert_true(auth_es_admin(), 'sesión admin activa');
assert_true(str_contains((string) auth_usuario()['nombre'], 'Gonzalo') || auth_usuario()['nombre'] !== '', 'nombre en sesión');

// Regeneración: tras login la sesión sigue usable
assert_true(auth_usuario() !== null, 'sesión válida tras regenerate_id');

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
