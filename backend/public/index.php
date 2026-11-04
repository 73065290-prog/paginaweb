<?php
declare(strict_types=1);

/**
 * Punto de entrada de la API JSON (backend/public/index.php).
 * Inicializa sesión/servicios, estandariza salidas en JSON UTF-8 y delega en ApiCarritoController y ApiStockController.
 */
ini_set('display_errors', '0');

if (PHP_SAPI === 'cli' && !empty($_COOKIE['PHPSESSID']) && session_status() === PHP_SESSION_NONE) {
    session_id((string) $_COOKIE['PHPSESSID']);
}

require_once dirname(__DIR__, 2) . '/includes/init.php';
require_once dirname(__DIR__) . '/controllers/ApiCarritoController.php';
require_once dirname(__DIR__) . '/controllers/ApiStockController.php';

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$rawInput = (string) file_get_contents('php://input');
if ($rawInput === '' && isset($_SERVER['CLI_JSON_BODY'])) {
    $rawInput = (string) $_SERVER['CLI_JSON_BODY'];
}
$jsonBody = [];
if ($rawInput !== '') {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $jsonBody = $decoded;
    }
}

$payload = array_merge($_POST, $jsonBody);
$action = trim((string) ($_GET['action'] ?? $payload['action'] ?? ''));
$headerCsrf = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? (string) $_SERVER['HTTP_X_CSRF_TOKEN'] : null;

$carritoController = new ApiCarritoController();
$stockController = new ApiStockController();

if ($action === '' || $action === 'health' || $action === 'csrf') {
    if ($method !== 'GET' && $method !== 'HEAD') {
        if (!headers_sent()) {
            http_response_code(405);
            header('Allow: GET');
        }
        echo json_encode([
            'success' => false,
            'message' => 'Método HTTP no permitido',
            'errors' => ['Utiliza GET para consultar el estado de la API.'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $estadoCarrito = $carritoController->construirEstadoCarrito();
    if (!headers_sent()) {
        http_response_code(200);
    }
    echo json_encode([
        'app' => 'Mass Online',
        'ok' => true,
        'db' => Database::connected(),
        'db_error' => null,
        'success' => true,
        'message' => 'API Mass Online operativa',
        'data' => [
            'csrf_token' => csrf_token(),
            'carrito' => $estadoCarrito,
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$response = match ($action) {
    'carrito.agregar' => $carritoController->agregar($payload, $method, $headerCsrf),
    'carrito.actualizar' => $carritoController->actualizar($payload, $method, $headerCsrf),
    'carrito.quitar' => $carritoController->quitar($payload, $method, $headerCsrf),
    'stock.consultar' => $stockController->consultar($_GET, $method),
    default => [
        'status' => 404,
        'body' => [
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'errors' => ['La acción solicitada "' . $action . '" no existe en la API.'],
        ],
    ],
};

if (!headers_sent()) {
    http_response_code($response['status']);
    if (!empty($response['allowed']) && is_array($response['allowed'])) {
        header('Allow: ' . implode(', ', $response['allowed']));
    }
}

echo json_encode($response['body'], JSON_UNESCAPED_UNICODE);
