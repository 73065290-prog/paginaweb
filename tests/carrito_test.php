<?php
declare(strict_types=1);

/**
 * Pruebas de límites y operaciones del carrito (CarritoService + delegadores).
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

$carritoService = new CarritoService();

assert_true(
    $carritoService->cantidadMaxima() === 99 && carrito_cantidad_maxima() === 99,
    'tope de cantidad = 99'
);

$carritoService->agregar(1, 1);
assert_true($carritoService->totalUnidades() === 1 && carrito_total_unidades() === 1, 'agregar 1 unidad');

carrito_agregar(1, 5);
assert_true($carritoService->totalUnidades() === 6, 'suma cantidades del mismo SKU');

$carritoService->agregar(9999, 1);
assert_true($carritoService->totalUnidades() === 6, 'id inexistente no agrega');

$carritoService->agregar(1, 0);
assert_true($carritoService->totalUnidades() === 6, 'cantidad 0 no agrega');

$_SESSION['carrito'] = [['id' => 1, 'cantidad' => 95]];
$carritoService->agregar(1, 10);
assert_true(
    (int) $carritoService->items()[0]['cantidad'] === 99,
    'agregar no supera el tope 99'
);

$_SESSION['carrito'] = [['id' => 1, 'cantidad' => 98]];
$carritoService->cambiar(1, 5);
assert_true(
    (int) carrito_items()[0]['cantidad'] === 99,
    'cambiar (+) respeta tope 99'
);

$carritoService->setCantidad(1, 150);
assert_true(
    (int) $carritoService->items()[0]['cantidad'] === 99,
    'set_cantidad capea a 99'
);

$carritoService->cambiar(1, -200);
assert_true($carritoService->items() === [], 'cambiar a <=0 elimina la línea');

$carritoService->agregar(2, 2);
$carritoService->setCantidad(2, 0);
assert_true(carrito_items() === [], 'set_cantidad 0 elimina la línea');

$carritoService->agregar(1, 3);
$carritoService->agregar(2, 1);
$carritoService->eliminar(1);
assert_true(count($carritoService->items()) === 1 && (int) $carritoService->items()[0]['id'] === 2, 'eliminar por id');

$carritoService->vaciar();
assert_true($carritoService->items() === [] && carrito_items() === [], 'vaciar limpia carrito');

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
