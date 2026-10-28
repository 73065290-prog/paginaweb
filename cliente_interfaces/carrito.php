<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pedido.php');
}

$accion = (string) ($_POST['accion'] ?? '');
$volver = (string) ($_POST['volver'] ?? 'pedido.php');

// Evitar open redirect: solo rutas relativas del sitio
if ($volver === '' || str_contains($volver, '://') || str_starts_with($volver, '//')) {
    $volver = 'pedido.php';
}

switch ($accion) {
    case 'agregar':
        $id = (int) ($_POST['id'] ?? 0);
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
        carrito_agregar($id, $cantidad);
        flash_set('Producto agregado al carrito');
        break;

    case 'cambiar':
        $id = (int) ($_POST['id'] ?? 0);
        $delta = (int) ($_POST['delta'] ?? 0);
        carrito_cambiar($id, $delta);
        break;

    case 'set_cantidad':
        $id = (int) ($_POST['id'] ?? 0);
        $cantidad = (int) ($_POST['cantidad'] ?? 1);
        carrito_set_cantidad($id, $cantidad);
        break;

    case 'eliminar':
        carrito_eliminar((int) ($_POST['id'] ?? 0));
        flash_set('Producto eliminado del carrito');
        break;

    case 'vaciar':
        carrito_vaciar();
        flash_set('Canasta vaciada');
        break;

    case 'guardar':
        carrito_guardar_despues((int) ($_POST['id'] ?? 0));
        flash_set('Guardado para después');
        break;

    case 'cupon':
        $codigo = strtoupper(trim((string) ($_POST['cupon'] ?? '')));
        if ($codigo === 'CLUBMASS10') {
            pedido_set(['cupon' => $codigo]);
            flash_set('Cupón CLUBMASS10 aplicado');
        } elseif ($codigo === '') {
            pedido_set(['cupon' => '']);
            flash_set('Cupón quitado');
        } else {
            flash_set('Cupón no válido — prueba CLUBMASS10');
        }
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
        if (!carrito_items()) {
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
        if (!carrito_items()) {
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
        if (!carrito_items()) {
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
