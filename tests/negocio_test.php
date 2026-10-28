<?php
declare(strict_types=1);

/**
 * Pruebas de reglas de negocio (retiro vs delivery) — ejecutar:
 *   php tests/negocio_test.php
 */

session_start();
$_SESSION = [];

require_once dirname(__DIR__) . '/includes/data.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$_SESSION['carrito'] = [];
$_SESSION['pedido'] = pedido_defaults();

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

echo "=== Reglas de negocio Mass Online ===\n";

// 1) Normalización modalidad
assert_true(pedido_normalizar_entrega('express') === 'express', 'normaliza express');
assert_true(pedido_normalizar_entrega('delivery') === 'retiro', 'modo inválido cae a retiro');
assert_true(pedido_normalizar_entrega('retiro') === 'retiro', 'normaliza retiro');

// 2) Costo envío
assert_true(pedido_costo_envio('retiro') === 0.0, 'retiro es GRATIS');
assert_true(pedido_costo_envio('express') === 4.9, 'express cuesta S/ 4.90');

// 3) Cupones
assert_true(pedido_descuento_cupon('CLUBMASS10') === 5.0, 'cupón CLUBMASS10 = 5');
assert_true(pedido_descuento_cupon('CLUBMASS60') === 6.0, 'cupón CLUBMASS60 = 6');
assert_true(pedido_descuento_cupon('XXX') === 0.0, 'cupón inválido = 0');

// 4) Resumen retiro vs express
$_SESSION['carrito'] = [
    ['id' => 1, 'cantidad' => 2],
    ['id' => 2, 'cantidad' => 1],
];
$rRetiro = pedido_resumen(null, ['cupon' => '', 'entrega' => 'retiro']);
$rExpress = pedido_resumen(null, ['cupon' => '', 'entrega' => 'express']);
assert_true($rRetiro['envio'] === 0.0, 'resumen retiro envío 0');
assert_true($rExpress['envio'] === 4.9, 'resumen express envío 4.90');
assert_true(
    abs($rExpress['total'] - ($rRetiro['total'] + 4.9)) < 0.001,
    'total express = total retiro + 4.90'
);
assert_true($rRetiro['items'] === 3, '3 unidades en carrito');
assert_true($rRetiro['ahorroAprovecha'] === 6.95, 'Aprovecha+ aplica desde 3 un.');

// 5) Validación retiro: tienda agotada
$badRetiro = pedido_validar_entrega([
    'entrega' => 'retiro',
    'tienda_id' => 'brena',
    'nombre' => 'Ana',
    'apellido' => 'Lopez',
    'documento' => '12345678',
    'telefono' => '999888777',
    'horario' => 'hoy_tarde',
]);
assert_true(!$badRetiro['ok'], 'retiro en tienda agotada falla');
assert_true(
    (bool) array_filter($badRetiro['errores'], static fn ($e) => str_contains($e, 'stock')),
    'error menciona stock'
);

// 6) Validación retiro OK misma tienda
$okRetiro = pedido_validar_entrega([
    'entrega' => 'retiro',
    'tienda_id' => 'magdalena',
    'nombre' => 'Ana',
    'apellido' => 'Lopez',
    'documento' => '12345678',
    'telefono' => '999888777',
    'horario' => 'hoy_tarde',
    'comprobante' => 'boleta',
]);
assert_true($okRetiro['ok'], 'retiro Magdalena OK');
assert_true($okRetiro['data']['entrega'] === 'retiro', 'data.entrega = retiro');
assert_true(str_contains($okRetiro['data']['direccion'], 'Sucre'), 'dirección toma la de la tienda');

// 7) Validación express: dirección corta falla
$badExpress = pedido_validar_entrega([
    'entrega' => 'express',
    'direccion' => 'Calle 1',
    'nombre' => 'Ana',
    'apellido' => 'Lopez',
    'documento' => '12345678',
    'telefono' => '999888777',
    'horario' => 'hoy_express',
]);
assert_true(!$badExpress['ok'], 'express con dirección corta falla');

// 8) Validación express OK
$okExpress = pedido_validar_entrega([
    'entrega' => 'express',
    'direccion' => 'Av. Brasil 2340, Dpto 802, Magdalena',
    'nombre' => 'Mariana',
    'apellido' => 'Quispe',
    'documento' => '45678912',
    'telefono' => '999111222',
    'horario' => 'hoy_express',
    'tienda_id' => 'magdalena',
]);
assert_true($okExpress['ok'], 'express con dirección completa OK');
assert_true($okExpress['data']['entrega'] === 'express', 'data.entrega = express');

// 9) Horario retiro inválido en express
$badHorario = pedido_validar_entrega([
    'entrega' => 'express',
    'direccion' => 'Av. Brasil 2340 Magdalena del Mar',
    'nombre' => 'Ana',
    'apellido' => 'Lopez',
    'documento' => '12345678',
    'telefono' => '999888777',
    'horario' => 'pasado_manana', // solo retiro
]);
assert_true(!$badHorario['ok'], 'horario de retiro no válido en express');

// 10) Tracking distinto por modalidad
$trExpress = pedido_tracking_pasos('express', 'gondola');
$trRetiro = pedido_tracking_pasos('retiro', 'gondola');
$keysE = array_column($trExpress, 'key');
$keysR = array_column($trRetiro, 'key');
assert_true(in_array('camino', $keysE, true), 'express tiene paso en camino');
assert_true(in_array('listo', $keysR, true), 'retiro tiene paso listo para retiro');
assert_true(!in_array('camino', $keysR, true), 'retiro no tiene motorizado');
assert_true($trExpress[1]['estado'] === 'current', 'estado gondola es current');

// 11) Confirmar pedido guarda snapshot y vacía carrito
pedido_set($okExpress['data'] + ['cupon' => 'CLUBMASS10']);
$orden = pedido_confirmar('tarjeta');
assert_true(is_array($orden), 'pedido_confirmar retorna orden');
assert_true(str_starts_with((string) $orden['codigo'], 'MASS-'), 'código MASS-');
assert_true(carrito_items() === [], 'carrito vacío tras confirmar');
assert_true(pedido_ultimo() !== null, 'ultimo_pedido en sesión');
assert_true(($orden['resumen']['envio'] ?? -1) === 4.9, 'orden express conserva envío');
assert_true(($orden['pedido']['entrega'] ?? '') === 'express', 'orden conserva modalidad');

// 12) Confirmar retiro sin envío
$_SESSION['carrito'] = [['id' => 1, 'cantidad' => 1]];
pedido_set($okRetiro['data'] + ['cupon' => '']);
$ordenR = pedido_confirmar('yape');
assert_true(($ordenR['resumen']['envio'] ?? -1) === 0.0, 'orden retiro envío 0');
assert_true(($ordenR['pedido']['entrega'] ?? '') === 'retiro', 'orden retiro modalidad');

echo "\nResultado: {$passed} OK, {$failed} FAIL\n";
exit($failed > 0 ? 1 : 0);
