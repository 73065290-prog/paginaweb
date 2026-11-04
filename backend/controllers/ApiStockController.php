<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/services/CarritoService.php';
require_once dirname(__DIR__) . '/services/ProductoService.php';

/**
 * Controlador API JSON para consulta de stock disponible por producto (solo lectura).
 */
final class ApiStockController
{
    private ProductoService $productoService;
    private CarritoService $carritoService;

    public function __construct(?ProductoService $productoService = null, ?CarritoService $carritoService = null)
    {
        $this->productoService = $productoService ?? new ProductoService();
        $this->carritoService = $carritoService ?? new CarritoService($this->productoService);
    }

    /**
     * Endpoint GET ?action=stock.consultar&id=<id>
     * Consulta la disponibilidad de stock de un producto sin modificar el carrito ni el inventario.
     *
     * @param array<string, mixed> $query
     * @return array{status:int, allowed?:array<int,string>, body:array<string, mixed>}
     */
    public function consultar(array $query, string $method = 'GET'): array
    {
        $httpMethod = strtoupper($method);
        if ($httpMethod === 'HEAD') {
            $httpMethod = 'GET';
        }
        if ($httpMethod !== 'GET') {
            return [
                'status' => 405,
                'allowed' => ['GET'],
                'body' => [
                    'success' => false,
                    'message' => 'Método HTTP no permitido para consultar stock',
                    'errors' => ['Utiliza el método GET para consultar el stock de un producto.'],
                ],
            ];
        }

        if (!array_key_exists('id', $query) || $query['id'] === '' || $query['id'] === null) {
            return [
                'status' => 400,
                'body' => [
                    'success' => false,
                    'message' => 'Datos obligatorios ausentes',
                    'errors' => ['El parámetro "id" del producto es obligatorio.'],
                ],
            ];
        }

        $id = $this->parseEntero($query['id']);
        if ($id === null || $id < 1) {
            return [
                'status' => 422,
                'body' => [
                    'success' => false,
                    'message' => 'Identificador de producto inválido',
                    'errors' => ['El parámetro "id" debe ser un número entero positivo.'],
                ],
            ];
        }

        $producto = $this->productoService->obtenerPorId($id);
        if ($producto === null) {
            return [
                'status' => 404,
                'body' => [
                    'success' => false,
                    'message' => 'Producto no encontrado',
                    'errors' => ['No existe un producto registrado con el identificador ' . $id . '.'],
                ],
            ];
        }

        $stockDisponible = $this->carritoService->stockDisponible($id);
        $stockTone = (string) ($producto['stockTone'] ?? ($stockDisponible > 10 ? 'ok' : 'bajo'));
        $stockLabel = (string) ($producto['stockLabel'] ?? ($stockDisponible > 0 ? 'Disponible' : 'Agotado'));

        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'message' => 'Stock consultado correctamente',
                'data' => [
                    'id' => $id,
                    'nombre' => (string) $producto['nombre'],
                    'marca' => (string) ($producto['marca'] ?? ''),
                    'categoria' => (string) ($producto['categoria'] ?? ''),
                    'precio' => (float) $producto['precio'],
                    'precio_formateado' => dinero((float) $producto['precio']),
                    'stock_disponible' => $stockDisponible,
                    'stock_estado' => $stockDisponible > 0 ? $stockTone : 'agotado',
                    'stock_etiqueta' => $stockLabel,
                    'disponible' => $stockDisponible > 0,
                    'max_por_linea' => $this->carritoService->cantidadMaxima(),
                ],
            ],
        ];
    }

    /**
     * Convierte un valor escalar a entero estricto o retorna null.
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
}

