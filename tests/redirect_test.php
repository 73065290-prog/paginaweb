<?php
declare(strict_types=1);

session_start();
$_SESSION = [];
require_once dirname(__DIR__) . '/includes/data.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$ok = 0; $fail = 0;
function assert_true(bool $c, string $m): void {
    global $ok, $fail;
    if ($c) { echo "OK  $m\n"; $ok++; } else { echo "FAIL  $m\n"; $fail++; }
}

echo "=== Redirect seguro ===\n";
assert_true(mass_safe_return_url('pedido.php') === 'pedido.php', 'ruta local OK');
assert_true(mass_safe_return_url('pedido.php?paso=2') === 'pedido.php?paso=2', 'query OK');
assert_true(mass_safe_return_url('https://evil.test') === 'pedido.php', 'bloquea http');
assert_true(mass_safe_return_url('//evil.test') === 'pedido.php', 'bloquea protocol-relative');
assert_true(mass_safe_return_url('../admin/login.php') === 'pedido.php', 'bloquea path traversal');
assert_true(mass_safe_return_url("pedido.php\r\nLocation: https://evil") === 'pedido.php', 'bloquea CRLF');

echo "\nResultado: $ok OK, $fail FAIL\n";
exit($fail > 0 ? 1 : 0);
