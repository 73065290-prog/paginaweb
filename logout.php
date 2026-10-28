<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$rol = (string) (auth_usuario()['rol'] ?? '');
auth_logout();
flash_set('Sesión cerrada correctamente');

if ($rol === 'admin') {
    redirect('admin/login.php?rol=admin');
}
if ($rol === 'proveedor') {
    redirect('admin/login.php?rol=proveedor');
}
if ($rol === 'cliente') {
    redirect('admin/login.php?rol=cliente');
}

redirect('cliente_interfaces/index.php');
