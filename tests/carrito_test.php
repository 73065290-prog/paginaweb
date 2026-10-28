<?php
declare(strict_types=1);

/**
 * Pruebas de límites y operaciones del carrito.
 *   php tests/carrito_test.php
 */

session_start();
$_SESSION = [];

require_once dirname(__DIR__) . '/includes/data.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$_SESSION['carrito'] = [];
$_SESSION['guardados'] = [];

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

echo "=== Carrito Mass Online ===\n";

assert_true(carrito_cantidad_maxima() === 99, 'tope de cantidad = 99');

carrito_agregar(1, 1);
assert_true(carrito_total_unidades() === 1, 'agregar 1 unidad');

carrito_agregar(1, 5);
assert_true(carrito_total_unidades() === 6, 'suma cantidades del mismo SKU');

carrito_agregar(9999, 1);
assert_true(carrito_total_unidades() === 6, 'id inexistente no agrega');

carrito_agregar(1, 0);
assert_true(carrito_total_unidades() === 6, 'cantidad 0 no agrega');

$_SESSION['carrito'] = [['id' => 1, 'cantidad' => 95]];
carrito_agregar(1, 10);
assert_true(
    (int) carrito_items()[0]['cantidad'] === 99,
    'agregar no supera el tope 99'
);

$_SESSION['carrito'] = [['id' => 1, 'cantidad' => 98]];
carrito_cambiar(1, 5);
assert_true(
    (int) carrito_items()[0]['cantidad'] === 99,
    'cambiar (+) respeta tope 99'
);

carrito_set_cantidad(1, 150);
assert_true(
    (int) carrito_items()[0]['cantidad'] === 99,
    'set_cantidad capea a 99'
);

carrito_cambiar(1, -200);
assert_true(carrito_items() === [], 'cambiar a <=0 elimina la línea');

carrito_agregar(2, 2);
carrito_set_cantidad(2, 0);
assert_true(carrito_items() === [], 'set_cantidad 0 elimina la línea');

carrito_agregar(1, 3);
carrito_agregar(2, 1);
carrito_eliminar(1);
assert_true(count(carrito_items()) === 1 && (int) carrito_items()[0]['id'] === 2, 'eliminar por id');

carrito_vaciar();
assert_true(carrito_items() === [], 'vaciar limpia carrito');

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
