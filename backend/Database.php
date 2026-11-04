<?php
declare(strict_types=1);

/**
 * Conexión PDO singleton.
 * MASS_USE_DB=1 (o config enabled sin la variable) usa MySQL.
 * MASS_USE_DB=0 usa datos demo. Un fallo de conexión no conmuta al modo demo.
 */
final class Database
{
    private static ?PDO $pdo = null;
    private static bool $tried = false;
    private static ?string $lastError = null;
    private static ?string $falloRegistrado = null;

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
            self::$lastError = self::sanearMensaje($e->getMessage());
            self::registrarFalloConexion(self::$lastError, $cfg);
        }
        return self::$pdo;
    }

    public static function connected(): bool
    {
        return self::pdo() instanceof PDO;
    }

    /**
     * demo: MASS_USE_DB desactiva MySQL.
     * mysql: la conexión configurada está activa.
     * error: se pidió MySQL y la conexión falló. No es modo demo.
     */
    public static function modo(): string
    {
        if (!self::shouldUseDb()) {
            return 'demo';
        }
        if (self::connected()) {
            return 'mysql';
        }
        return 'error';
    }

    /**
     * Texto del aviso de administración. Null cuando MySQL está operativo.
     * No incluye host, usuario, contraseña ni el mensaje interno de PDO.
     *
     * @return array{tipo:string,titulo:string,texto:string}|null
     */
    public static function avisoAdmin(): ?array
    {
        $modo = self::modo();
        if ($modo === 'demo') {
            return [
                'tipo' => 'demo',
                'titulo' => 'Modo demo',
                'texto' => 'La aplicación usa datos de demostración porque así se pidió. No está leyendo la base de datos.',
            ];
        }
        if ($modo === 'error') {
            return [
                'tipo' => 'error',
                'titulo' => 'Sin conexión a MySQL',
                'texto' => 'No se pudo conectar a la base de datos configurada. Los datos demo no se activaron en su lugar.',
            ];
        }
        return null;
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
        self::$falloRegistrado = null;
    }

    /**
     * @param array<string, mixed> $cfg
     */
    private static function registrarFalloConexion(string $mensaje, array $cfg): void
    {
        $linea = sprintf(
            'Mass Online: fallo de conexión MySQL con MASS_USE_DB activo (host=%s port=%d dbname=%s). %s',
            (string) ($cfg['host'] ?? '127.0.0.1'),
            (int) ($cfg['port'] ?? 3306),
            (string) ($cfg['dbname'] ?? 'mass_online'),
            $mensaje
        );
        if (self::$falloRegistrado === $linea) {
            return;
        }
        self::$falloRegistrado = $linea;
        error_log($linea);
    }

    private static function sanearMensaje(string $mensaje): string
    {
        $limpio = preg_replace('/(password|passwd|pwd|pass)\s*[=:]\s*\S+/i', '$1=[omitido]', $mensaje);
        if (!is_string($limpio)) {
            $limpio = $mensaje;
        }
        return str_replace(["\r", "\n"], ' ', $limpio);
    }
}
