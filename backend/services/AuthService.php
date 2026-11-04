<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/repositories/UsuarioRepository.php';

/**
 * Servicio de dominio para autenticación, gestión segura de sesión y cambio de rol.
 */
final class AuthService
{
    public const ROL_ADMIN = 'admin';
    public const ROL_CLIENTE = 'cliente';

    /**
     * Hash bcrypt de la contraseña demo académica (login: 123456).
     * No se almacena el secreto en texto plano (higiene SonarQube S2068).
     */
    private const DEMO_PASSWORD_HASH = '$2y$10$MWrE46ay48vQzn7hxxu7bekEQyTK8k1DMfvBt9L3WrBtkmaiQPOVG';

    private UsuarioRepository $usuarioRepository;

    public function __construct(?UsuarioRepository $usuarioRepository = null)
    {
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
    }

    /**
     * Devuelve el hash bcrypt de la contraseña demo académica.
     */
    public function demoPasswordHash(): string
    {
        return self::DEMO_PASSWORD_HASH;
    }

    /**
     * Normaliza el rol solicitado: solo permite 'admin' o 'cliente'.
     */
    public function normalizarRol(string $rol): string
    {
        return $rol === self::ROL_CLIENTE ? self::ROL_CLIENTE : self::ROL_ADMIN;
    }

    /**
     * Devuelve el catálogo de usuarios demo SGC-Mass (Figma 30-9006).
     *
     * @return array<int, array{usuario:string, password_hash:string, rol:string, nombre:string, cargo:string}>
     */
    public function usuariosDemo(): array
    {
        $hash = $this->demoPasswordHash();

        return [
            [
                'usuario' => 'admin.operaciones@mass.pe',
                'password_hash' => $hash,
                'rol' => self::ROL_ADMIN,
                'nombre' => 'Gonzalo Méndez',
                'cargo' => 'Administrador de Tienda',
            ],
            [
                'usuario' => 'admin',
                'password_hash' => $hash,
                'rol' => self::ROL_ADMIN,
                'nombre' => 'Gonzalo Méndez',
                'cargo' => 'Administrador WMS',
            ],
            [
                'usuario' => 'proveedor@mass.pe',
                'password_hash' => $hash,
                'rol' => self::ROL_CLIENTE,
                'nombre' => 'Ana Proveedor',
                'cargo' => 'Cliente / Proveedor Pedidos',
            ],
            [
                'usuario' => 'cliente',
                'password_hash' => $hash,
                'rol' => self::ROL_CLIENTE,
                'nombre' => 'Cliente Demo',
                'cargo' => 'Portal Pedidos',
            ],
        ];
    }

    /**
     * Obtiene los usuarios habilitados para autenticación, priorizando MySQL mediante
     * UsuarioRepository y cayendo en usuarios demo cuando no hay conexión activa.
     *
     * @return array<int, array{usuario:string, password_hash:string, rol:string, nombre:string, cargo:string}>
     */
    public function usuariosAuth(): array
    {
        $dbUsers = $this->usuarioRepository->allAuthUsers();
        if ($dbUsers !== []) {
            return $dbUsers;
        }

        return $this->usuariosDemo();
    }

    /**
     * Valida credenciales y rol esperado contra UsuarioRepository / usuarios demo.
     *
     * @return array{ok:bool, errores:array<int,string>, usuario:?array<string,mixed>}
     */
    public function intentar(string $usuario, string $password, string $rolEsperado = self::ROL_ADMIN): array
    {
        $usuarioLimpio = mb_strtolower(trim($usuario));
        $passwordLimpio = trim($password);
        $rolEsperado = $this->normalizarRol($rolEsperado);
        $errores = [];

        if ($usuarioLimpio === '') {
            $errores[] = 'Ingresa tu correo o usuario corporativo';
        }
        if ($passwordLimpio === '') {
            $errores[] = 'Ingresa tu contraseña';
        }
        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores, 'usuario' => null];
        }

        $candidato = $this->usuarioRepository->findByIdentifier($usuarioLimpio, $this->usuariosAuth());
        if ($candidato !== null) {
            $hash = (string) ($candidato['password_hash'] ?? '');
            if ($hash !== '' && password_verify($passwordLimpio, $hash)) {
                if (($candidato['rol'] ?? '') !== $rolEsperado) {
                    return [
                        'ok' => false,
                        'errores' => ['Este usuario no corresponde al modo seleccionado (' . $rolEsperado . ')'],
                        'usuario' => null,
                    ];
                }

                unset($candidato['password_hash']);

                return ['ok' => true, 'errores' => [], 'usuario' => $candidato];
            }
        }

        return ['ok' => false, 'errores' => ['Credenciales incorrectas'], 'usuario' => null];
    }

    /**
     * Alias compatible con la firma previa de AuthService::attempt().
     *
     * @return array{ok:bool, errores:array<int,string>, usuario:?array<string,mixed>}
     */
    public function attempt(string $usuario, string $password, string $rol = self::ROL_ADMIN): array
    {
        return $this->intentar($usuario, $password, $rol);
    }

    /**
     * Inicia la sesión del usuario autenticado regenerando el ID de sesión para prevenir fijación.
     *
     * @param array<string, mixed> $usuario
     */
    public function login(array $usuario, bool $recordar = false): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['auth'] = [
            'usuario' => (string) ($usuario['usuario'] ?? ''),
            'rol' => (string) ($usuario['rol'] ?? ''),
            'nombre' => (string) ($usuario['nombre'] ?? ''),
            'cargo' => (string) ($usuario['cargo'] ?? ''),
            'login_at' => date('c'),
            'recordar' => $recordar,
        ];
    }

    /**
     * Cierra la sesión de autenticación activa limpiando $_SESSION['auth'].
     */
    public function logout(): void
    {
        unset($_SESSION['auth']);
    }

    /**
     * Devuelve la ruta de redirección tras cerrar sesión según si el usuario activo era administrador.
     */
    public function logoutDestino(bool $eraAdmin): string
    {
        return $eraAdmin ? 'admin/login.php' : 'cliente_interfaces/index.php';
    }

    /**
     * Devuelve la ruta de redirección posterior a un inicio de sesión exitoso según el rol.
     */
    public function loginDestino(string $rol): string
    {
        return $this->normalizarRol($rol) === self::ROL_CLIENTE
            ? '../cliente_interfaces/pedido.php?demo=1'
            : 'index.php';
    }

    /**
     * Obtiene los datos del usuario autenticado en la sesión actual o null si no hay sesión.
     *
     * @return array<string, mixed>|null
     */
    public function usuarioActual(): ?array
    {
        $a = $_SESSION['auth'] ?? null;
        return is_array($a) ? $a : null;
    }

    /**
     * Indica si existe una sesión activa con rol de administrador.
     */
    public function esAdmin(): bool
    {
        $u = $this->usuarioActual();
        return $u !== null && ($u['rol'] ?? '') === self::ROL_ADMIN;
    }

    /**
     * Indica si existe una sesión activa con rol de cliente.
     */
    public function esCliente(): bool
    {
        $u = $this->usuarioActual();
        return $u !== null && ($u['rol'] ?? '') === self::ROL_CLIENTE;
    }

    /**
     * Evalúa el estado de sesión ante una solicitud de acceso/cambio de rol en la vista de login:
     * - Si no hay sesión activa: permite mostrar el formulario.
     * - Si la sesión activa ya pertenece al rol solicitado: indica redirección a su panel.
     * - Si la sesión activa pertenece a otro rol: cierra la sesión anterior y devuelve el mensaje de aviso.
     *
     * @return array{accion:string, redirect:?string, mensaje:?string, rol:string}
     */
    public function prepararCambioRol(string $rolSolicitado): array
    {
        $rol = $this->normalizarRol($rolSolicitado);
        if ($this->usuarioActual() === null) {
            return [
                'accion' => 'mostrar',
                'redirect' => null,
                'mensaje' => null,
                'rol' => $rol,
            ];
        }

        if ($rol === self::ROL_ADMIN && $this->esAdmin()) {
            return [
                'accion' => 'redirect',
                'redirect' => 'index.php',
                'mensaje' => null,
                'rol' => $rol,
            ];
        }

        if ($rol === self::ROL_CLIENTE && $this->esCliente()) {
            return [
                'accion' => 'redirect',
                'redirect' => '../cliente_interfaces/index.php',
                'mensaje' => null,
                'rol' => $rol,
            ];
        }

        // Cambio admin <-> cliente: cerrar sesión actual antes de mostrar o establecer nueva sesión
        $this->logout();

        return [
            'accion' => 'cambio_rol',
            'redirect' => null,
            'mensaje' => 'Sesión anterior cerrada. Inicia sesión con el rol seleccionado.',
            'rol' => $rol,
        ];
    }
}
