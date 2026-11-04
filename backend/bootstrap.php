<?php
declare(strict_types=1);

if (!defined('MASS_ROOT')) {
    define('MASS_ROOT', dirname(__DIR__));
}

// 1. Conexión a base de datos
require_once __DIR__ . '/Database.php';

// 2. Modelos de dominio
require_once __DIR__ . '/models/Categoria.php';
require_once __DIR__ . '/models/Producto.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Tienda.php';
require_once __DIR__ . '/models/InventarioItem.php';
require_once __DIR__ . '/models/Cupon.php';
require_once __DIR__ . '/models/Pedido.php';
require_once __DIR__ . '/models/PedidoDetalle.php';

// 3. Validadores de reglas de negocio
require_once __DIR__ . '/validators/ProductoValidator.php';
require_once __DIR__ . '/validators/PedidoValidator.php';

// 4. Repositorios de acceso a datos
require_once __DIR__ . '/repositories/ProductoRepository.php';
require_once __DIR__ . '/repositories/CategoriaRepository.php';
require_once __DIR__ . '/repositories/UsuarioRepository.php';
require_once __DIR__ . '/repositories/TiendaRepository.php';
require_once __DIR__ . '/repositories/CuponRepository.php';
require_once __DIR__ . '/repositories/PedidoRepository.php';

// 5. Servicios de negocio
require_once __DIR__ . '/services/ProductoService.php';
require_once __DIR__ . '/services/InventarioService.php';
require_once __DIR__ . '/services/CarritoService.php';
require_once __DIR__ . '/services/CuponService.php';
require_once __DIR__ . '/services/PedidoService.php';
require_once __DIR__ . '/services/AuthService.php';

// 6. Middleware de seguridad (CSRF y Autenticación/Autorización)
require_once __DIR__ . '/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

/**
 * Comprueba si existe una conexión PDO activa a MySQL/MariaDB.
 */
function mass_db_connected(): bool
{
    return Database::connected();
}
