<?php
declare(strict_types=1);

final class AuthMiddleware
{
    public static function requireAdmin(): void
    {
        if (!function_exists('auth_es_admin') || !auth_es_admin()) {
            if (function_exists('redirect')) {
                redirect('login.php');
            }
            header('Location: login.php');
            exit;
        }
    }

    public static function requireCliente(): void
    {
        if (!function_exists('auth_es_cliente') || !auth_es_cliente()) {
            header('Location: ../admin/login.php?rol=cliente');
            exit;
        }
    }
}
