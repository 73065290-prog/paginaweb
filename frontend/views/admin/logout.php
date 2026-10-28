<?php
declare(strict_types=1);

/* bootstrap vía shim */

auth_logout();
flash_set('Sesión cerrada correctamente');
$rol = (string) ($_GET['rol'] ?? 'admin');
$rol = $rol === 'cliente' ? 'cliente' : 'admin';
redirect('login.php?rol=' . rawurlencode($rol));

