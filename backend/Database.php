<?php
declare(strict_types=1);

/**
 * Conexión PDO singleton. Si falla, la app usa datos demo en sesión/arrays.
 */
final class Database
{
    private static ?PDO $pdo = null;
    private static bool $tried = false;
    private static ?string $lastError = null;

    public static function config(): array
    {
        $local = __DIR__ . '/config/database.local.php';
        $example = __DIR__ . '/config/database.example.php';
        if (is_file($local)) {
            /** @var array $cfg */
            $cfg = require $local;
            return $cfg;
        }
        if (is_file($example)) {
            /** @var array $cfg */
            $cfg = require $example;
            // Sin archivo local no forzamos conexión automática.
            $cfg['enabled'] = false;
            return $cfg;
        }
        return ['enabled' => false];
    }

    public static function shouldUseDb(): bool
    {
        $env = getenv('MASS_USE_DB');
        if ($env === '0' || $env === 'false') {
            return false;
        }
        if ($env === '1' || $env === 'true') {
            return true;
        }
        $cfg = self::config();
        return !empty($cfg['enabled']);
    }

    public static function pdo(): ?PDO
    {
        if (!self::shouldUseDb()) {
            return null;
        }
        if (self::$tried) {
            return self::$pdo;
        }
        self::$tried = true;
        $cfg = self::config();
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'] ?? '127.0.0.1',
                (int) ($cfg['port'] ?? 3306),
                $cfg['dbname'] ?? 'mass_online',
                $cfg['charset'] ?? 'utf8mb4'
            );
            self::$pdo = new PDO($dsn, (string) ($cfg['user'] ?? 'root'), (string) ($cfg['pass'] ?? ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            self::$pdo = null;
            self::$lastError = $e->getMessage();
        }
        return self::$pdo;
    }

    public static function connected(): bool
    {
        return self::pdo() instanceof PDO;
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    /** Reinicia estado (útil en tests). */
    public static function reset(): void
    {
        self::$pdo = null;
        self::$tried = false;
        self::$lastError = null;
    }
}
