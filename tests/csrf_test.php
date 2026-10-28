<?php
declare(strict_types=1);

/**
 * Pruebas CSRF y helpers de seguridad.
 *   php tests/csrf_test.php
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

echo "=== CSRF Mass Online ===\n";

$token = csrf_token();
assert_true(strlen($token) === 64, 'token tiene 64 hex chars');
assert_true(csrf_token() === $token, 'token estable en la misma sesión');
assert_true(csrf_validar($token), 'token válido pasa');
assert_true(!csrf_validar('token-falso'), 'token falso falla');
assert_true(!csrf_validar(null), 'token null falla');
assert_true(!csrf_validar(''), 'token vacío falla');

$field = csrf_field();
assert_true(str_contains($field, 'csrf_token'), 'csrf_field incluye name');
assert_true(str_contains($field, $token), 'csrf_field incluye valor del token');
assert_true(str_contains($field, 'type="hidden"'), 'csrf_field es hidden');

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
