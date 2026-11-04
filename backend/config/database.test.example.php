<?php
declare(strict_types=1);

/**
 * Conexión de la suite PHPUnit "Integration" (tests/Integration).
 *
 * Valores por defecto de XAMPP. Para otras credenciales usa variables de entorno
 * (MASS_TEST_DB_HOST, MASS_TEST_DB_PORT, MASS_TEST_DB_USER, MASS_TEST_DB_PASS) o copia este
 * archivo a database.test.local.php, que NO debe subirse al repositorio.
 *
 * dbname debe ser exactamente mass_online_test: la suite borra y recrea sus tablas y se
 * niega a ejecutarse contra cualquier otra base.
 */
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'mass_online_test',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];
