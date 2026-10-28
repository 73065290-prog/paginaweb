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
 * Helpers globales CSRF (usados en vistas).
 */
function csrf_token(): string
{
    return CsrfMiddleware::token();
}

function csrf_field(): string
{
    return CsrfMiddleware::field();
}

function mass_db_connected(): bool
{
    return Database::connected();
}
