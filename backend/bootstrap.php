<?php
declare(strict_types=1);

if (!defined('MASS_ROOT')) {
    define('MASS_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/repositories/ProductoRepository.php';
require_once __DIR__ . '/repositories/CategoriaRepository.php';
require_once __DIR__ . '/repositories/UsuarioRepository.php';
require_once __DIR__ . '/repositories/TiendaRepository.php';
require_once __DIR__ . '/repositories/CuponRepository.php';
require_once __DIR__ . '/repositories/PedidoRepository.php';

/**
 * CSRF canónico vive en includes/functions.php (csrf_token / csrf_field / csrf_validar).
 * CsrfMiddleware queda disponible para usos internos del backend.
 */
function mass_db_connected(): bool
{
    return Database::connected();
}
