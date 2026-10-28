<?php
declare(strict_types=1);

auth_logout();
flash_set('Sesión cerrada correctamente');
redirect(mass_url('admin/login.php?rol=cliente'));
