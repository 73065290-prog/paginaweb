<?php declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$eraAdmin = auth_es_admin();
auth_logout();
flash_set('Sesión cerrada correctamente');

if ($eraAdmin) {
    redirect('../admin/login.php');
}

redirect('index.php');
