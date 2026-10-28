<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';

auth_logout();
flash_set('Sesión cerrada correctamente');
redirect('login.php');
