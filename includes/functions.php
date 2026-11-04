<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/bootstrap.php';
require_once dirname(__DIR__) . '/backend/controllers/AuthController.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dinero(float $n): string
{
    return 'S/ ' . number_format($n, 2, '.', '');
}

function producto_por_id(int $id): ?array
{
    return (new ProductoService())->obtenerPorId($id);
}

function carrito_items(): array
{
    return (new CarritoService())->items();
}

function carrito_total_unidades(): int
{
    return (new CarritoService())->totalUnidades();
}

function carrito_total_monto(): float
{
    return (new CarritoService())->totalMonto();
}

/** Tope de unidades por línea en carrito (alineado con UI qty). */
function carrito_cantidad_maxima(): int
{
    return (new CarritoService())->cantidadMaxima();
}

function carrito_agregar(int $id, int $cantidad = 1): void
{
    (new CarritoService())->agregar($id, $cantidad);
}

function carrito_cambiar(int $id, int $delta): void
{
    (new CarritoService())->cambiar($id, $delta);
}

function carrito_vaciar(): void
{
    (new CarritoService())->vaciar();
}

function carrito_eliminar(int $id): void
{
    (new CarritoService())->eliminar($id);
}

function carrito_set_cantidad(int $id, int $cantidad): void
{
    (new CarritoService())->setCantidad($id, $cantidad);
}

function carrito_guardar_despues(int $id): void
{
    (new CarritoService())->guardarParaDespues($id);
}

function pedido_defaults(): array
{
    return (new PedidoService())->defaults();
}

function pedido_estado(): array
{
    return (new PedidoService())->estado();
}

function pedido_set(array $data): void
{
    (new PedidoService())->set($data);
}

/** Normaliza modalidad: solo retiro | express (delega en PedidoValidator). */
function pedido_normalizar_entrega(string $modo): string
{
    return (new PedidoValidator())->normalizarEntrega($modo);
}

function pedido_costo_envio(string $modo): float
{
    return (new PedidoService())->costoEnvio($modo);
}

/**
 * Cupones válidos Club Mass → monto de descuento (delega en CuponService).
 *
 * @return array<string, float>
 */
function pedido_cupones_validos(): array
{
    return (new CuponService())->cuponesValidos();
}

function pedido_descuento_cupon(string $cupon): float
{
    return (new CuponService())->descuento($cupon);
}

function pedido_es_cupon_valido(string $cupon): bool
{
    return (new CuponService())->esValido($cupon);
}

/**
 * Valida datos de entrega según modalidad (delega en PedidoValidator).
 *
 * @param array<string, mixed> $data
 * @return array{ok:bool, errores:array<int,string>, data:array<string,mixed>}
 */
function pedido_validar_entrega(array $data): array
{
    return (new PedidoValidator())->validateEntrega($data);
}

/**
 * Totales del checkout (delega en PedidoService).
 *
 * @param array<int, array{id:int|string, cantidad:int|string}>|null $items
 * @param array<string, mixed>|null $pedidoOverride
 * @return array{
 *   items:int,
 *   subtotal:float,
 *   ahorroOfertas:float,
 *   ahorroAprovecha:float,
 *   descuentoCupon:float,
 *   envio:float,
 *   ahorroTotal:float,
 *   total:float,
 *   cupon:string,
 *   entrega:string,
 *   lineas:array<int, array<string, mixed>>
 * }
 */
function pedido_resumen(?array $items = null, ?array $pedidoOverride = null): array
{
    return (new PedidoService())->resumen($items, $pedidoOverride);
}

/**
 * Steps de tracking según modalidad (Figma 20-6246).
 *
 * @return array<int, array{key:string, titulo:string, detalle:string, estado:string}>
 */
function pedido_tracking_pasos(string $entrega, string $estadoActual = 'gondola'): array
{
    return (new PedidoService())->trackingPasos($entrega, $estadoActual);
}

/**
 * Confirma pedido demo/BD: guarda snapshot y vacía carrito.
 *
 * @return array<string, mixed>|null
 */
function pedido_confirmar(string $pago = 'yape'): ?array
{
    return (new PedidoService())->confirmar($pago);
}

function pedido_ultimo(): ?array
{
    return (new PedidoService())->ultimo();
}

/**
 * Hash bcrypt de la contraseña demo académica (login sigue siendo 123456).
 * No se guarda el secreto en texto plano (higiene SonarQube S2068).
 */
function mass_demo_password_hash(): string
{
    return (new AuthService())->demoPasswordHash();
}

/** Usuarios demo SGC-Mass (Figma 30-9006). Prefiere MySQL si está conectado (delega en AuthService). */
function mass_usuarios_auth(): array
{
    return (new AuthService())->usuariosAuth();
}

/**
 * @return array{ok:bool, errores:array<int,string>, usuario:?array<string,mixed>}
 */
function auth_intentar(string $usuario, string $password, string $rolEsperado = 'admin'): array
{
    return (new AuthService())->intentar($usuario, $password, $rolEsperado);
}

function auth_login(array $usuario, bool $recordar = false): void
{
    (new AuthService())->login($usuario, $recordar);
}

function auth_logout(): void
{
    (new AuthService())->logout();
}

/**
 * Devuelve la ruta de redirección tras cerrar sesión según si el usuario activo era administrador.
 */
function auth_logout_destino(bool $eraAdmin): string
{
    return (new AuthService())->logoutDestino($eraAdmin);
}

/** Obtiene el usuario autenticado en sesión (delega en AuthMiddleware). */
function auth_usuario(): ?array
{
    return AuthMiddleware::user();
}

/** Verifica si la sesión activa tiene rol administrador (delega en AuthMiddleware). */
function auth_es_admin(): bool
{
    return AuthMiddleware::isAdmin();
}

/** Verifica si la sesión activa tiene rol cliente (delega en AuthMiddleware). */
function auth_es_cliente(): bool
{
    return AuthMiddleware::isCliente();
}

/** Protege páginas admin: redirige a login si no hay sesión admin (delega en AuthMiddleware). */
function auth_requerir_admin(): void
{
    AuthMiddleware::requireAdmin();
}

/** Protege páginas exclusivas de cliente autenticado (delega en AuthMiddleware). */
function auth_requerir_cliente(): void
{
    AuthMiddleware::requireCliente();
}

function flash_set(string $mensaje): void
{
    $_SESSION['flash'] = $mensaje;
}

/** Token CSRF por sesión (delega en CsrfMiddleware). */
function csrf_token(): string
{
    return CsrfMiddleware::token();
}

/** Campo oculto CSRF para formularios POST (delega en CsrfMiddleware). */
function csrf_field(): string
{
    return CsrfMiddleware::field();
}

/** Valida el token CSRF recibido frente al token de sesión (delega en CsrfMiddleware). */
function csrf_validar(?string $token): bool
{
    return CsrfMiddleware::validate($token);
}

/**
 * Normaliza destino post-acción del carrito (anti open-redirect).
 * Solo permite rutas relativas locales sin esquema ni path traversal.
 */
function mass_safe_return_url(string $url, string $fallback = 'pedido.php'): string
{
    $url = trim($url);
    if ($url === '' || str_contains($url, '://') || str_starts_with($url, '//') || str_contains($url, '..')) {
        return $fallback;
    }
    // Evitar saltos de línea en Location
    $url = str_replace(["\r", "\n", "\0"], '', $url);
    if ($url === '' || !preg_match('~^[A-Za-z0-9_./?&=%#\\-]+$~', $url)) {
        return $fallback;
    }
    return $url;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function mass_base(): string
{
    return defined('MASS_BASE_HREF') ? MASS_BASE_HREF : '';
}

/**
 * URL de página/app relativa a la raíz del proyecto (no assets).
 * Ej.: mass_url('logout.php') → ../logout.php desde admin/ o cliente_interfaces/
 */
function mass_url(string $path = ''): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    return mass_base() . $path;
}

/** Ruta a asset estático (CSS/JS/IMG) en frontend/assets. */
function mass_asset(string $path): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if (str_starts_with($path, 'frontend/assets/')) {
        return mass_base() . $path;
    }
    if (str_starts_with($path, 'assets/')) {
        return mass_base() . 'frontend/' . $path;
    }
    // Compatibilidad: archivos sueltos de assets (solo si parecen estáticos)
    if (preg_match('/\.(css|js|svg|png|jpe?g|gif|webp|ico)$/i', $path)) {
        return mass_base() . 'frontend/assets/' . $path;
    }
    // No tratar rutas de app (logout.php, admin/...) como assets
    return mass_url($path);
}

/** URL relativa a una página de la tienda (cliente_interfaces). */
function mass_cliente_url(string $page = 'index.php'): string
{
    $page = ltrim($page, '/');
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_ends_with($scriptDir, '/cliente_interfaces')) {
        return $page;
    }
    if (str_ends_with($scriptDir, '/admin')) {
        return '../cliente_interfaces/' . $page;
    }
    return 'cliente_interfaces/' . $page;
}

function pagina_actual(): string
{
    return basename($_SERVER['PHP_SELF'] ?? 'index.php');
}

/**
 * @param array<string, mixed> $filtros
 * @return array<int, array<string, mixed>>
 */
function filtrar_productos(array $filtros): array
{
    return (new ProductoService())->filtrarCatalogo($filtros);
}

function render_product_card(array $p): void
{
    require MASS_ROOT . '/frontend/views/components/product-card.php';
}

function render_category_card(array $c): void
{
    require MASS_ROOT . '/frontend/views/components/category-card.php';
}

function render_store_card(array $tienda): void
{
    require MASS_ROOT . '/frontend/views/components/store-card.php';
}

/**
 * @return array<int, array<string, mixed>>
 */
function productos_por_ids(array $ids): array
{
    return (new ProductoService())->obtenerPorIds($ids);
}
