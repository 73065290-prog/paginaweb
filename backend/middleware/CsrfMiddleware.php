<?php
declare(strict_types=1);

final class CsrfMiddleware
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        $t = htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $t . '" />';
    }

    public static function validate(?string $token): bool
    {
        $session = $_SESSION['_csrf'] ?? '';
        return is_string($session) && $session !== '' && is_string($token) && hash_equals($session, $token);
    }

    public static function requireValid(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!self::validate(is_string($token) ? $token : null)) {
            http_response_code(403);
            exit('CSRF token inválido.');
        }
    }
}
