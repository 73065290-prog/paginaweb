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

$tope1 = carrito_tope_linea(1);
$_SESSION['carrito'] = [['id' => 1, 'cantidad' => max(1, $tope1 - 2)]];
carrito_agregar(1, 10);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $tope1,
    'agregar no supera tope efectivo (stock/99)'
);

$_SESSION['carrito'] = [['id' => 1, 'cantidad' => max(1, $tope1 - 1)]];
carrito_cambiar(1, 5);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $tope1,
    'cambiar (+) respeta tope efectivo'
);

carrito_set_cantidad(1, 150);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $tope1,
    'set_cantidad capea al tope efectivo'
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

$stock1 = producto_stock_disponible(1);
assert_true($stock1 === 42, 'stock demo producto 1 = 42');
assert_true(carrito_tope_linea(1) === 42, 'tope línea = min(99, stock)');

$idBajo = 0;
foreach (mass_productos() as $p) {
    if (($p['stockTone'] ?? '') === 'bajo') {
        $idBajo = (int) $p['id'];
        break;
    }
}
assert_true($idBajo > 0, 'existe producto con stock bajo');
$stockBajo = producto_stock_disponible($idBajo);
assert_true($stockBajo === 8, 'stock bajo demo = 8');

$_SESSION['carrito'] = [];
carrito_agregar($idBajo, 20);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $stockBajo,
    'agregar no supera stock disponible'
);

$_SESSION['carrito'] = [['id' => $idBajo, 'cantidad' => $stockBajo - 1]];
carrito_agregar($idBajo, 5);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $stockBajo,
    'sumar no supera stock disponible'
);

carrito_set_cantidad($idBajo, 99);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $stockBajo,
    'set_cantidad capea al stock'
);

carrito_cambiar($idBajo, 10);
assert_true(
    (int) carrito_items()[0]['cantidad'] === $stockBajo,
    'cambiar (+) respeta stock'
);

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
