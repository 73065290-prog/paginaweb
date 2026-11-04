<?php
declare(strict_types=1);

/* bootstrap vía shim */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pedido.php');
}

$accion = (string) ($_POST['accion'] ?? '');
$volver = mass_safe_return_url((string) ($_POST['volver'] ?? 'pedido.php'), 'pedido.php');

if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
    flash_set('Sesión inválida. Recarga la página e intenta de nuevo.');
    redirect($volver);
}

$carritoService = new CarritoService();
$cuponService = new CuponService();

switch ($accion) {
    case 'agregar':
        $id = (int) ($_POST['id'] ?? 0);
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
        $carritoService->agregar($id, $cantidad);
        flash_set('Producto agregado al carrito');
        break;

    case 'cambiar':
        $id = (int) ($_POST['id'] ?? 0);
        $delta = (int) ($_POST['delta'] ?? 0);
        $carritoService->cambiar($id, $delta);
        break;

    case 'set_cantidad':
        $id = (int) ($_POST['id'] ?? 0);
        $cantidad = (int) ($_POST['cantidad'] ?? 1);
        $carritoService->setCantidad($id, $cantidad);
        break;

    case 'eliminar':
        $carritoService->eliminar((int) ($_POST['id'] ?? 0));
        flash_set('Producto eliminado del carrito');
        break;

    case 'vaciar':
        $carritoService->vaciar();
        flash_set('Canasta vaciada');
        break;

    case 'guardar':
        $carritoService->guardarParaDespues((int) ($_POST['id'] ?? 0));
        flash_set('Guardado para después');
        break;

    case 'recuperar':
        $carritoService->recuperarGuardado((int) ($_POST['id'] ?? 0));
        flash_set('Producto devuelto al carrito');
        break;

    case 'cupon':
        $eval = $cuponService->evaluarAplicacion((string) ($_POST['cupon'] ?? ''));
        if ($eval['ok']) {
            pedido_set(['cupon' => $eval['codigo']]);
        }
        flash_set($eval['mensaje']);
        break;

    case 'entrega':
        $modo = pedido_normalizar_entrega((string) ($_POST['entrega'] ?? 'retiro'));
        $extra = ['entrega' => $modo];
        if ($modo === 'retiro') {
            $extra['horario'] = 'hoy_tarde';
        } else {
            $extra['horario'] = 'hoy_express';
        }
        if (!empty($_POST['tienda_id'])) {
            $extra['tienda_id'] = (string) $_POST['tienda_id'];
        }
        pedido_set($extra);
        flash_set($modo === 'retiro' ? 'Modalidad: Retiro en tienda (GRATIS)' : 'Modalidad: Delivery Express (+S/ 4.90)');
        break;

    case 'paso2':
        if (!$carritoService->items()) {
            flash_set('Agrega productos antes de continuar');
            $volver = 'pedido.php';
        } else {
            $modo = pedido_normalizar_entrega((string) ($_POST['entrega'] ?? pedido_estado()['entrega']));
            pedido_set([
                'entrega' => $modo,
                'horario' => $modo === 'express' ? 'hoy_express' : 'hoy_tarde',
            ]);
            $volver = 'pedido.php?paso=2';
        }
        break;

    case 'paso3':
        $valid = pedido_validar_entrega($_POST);
        if (!$valid['ok']) {
            flash_set(implode(' · ', $valid['errores']));
            $volver = 'pedido.php?paso=2';
            break;
        }
        pedido_set($valid['data']);
        $volver = 'pedido.php?paso=3';
        break;

    case 'confirmar':
        if (!$carritoService->items()) {
            flash_set('Agrega productos antes de continuar');
            $volver = 'pedido.php';
            break;
        }
        $orden = pedido_confirmar((string) ($_POST['pago'] ?? 'yape'));
        if ($orden === null) {
            flash_set('No se pudo confirmar el pedido');
            $volver = 'pedido.php';
            break;
        }
        flash_set('Pedido ' . $orden['codigo'] . ' confirmado');
        $volver = 'confirmacion.php';
        break;

    case 'checkout':
        if (!$carritoService->items()) {
            flash_set('Agrega productos antes de continuar');
        } else {
            $volver = 'pedido.php';
        }
        break;

    default:
        flash_set('Acción no válida');
        break;
}

redirect($volver);
