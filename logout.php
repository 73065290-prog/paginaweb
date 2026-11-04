<?php
declare(strict_types=1);

/**
 * Logout unificado (raíz).
 * Delega el cierre de sesión y destino en AuthController.
 */
require_once __DIR__ . '/includes/init.php';

$authController = new AuthController();
$resLogout = $authController->logout();

redirect((string) $resLogout['redirect']);
