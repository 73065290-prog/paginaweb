<?php
declare(strict_types=1);

/**
 * Router HTTP mínimo en PHP nativo (sin dependencias externas).
 * Registra rutas reales (URL + métodos HTTP -> Controller::acción -> vista),
 * resuelve solicitudes y maneja respuestas 404 (ruta inexistente) y 405 (método no permitido).
 */
final class Router
{
    /**
     * @var array<string, array{
     *   uri:string,
     *   name:string,
     *   methods:array<int,string>,
     *   controller:class-string,
     *   action:string,
     *   view:string
     * }>
     */
    private array $routesByUri = [];

    /**
     * @var array<string, string>
     */
    private array $nameToUri = [];

    /**
     * Registra una ruta asociando métodos HTTP, URL pública, controlador, acción y vista.
     *
     * @param array<int, string>|string $methods
     * @param class-string $controller
     */
    public function register(
        array|string $methods,
        string $uri,
        string $controller,
        string $action,
        string $name = '',
        string $view = ''
    ): self {
        $normalizedUri = $this->normalizePath($uri);
        $methodList = is_array($methods) ? $methods : [$methods];
        $upperMethods = array_values(array_unique(array_map(
            static fn (string $m): string => strtoupper(trim($m)),
            $methodList
        )));

        if (isset($this->routesByUri[$normalizedUri])) {
            $upperMethods = array_values(array_unique(array_merge(
                $this->routesByUri[$normalizedUri]['methods'],
                $upperMethods
            )));
        }

        $routeName = $name !== '' ? $name : $normalizedUri;
        $this->routesByUri[$normalizedUri] = [
            'uri' => $normalizedUri,
            'name' => $routeName,
            'methods' => $upperMethods,
            'controller' => $controller,
            'action' => $action,
            'view' => $view,
        ];

        if ($name !== '') {
            $this->nameToUri[$name] = $normalizedUri;
        }

        return $this;
    }

    /**
     * Registra una ruta para solicitudes GET.
     *
     * @param class-string $controller
     */
    public function get(string $uri, string $controller, string $action, string $name = '', string $view = ''): self
    {
        return $this->register(['GET'], $uri, $controller, $action, $name, $view);
    }

    /**
     * Registra una ruta para solicitudes POST.
     *
     * @param class-string $controller
     */
    public function post(string $uri, string $controller, string $action, string $name = '', string $view = ''): self
    {
        return $this->register(['POST'], $uri, $controller, $action, $name, $view);
    }

    /**
     * Registra una ruta para múltiples métodos HTTP (por ejemplo GET y POST).
     *
     * @param array<int, string> $methods
     * @param class-string $controller
     */
    public function match(
        array $methods,
        string $uri,
        string $controller,
        string $action,
        string $name = '',
        string $view = ''
    ): self {
        return $this->register($methods, $uri, $controller, $action, $name, $view);
    }

    /**
     * Normaliza una ruta o SCRIPT_NAME a su forma relativa canónica
     * (ej. 'cliente_interfaces/index.php' o 'admin/login.php').
     */
    public function normalizePath(string $rawPath): string
    {
        $clean = trim(str_replace('\\', '/', $rawPath));
        $qPos = strpos($clean, '?');
        if ($qPos !== false) {
            $clean = substr($clean, 0, $qPos);
        }
        $clean = ltrim($clean, '/');

        if (isset($this->nameToUri[$clean])) {
            return $this->nameToUri[$clean];
        }

        if (preg_match('#((?:admin|cliente_interfaces)/[A-Za-z0-9_.\-]+\.php)$#', $clean, $m)) {
            return $m[1];
        }

        return $clean;
    }

    /**
     * Resuelve una ruta y método HTTP sin ejecutar el controlador.
     *
     * @return array{
     *   status:int,
     *   route:?array{uri:string, name:string, methods:array<int,string>, controller:class-string, action:string, view:string},
     *   allowed:array<int,string>
     * }
     */
    public function resolve(string $path, string $method = 'GET'): array
    {
        $normalized = $this->normalizePath($path);
        $httpMethod = strtoupper(trim($method));
        if ($httpMethod === 'HEAD') {
            $httpMethod = 'GET';
        }

        if (!isset($this->routesByUri[$normalized])) {
            return [
                'status' => 404,
                'route' => null,
                'allowed' => [],
            ];
        }

        $route = $this->routesByUri[$normalized];
        if (!in_array($httpMethod, $route['methods'], true)) {
            return [
                'status' => 405,
                'route' => null,
                'allowed' => $route['methods'],
            ];
        }

        return [
            'status' => 200,
            'route' => $route,
            'allowed' => $route['methods'],
        ];
    }

    /**
     * Despacha la solicitud actual (o la ruta/método indicados) ejecutando el controlador correspondiente.
     * Maneja rutas no registradas con HTTP 404 y métodos no permitidos con HTTP 405.
     *
     * @return array{status:int, route:?array<string,mixed>, allowed:array<int,string>, error:?string}
     */
    public function dispatch(?string $path = null, ?string $method = null): array
    {
        $targetPath = $path ?? (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $targetMethod = $method ?? (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $resolved = $this->resolve($targetPath, $targetMethod);

        if ($resolved['status'] === 404) {
            if (!headers_sent()) {
                http_response_code(404);
            }
            $msg = '404 Not Found: Ruta no registrada (' . htmlspecialchars($this->normalizePath($targetPath), ENT_QUOTES, 'UTF-8') . ')';
            echo $msg;
            return [
                'status' => 404,
                'route' => null,
                'allowed' => [],
                'error' => $msg,
            ];
        }

        if ($resolved['status'] === 405) {
            if (!headers_sent()) {
                http_response_code(405);
                header('Allow: ' . implode(', ', $resolved['allowed']));
            }
            $msg = '405 Method Not Allowed: Método ' . htmlspecialchars(strtoupper($targetMethod), ENT_QUOTES, 'UTF-8')
                . ' no permitido. Permitidos: ' . implode(', ', $resolved['allowed']);
            echo $msg;
            return [
                'status' => 405,
                'route' => null,
                'allowed' => $resolved['allowed'],
                'error' => $msg,
            ];
        }

        $route = $resolved['route'];
        $controllerClass = $route['controller'];
        $action = $route['action'];

        if (!class_exists($controllerClass)) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            $msg = '500 Internal Server Error: Controlador no encontrado (' . $controllerClass . ')';
            echo $msg;
            return [
                'status' => 500,
                'route' => $route,
                'allowed' => $resolved['allowed'],
                'error' => $msg,
            ];
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $action)) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            $msg = '500 Internal Server Error: Acción no encontrada (' . $controllerClass . '::' . $action . ')';
            echo $msg;
            return [
                'status' => 500,
                'route' => $route,
                'allowed' => $resolved['allowed'],
                'error' => $msg,
            ];
        }

        $controller->{$action}();

        return [
            'status' => 200,
            'route' => $route,
            'allowed' => $resolved['allowed'],
            'error' => null,
        ];
    }

    /**
     * Devuelve todas las rutas registradas indexadas por su URI normalizada.
     *
     * @return array<string, array{
     *   uri:string,
     *   name:string,
     *   methods:array<int,string>,
     *   controller:class-string,
     *   action:string,
     *   view:string
     * }>
     */
    public function routes(): array
    {
        return $this->routesByUri;
    }

    /**
     * Devuelve el mapa de nombres lógicos a rutas de vista (compatibilidad con mapa de referencia).
     *
     * @return array<string, string>
     */
    public function viewMap(): array
    {
        $map = [];
        foreach ($this->routesByUri as $route) {
            $map[$route['name']] = $route['view'];
        }
        return $map;
    }
}

