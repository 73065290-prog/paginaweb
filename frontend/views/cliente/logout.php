<?php declare(strict_types=1);

/* bootstrap vía shim admin|cliente_interfaces */

$eraAdmin = auth_es_admin();
auth_logout();
flash_set('Sesión cerrada correctamente');

if ($eraAdmin) {
    redirect('../admin/login.php');
}

redirect('index.php');

