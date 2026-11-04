<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/services/CarritoService.php';
require_once dirname(__DIR__) . '/services/ProductoService.php';

/**
 * Controlador API JSON para operaciones asíncronas del carrito de compras
 * (agregar, actualizar cantidad y quitar productos), protegido con CSRF y respaldado por CarritoService.
 */
final class ApiCarritoController
{
    private CarritoService $carritoService;
    private ProductoService $productoService;

    public function __construct(?CarritoService $carritoService = null, ?ProductoService $productoService = null)
    {
        $this->productoService = $productoService ?? new ProductoService();
        $this->carritoService = $carritoService ?? new CarritoService($this->productoService);
    }

    /**
     * Endpoint POST ?action=carrito.agregar
     * Agrega unidades de un producto al carrito validando CSRF, existencia y disponibilidad de stock.
     *
     * @param array<string, mixed> $payload
     * @return array{status:int, body:array<string, mixed>}
     */
    public function agregar(array $payload, string $method = 'POST', ?string $headerCsrf = null): array
    {
        if (strtoupper($method) !== 'POST') {
            return $this->errorResponse(
                405,
                'Método HTTP no permitido para agregar al carrito',
                ['Utiliza el método POST para esta operación.'],
                ['POST']
            );
        }

        if (!$this->validarCsrf($payload, $headerCsrf)) {
            return $this->errorResponse(
                403,
                'Token CSRF ausente o inválido',
                ['Sesión inválida. Recarga la página e intenta nuevamente.']
            );
        }

        if (!array_key_exists('id', $payload) || $payload['id'] === '' || $payload['id'] === null) {
            return $this->errorResponse(
                400,
                'Datos obligatorios ausentes',
                ['El campo "id" del producto es obligatorio.']
            );
        }

        $id = $this->parseEntero($payload['id']);
        if ($id === null || $id < 1) {
            return $this->errorResponse(
                422,
                'Identificador de producto inválido',
                ['El campo "id" debe ser un número entero positivo.']
            );
        }

        $cantidadRaw = $payload['cantidad'] ?? 1;
        $cantidad = $this->parseEntero($cantidadRaw);
        $max = $this->carritoService->cantidadMaxima();
        if ($cantidad === null || $cantidad < 1 || $cantidad > $max) {
            return $this->errorResponse(
                422,
                'Cantidad solicitada inválida',
                ['La cantidad debe ser un número entero entre 1 y ' . $max . '.']
            );
        }

        $producto = $this->productoService->obtenerPorId($id);
        if ($producto === null) {
            return $this->errorResponse(
                404,
                'Producto no encontrado',
                ['No existe un producto registrado con el identificador ' . $id . '.']
            );
        }

        $stockDisponible = $this->carritoService->stockDisponible($id);
        $cantidadEnCarrito = $this->cantidadActualEnCarrito($id);
        if ($stockDisponible <= 0 || ($cantidadEnCarrito + $cantidad) > $stockDisponible) {
            return $this->errorResponse(
                409,
                'Stock insuficiente para la cantidad solicitada',
                [
                    'Stock disponible: ' . $stockDisponible . ' un. (en carrito: '
                    . $cantidadEnCarrito . ', solicitado adicional: ' . $cantidad . ').',
                ]
            );
        }

        $this->carritoService->agregar($id, $cantidad);

        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'data' => [
                    'producto_id' => $id,
                    'nombre' => (string) $producto['nombre'],
                    'cantidad_agregada' => $cantidad,
                    'cantidad_linea' => $this->cantidadActualEnCarrito($id),
                    'stock_disponible' => $stockDisponible,
                    'carrito' => $this->construirEstadoCarrito(),
                ],
            ],
        ];
    }

    /**
     * Endpoint POST ?action=carrito.actualizar
     * Actualiza la cantidad de una línea existente en el carrito (por "cantidad" exacta o por "delta").
     *
     * @param array<string, mixed> $payload
     * @return array{status:int, body:array<string, mixed>}
     */
    public function actualizar(array $payload, string $method = 'POST', ?string $headerCsrf = null): array
    {
        if (strtoupper($method) !== 'POST') {
            return $this->errorResponse(
                405,
                'Método HTTP no permitido para actualizar el carrito',
                ['Utiliza el método POST para esta operación.'],
                ['POST']
            );
        }

        if (!$this->validarCsrf($payload, $headerCsrf)) {
            return $this->errorResponse(
                403,
                'Token CSRF ausente o inválido',
                ['Sesión inválida. Recarga la página e intenta nuevamente.']
            );
        }

        if (!array_key_exists('id', $payload) || $payload['id'] === '' || $payload['id'] === null) {
            return $this->errorResponse(
                400,
                'Datos obligatorios ausentes',
                ['El campo "id" del producto es obligatorio.']
            );
        }

        $tieneCantidad = array_key_exists('cantidad', $payload) && $payload['cantidad'] !== '' && $payload['cantidad'] !== null;
        $tieneDelta = array_key_exists('delta', $payload) && $payload['delta'] !== '' && $payload['delta'] !== null;
        if (!$tieneCantidad && !$tieneDelta) {
            return $this->errorResponse(
                400,
                'Datos obligatorios ausentes',
                ['Debes enviar el campo "cantidad" o el campo "delta" para actualizar la línea.']
            );
        }

        $id = $this->parseEntero($payload['id']);
        if ($id === null || $id < 1) {
            return $this->errorResponse(
                422,
                'Identificador de producto inválido',
                ['El campo "id" debe ser un número entero positivo.']
            );
        }

        $producto = $this->productoService->obtenerPorId($id);
        if ($producto === null) {
            return $this->errorResponse(
                404,
                'Producto no encontrado',
                ['No existe un producto registrado con el identificador ' . $id . '.']
            );
        }

        $cantidadActual = $this->cantidadActualEnCarrito($id);
        if ($cantidadActual <= 0) {
            return $this->errorResponse(
                404,
                'El producto no se encuentra en el carrito',
                ['No existe una línea activa en el carrito para el producto ' . $id . '.']
            );
        }

        $max = $this->carritoService->cantidadMaxima();
        if ($tieneCantidad) {
            $nuevaCantidad = $this->parseEntero($payload['cantidad']);
            if ($nuevaCantidad === null || $nuevaCantidad < 0 || $nuevaCantidad > $max) {
                return $this->errorResponse(
                    422,
                    'Cantidad inválida',
                    ['La cantidad debe ser un número entero entre 0 y ' . $max . '.']
                );
            }
        } else {
            $delta = $this->parseEntero($payload['delta']);
            if ($delta === null || $delta === 0 || abs($delta) > $max) {
                return $this->errorResponse(
                    422,
                    'Delta de cantidad inválido',
                    ['El ajuste "delta" debe ser un número entero distinto de cero.']
                );
            }
            $nuevaCantidad = max(0, $cantidadActual + $delta);
        }

        $stockDisponible = $this->carritoService->stockDisponible($id);
        if ($nuevaCantidad > $stockDisponible) {
            return $this->errorResponse(
                409,
                'Stock insuficiente para la cantidad solicitada',
                ['Solo hay ' . $stockDisponible . ' unidades disponibles para "' . $producto['nombre'] . '".']
            );
        }

        $this->carritoService->setCantidad($id, $nuevaCantidad);

        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'message' => $nuevaCantidad === 0
                    ? 'Producto eliminado del carrito'
                    : 'Cantidad actualizada correctamente',
                'data' => [
                    'producto_id' => $id,
                    'nombre' => (string) $producto['nombre'],
                    'cantidad_anterior' => $cantidadActual,
                    'cantidad_actual' => $this->cantidadActualEnCarrito($id),
                    'stock_disponible' => $stockDisponible,
                    'carrito' => $this->construirEstadoCarrito(),
                ],
            ],
        ];
    }

    /**
     * Endpoint POST ?action=carrito.quitar
     * Quita un producto existente del carrito activo.
     *
     * @param array<string, mixed> $payload
     * @return array{status:int, body:array<string, mixed>}
     */
    public function quitar(array $payload, string $method = 'POST', ?string $headerCsrf = null): array
    {
        if (strtoupper($method) !== 'POST') {
            return $this->errorResponse(
                405,
                'Método HTTP no permitido para quitar del carrito',
                ['Utiliza el método POST para esta operación.'],
                ['POST']
            );
        }

        if (!$this->validarCsrf($payload, $headerCsrf)) {
            return $this->errorResponse(
                403,
                'Token CSRF ausente o inválido',
                ['Sesión inválida. Recarga la página e intenta nuevamente.']
            );
        }

        if (!array_key_exists('id', $payload) || $payload['id'] === '' || $payload['id'] === null) {
            return $this->errorResponse(
                400,
                'Datos obligatorios ausentes',
                ['El campo "id" del producto es obligatorio para quitarlo del carrito.']
            );
        }

        $id = $this->parseEntero($payload['id']);
        if ($id === null || $id < 1) {
            return $this->errorResponse(
                422,
                'Identificador de producto inválido',
                ['El campo "id" debe ser un número entero positivo.']
            );
        }

        $cantidadActual = $this->cantidadActualEnCarrito($id);
        if ($cantidadActual <= 0) {
            return $this->errorResponse(
                404,
                'El producto no se encuentra en el carrito',
                ['El producto con identificador ' . $id . ' no está en el carrito de la sesión actual.']
            );
        }

        $this->carritoService->eliminar($id);

        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'data' => [
                    'producto_id' => $id,
                    'cantidad_eliminada' => $cantidadActual,
                    'carrito' => $this->construirEstadoCarrito(),
                ],
            ],
        ];
    }

    /**
     * Construye el resumen estructurado del carrito activo para respuestas JSON.
     *
     * @return array{
     *   items:array<int, array<string, mixed>>,
     *   total_unidades:int,
     *   total_monto:float,
     *   total_formateado:string
     * }
     */
    public function construirEstadoCarrito(): array
    {
        $itemsDetalle = [];
        foreach ($this->carritoService->items() as $item) {
            $id = (int) ($item['id'] ?? 0);
            $cant = (int) ($item['cantidad'] ?? 0);
            $p = $this->productoService->obtenerPorId($id);
            if ($p === null || $cant <= 0) {
                continue;
            }
            $precio = (float) $p['precio'];
            $subtotal = round($precio * $cant, 2);
            $itemsDetalle[] = [
                'id' => $id,
                'nombre' => (string) $p['nombre'],
                'img' => (string) ($p['img'] ?? ''),
                'precio' => $precio,
                'precio_formateado' => dinero($precio),
                'cantidad' => $cant,
                'subtotal' => $subtotal,
                'subtotal_formateado' => dinero($subtotal),
            ];
        }

        $totalUnidades = $this->carritoService->totalUnidades();
        $totalMonto = round($this->carritoService->totalMonto(), 2);

        return [
            'items' => $itemsDetalle,
            'total_unidades' => $totalUnidades,
            'total_monto' => $totalMonto,
            'total_formateado' => dinero($totalMonto),
        ];
    }

    /**
     * Obtiene la cantidad actual de un producto específico dentro del carrito en sesión.
     */
    private function cantidadActualEnCarrito(int $id): int
    {
        foreach ($this->carritoService->items() as $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                return (int) ($item['cantidad'] ?? 0);
            }
        }
        return 0;
    }

    /**
     * Valida el token CSRF enviado en el cuerpo de la petición o en la cabecera X-CSRF-Token.
     *
     * @param array<string, mixed> $payload
     */
    private function validarCsrf(array $payload, ?string $headerCsrf): bool
    {
        $token = null;
        if (isset($payload['csrf_token']) && is_string($payload['csrf_token'])) {
            $token = $payload['csrf_token'];
        } elseif ($headerCsrf !== null && $headerCsrf !== '') {
            $token = $headerCsrf;
        }

        return function_exists('csrf_validar') && csrf_validar($token);
    }

    /**
     * Convierte un valor escalar a entero estricto o retorna null si no representa un entero válido.
     */
    private function parseEntero(mixed $val): ?int
    {
        if (is_int($val)) {
            return $val;
        }
        if (is_string($val)) {
            $trim = trim($val);
            if ($trim !== '' && preg_match('/^[+-]?\d+$/', $trim) === 1) {
                return (int) $trim;
            }
        }
        return null;
    }

    /**
     * Construye una respuesta de error estándar para la API.
     *
     * @param array<int, string> $errors
     * @param array<int, string> $allowed
     * @return array{status:int, allowed?:array<int,string>, body:array{success:bool, message:string, errors:array<int,string>}}
     */
    private function errorResponse(int $status, string $message, array $errors, array $allowed = []): array
    {
        $res = [
            'status' => $status,
            'body' => [
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ],
        ];
        if ($allowed !== []) {
            $res['allowed'] = $allowed;
        }
        return $res;
    }
}

