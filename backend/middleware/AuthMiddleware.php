<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/services/AuthService.php';

/**
 * Middleware centralizado de autenticación y autorización por roles (admin / cliente).
 * Respaldado por AuthService y consumido por las vistas administrativas, helpers de sesión y flujos protegidos.
 */
final class AuthMiddleware
{
    private static ?AuthService $authService = null;

    /**
     * Obtiene la instancia compartida de AuthService.
     */
    private static function service(): AuthService
    {
        if (self::$authService === null) {
            self::$authService = new AuthService();
        }
        return self::$authService;
    }

    /**
     * Devuelve los datos del usuario autenticado en la sesión activa o null si no existe sesión.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        return self::service()->usuarioActual();
    }

    /**
     * Comprueba si existe una sesión de usuario autenticada (cualquier rol).
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Comprueba si el usuario autenticado posee el rol de administrador ('admin').
     */
    public static function isAdmin(): bool
    {
        return self::service()->esAdmin();
    }

    /**
     * Comprueba si el usuario autenticado posee el rol de cliente ('cliente').
     */
    public static function isCliente(): bool
    {
        return self::service()->esCliente();
    }

    /**
     * Protege las rutas y vistas administrativas: si no hay sesión admin activa,
     * registra el mensaje flash y redirige al login administrativo.
     */
    public static function requireAdmin(
        string $loginUrl = 'login.php',
        string $mensaje = 'Inicia sesión como Administrador WMS'
    ): void {
        if (!self::isAdmin()) {
            if (function_exists('flash_set')) {
                flash_set($mensaje);
            } else {
                $_SESSION['flash'] = $mensaje;
            }
            if (function_exists('redirect')) {
                redirect($loginUrl);
            }
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Protege las rutas exclusivas de clientes autenticados: si no hay sesión cliente activa,
     * registra el mensaje flash y redirige al login en modo cliente.
     */
    public static function requireCliente(
        string $loginUrl = '../admin/login.php?rol=cliente',
        string $mensaje = 'Inicia sesión como Cliente'
    ): void {
        if (!self::isCliente()) {
            if (function_exists('flash_set')) {
                flash_set($mensaje);
            } else {
                $_SESSION['flash'] = $mensaje;
            }
            if (function_exists('redirect')) {
                redirect($loginUrl);
            }
            header('Location: ' . $loginUrl);
            exit;
        }
    }
}
