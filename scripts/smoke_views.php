<?php
declare(strict_types=1);

$_SERVER['SCRIPT_NAME'] = '/paginaweb/cliente_interfaces/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
require dirname(__DIR__) . '/includes/init.php';

/** @var Router $router */
$router = require MASS_ROOT . '/backend/routes/web.php';

// 1. Render inicial de portada (conservando salida histórica del smoke test)
ob_start();
$resHome = $router->dispatch('cliente_interfaces/index.php', 'GET');
$html = (string) ob_get_clean();

echo 'db=' . (Database::connected() ? '1' : '0') . PHP_EOL;
echo 'asset=' . mass_asset('assets/css/styles.css') . PHP_EOL;
echo 'view_ok=' . (($resHome['status'] === 200 && (str_contains($html, 'hero') || str_contains($html, 'Mass'))) ? '1' : '0') . PHP_EOL;
echo 'len=' . strlen($html) . PHP_EOL;

$allOk = ($resHome['status'] === 200 && strlen($html) > 100);
$routes = $router->routes();
echo 'routes_registered=' . count($routes) . PHP_EOL;

// 2. Verificar resolución de contrato (URL -> método -> Controller::acción -> vista) para todas las rutas registradas
foreach ($routes as $uri => $meta) {
    $primaryMethod = $meta['methods'][0] ?? 'GET';
    $res = $router->resolve($uri, $primaryMethod);
    $ctrlOk = class_exists($meta['controller']) && method_exists($meta['controller'], $meta['action']);
    $viewOk = is_file(MASS_ROOT . '/' . $meta['view']);
    $ok = ($res['status'] === 200 && $ctrlOk && $viewOk);
    if (!$ok) {
        $allOk = false;
    }
    echo sprintf(
        'route_contract[%s]=%s method=%s target=%s::%s view=%s',
        $uri,
        $ok ? 'OK' : 'FAIL',
        implode('|', $meta['methods']),
        $meta['controller'],
        $meta['action'],
        $meta['view']
    ) . PHP_EOL;
}

// 3. Ejecutar mediante Router::dispatch() las rutas renderizables de tienda y admin (con contexto real)
// Contexto cliente con producto, carrito y pedido confirmado
$_GET = ['id' => '1'];
carrito_agregar(1, 2);
$validEntrega = pedido_validar_entrega([
    'entrega' => 'retiro',
    'nombre' => 'Ana',
    'apellido' => 'Torres',
    'documento' => '12345678',
    'telefono' => '987654321',
    'horario' => 'hoy_tarde',
    'tienda_id' => 'magdalena',
]);
pedido_set($validEntrega['data']);
pedido_confirmar('yape');
carrito_agregar(1, 1);

$clienteRenderRoutes = [
    'cliente_interfaces/index.php',
    'cliente_interfaces/catalogo.php',
    'cliente_interfaces/producto.php',
    'cliente_interfaces/pedido.php',
    'cliente_interfaces/confirmacion.php',
];

foreach ($clienteRenderRoutes as $uri) {
    $_SERVER['SCRIPT_NAME'] = '/paginaweb/' . $uri;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    ob_start();
    $disp = $router->dispatch($uri, 'GET');
    $out = (string) ob_get_clean();
    $ok = ($disp['status'] === 200 && strlen($out) > 100);
    if (!$ok) {
        $allOk = false;
    }
    echo sprintf('dispatch_view[%s]=%s status=%d bytes=%d', $uri, $ok ? 'OK' : 'FAIL', $disp['status'], strlen($out)) . PHP_EOL;
}

// Render de admin/login.php sin sesión activa (muestra formulario de login sin redirigir)
$_GET = [];
auth_logout();
$_SERVER['SCRIPT_NAME'] = '/paginaweb/admin/login.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
$dispLogin = $router->dispatch('admin/login.php', 'GET');
$outLogin = (string) ob_get_clean();
$loginOk = ($dispLogin['status'] === 200 && str_contains($outLogin, 'loginForm'));
if (!$loginOk) {
    $allOk = false;
}
echo sprintf('dispatch_view[admin/login.php]=%s status=%d bytes=%d', $loginOk ? 'OK' : 'FAIL', $dispLogin['status'], strlen($outLogin)) . PHP_EOL;

// Render de vistas protegidas admin/* autenticando primero como administrador real
$intentoAdmin = auth_intentar('admin', '123456', 'admin');
if ($intentoAdmin['ok'] && is_array($intentoAdmin['usuario'])) {
    auth_login($intentoAdmin['usuario']);
}

$adminRenderRoutes = [
    'admin/index.php',
    'admin/productos.php',
    'admin/inventario.php',
    'admin/pedidos.php',
];

foreach ($adminRenderRoutes as $uri) {
    $_SERVER['SCRIPT_NAME'] = '/paginaweb/' . $uri;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    ob_start();
    $disp = $router->dispatch($uri, 'GET');
    $out = (string) ob_get_clean();
    $ok = ($disp['status'] === 200 && strlen($out) > 100);
    if (!$ok) {
        $allOk = false;
    }
    echo sprintf('dispatch_view[%s]=%s status=%d bytes=%d', $uri, $ok ? 'OK' : 'FAIL', $disp['status'], strlen($out)) . PHP_EOL;
}

// 4. Verificar manejo de rutas inexistentes (HTTP 404) y métodos no permitidos (HTTP 405)
ob_start();
$res404 = $router->dispatch('cliente_interfaces/ruta_inexistente.php', 'GET');
$out404 = (string) ob_get_clean();
$ok404 = ($res404['status'] === 404 && str_contains($out404, '404 Not Found'));
if (!$ok404) {
    $allOk = false;
}
echo sprintf('route_404=%s status=%d', $ok404 ? 'OK' : 'FAIL', $res404['status']) . PHP_EOL;

ob_start();
$res405 = $router->dispatch('cliente_interfaces/index.php', 'POST');
$out405 = (string) ob_get_clean();
$ok405 = ($res405['status'] === 405 && in_array('GET', $res405['allowed'], true) && str_contains($out405, '405 Method Not Allowed'));
if (!$ok405) {
    $allOk = false;
}
echo sprintf('route_405=%s status=%d allowed=%s', $ok405 ? 'OK' : 'FAIL', $res405['status'], implode(',', $res405['allowed'])) . PHP_EOL;

echo ($allOk ? 'SMOKE_ROUTES_OK' : 'SMOKE_ROUTES_FAIL') . PHP_EOL;
exit($allOk ? 0 : 1);
