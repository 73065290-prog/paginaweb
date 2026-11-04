<?php
declare(strict_types=1);

/**
 * Middleware centralizado para generación, renderizado y validación de tokens CSRF por sesión.
 * Utilizado por formularios del storefront, checkout, panel administrativo WMS y endpoints JSON.
 */
final class CsrfMiddleware
{
    public const SESSION_KEY = 'csrf_token';
    public const FIELD_NAME = 'csrf_token';

    /**
     * Obtiene o genera el token CSRF criptográficamente seguro de la sesión activa (64 caracteres hex).
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Genera el campo HTML oculto con el token CSRF actual para formularios POST.
     */
    public static function field(): string
    {
        $t = htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . $t . '" />';
    }

    /**
     * Valida en tiempo constante (hash_equals) un token recibido frente al token almacenado en sesión.
     */
    public static function validate(?string $token): bool
    {
        $esperado = $_SESSION[self::SESSION_KEY] ?? '';
        return is_string($esperado)
            && $esperado !== ''
            && is_string($token)
            && hash_equals($esperado, $token);
    }

    /**
     * Extrae el token CSRF desde un payload (POST/JSON) o desde la cabecera HTTP X-CSRF-Token.
     *
     * @param array<string, mixed>|null $payload
     */
    public static function extractToken(?array $payload = null, ?string $headerToken = null): ?string
    {
        $source = $payload ?? $_POST;
        if (isset($source[self::FIELD_NAME]) && is_string($source[self::FIELD_NAME])) {
            return $source[self::FIELD_NAME];
        }
        if (isset($source['_csrf']) && is_string($source['_csrf'])) {
            return $source['_csrf'];
        }
        $header = $headerToken ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (is_string($header) && $header !== '') {
            return $header;
        }
        return null;
    }

    /**
     * Valida la solicitud actual extrayendo el token de POST/JSON o de la cabecera X-CSRF-Token.
     *
     * @param array<string, mixed>|null $payload
     */
    public static function validateRequest(?array $payload = null, ?string $headerToken = null): bool
    {
        return self::validate(self::extractToken($payload, $headerToken));
    }

    /**
     * Exige un token CSRF válido en la solicitud actual; responde con HTTP 403 si es inválido o ausente.
     *
     * @param array<string, mixed>|null $payload
     */
    public static function requireValid(?array $payload = null, ?string $headerToken = null): void
    {
        if (!self::validateRequest($payload, $headerToken)) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            exit('CSRF token inválido.');
        }
    }
}
