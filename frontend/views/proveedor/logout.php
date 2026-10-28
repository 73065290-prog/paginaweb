<?php
declare(strict_types=1);

auth_logout();
flash_set('Sesión de proveedor cerrada');
redirect(mass_url('admin/login.php?rol=proveedor'));
