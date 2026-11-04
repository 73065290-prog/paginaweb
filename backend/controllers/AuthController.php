<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/services/AuthService.php';

/**
 * Controlador de entrada para los flujos de inicio de sesión, cierre de sesión y cambio de rol.
 */
final class AuthController
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    /**
     * Punto de entrada para procesar el inicio de sesión (POST).
     * Valida CSRF (cuando se envía desde formulario HTTP), cierra cualquier sesión previa,
     * valida credenciales mediante AuthService e inicia la nueva sesión.
     *
     * @param array<string, mixed> $input
     * @return array{
     *   ok:bool,
     *   error:string,
     *   errores:array<int,string>,
     *   redirect:?string,
     *   mensaje_flash:?string,
     *   usuario:?array<string,mixed>,
     *   usuario_input:string,
     *   rol:string,
     *   recordar:bool
     * }
     */
    public function login(array $input, ?string $rolOverride = null, bool $validarCsrf = true): array
    {
        $rolRaw = $rolOverride ?? (string) ($input['rol'] ?? AuthService::ROL_ADMIN);
        $rol = $this->authService->normalizarRol($rolRaw);
        $usuarioInput = (string) ($input['usuario'] ?? '');
        $passwordInput = (string) ($input['password'] ?? '');
        $recordar = !empty($input['recordar']);

        if ($validarCsrf) {
            $token = isset($input['csrf_token']) ? (string) $input['csrf_token'] : null;
            $csrfOk = function_exists('csrf_validar') ? csrf_validar($token) : false;
            if (!$csrfOk) {
                $msg = 'Sesión inválida. Recarga la página e intenta de nuevo.';
                return [
                    'ok' => false,
                    'error' => $msg,
                    'errores' => [$msg],
                    'redirect' => null,
                    'mensaje_flash' => null,
                    'usuario' => null,
                    'usuario_input' => $usuarioInput,
                    'rol' => $rol,
                    'recordar' => $recordar,
                ];
            }
        }

        // Al enviar login siempre reemplaza la sesión previa (cambio de administrador/cliente)
        $this->authService->logout();

        $resultado = $this->authService->intentar($usuarioInput, $passwordInput, $rol);
        if ($resultado['ok'] && is_array($resultado['usuario'])) {
            $this->authService->login($resultado['usuario'], $recordar);
            $flash = 'Bienvenido, ' . (string) ($resultado['usuario']['nombre'] ?? '');
            if (function_exists('flash_set')) {
                flash_set($flash);
            } else {
                $_SESSION['flash'] = $flash;
            }
            $redirect = $this->authService->loginDestino((string) ($resultado['usuario']['rol'] ?? $rol));

            return [
                'ok' => true,
                'error' => '',
                'errores' => [],
                'redirect' => $redirect,
                'mensaje_flash' => $flash,
                'usuario' => $resultado['usuario'],
                'usuario_input' => $usuarioInput,
                'rol' => $rol,
                'recordar' => $recordar,
            ];
        }

        $errores = $resultado['errores'];
        return [
            'ok' => false,
            'error' => implode(' · ', $errores),
            'errores' => $errores,
            'redirect' => null,
            'mensaje_flash' => null,
            'usuario' => null,
            'usuario_input' => $usuarioInput,
            'rol' => $rol,
            'recordar' => $recordar,
        ];
    }

    /**
     * Punto de entrada para cerrar la sesión activa y determinar la ruta de retorno según el rol previo.
     *
     * @return array{ok:bool, era_admin:bool, redirect:string, mensaje_flash:string}
     */
    public function logout(): array
    {
        $eraAdmin = $this->authService->esAdmin();
        $this->authService->logout();

        $flash = 'Sesión cerrada correctamente';
        if (function_exists('flash_set')) {
            flash_set($flash);
        } else {
            $_SESSION['auth_flash'] = $flash;
            $_SESSION['flash'] = $flash;
        }

        $redirect = $this->authService->logoutDestino($eraAdmin);

        return [
            'ok' => true,
            'era_admin' => $eraAdmin,
            'redirect' => $redirect,
            'mensaje_flash' => $flash,
        ];
    }

    /**
     * Punto de entrada para gestionar el cambio de rol entre administrador y cliente:
     * - Si solo se indica $rolSolicitado (acceso GET a login.php), cierra la sesión anterior si el rol difiere
     *   o indica redirección si ya tiene sesión activa en ese mismo rol.
     * - Si se proporcionan credenciales ($usuario y $password), cierra la sesión anterior y autentica
     *   bajo el nuevo rol solicitado.
     *
     * @return array{
     *   ok:bool,
     *   accion:string,
     *   redirect:?string,
     *   mensaje:?string,
     *   errores:array<int,string>,
     *   usuario:?array<string,mixed>,
     *   rol:string
     * }
     */
    public function cambioRol(
        string $rolSolicitado,
        ?string $usuario = null,
        ?string $password = null,
        bool $recordar = false
    ): array {
        $rol = $this->authService->normalizarRol($rolSolicitado);

        if ($usuario === null && $password === null) {
            $prep = $this->authService->prepararCambioRol($rol);
            return [
                'ok' => true,
                'accion' => $prep['accion'],
                'redirect' => $prep['redirect'],
                'mensaje' => $prep['mensaje'],
                'errores' => [],
                'usuario' => $this->authService->usuarioActual(),
                'rol' => $prep['rol'],
            ];
        }

        // Cambio directo con credenciales: cierra siempre la sesión anterior antes de autenticar el nuevo rol
        $this->authService->logout();
        $intento = $this->authService->intentar((string) $usuario, (string) $password, $rol);
        if (!$intento['ok'] || !is_array($intento['usuario'])) {
            return [
                'ok' => false,
                'accion' => 'error',
                'redirect' => null,
                'mensaje' => implode(' · ', $intento['errores']),
                'errores' => $intento['errores'],
                'usuario' => null,
                'rol' => $rol,
            ];
        }

        $this->authService->login($intento['usuario'], $recordar);
        $flash = 'Bienvenido, ' . (string) ($intento['usuario']['nombre'] ?? '');
        if (function_exists('flash_set')) {
            flash_set($flash);
        } else {
            $_SESSION['flash'] = $flash;
        }

        return [
            'ok' => true,
            'accion' => 'autenticado',
            'redirect' => $this->authService->loginDestino($rol),
            'mensaje' => $flash,
            'errores' => [],
            'usuario' => $intento['usuario'],
            'rol' => $rol,
        ];
    }

    /**
     * Acción de ruta para renderizar y procesar la vista de login (admin/login.php).
     */
    public function loginView(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/admin/login.php';
    }

    /**
     * Acción de ruta para ejecutar el cierre de sesión desde el directorio admin (admin/logout.php).
     */
    public function logoutAdminView(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/admin/logout.php';
    }

    /**
     * Acción de ruta para ejecutar el cierre de sesión desde tienda (cliente_interfaces/logout.php).
     */
    public function logoutClienteView(): void
    {
        global $flash;
        require MASS_ROOT . '/frontend/views/cliente/logout.php';
    }
}
