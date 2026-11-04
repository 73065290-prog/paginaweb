<?php
declare(strict_types=1);

namespace Tests\Integration;

use Database;
use PDO;
use PDOException;
use ReflectionProperty;
use RuntimeException;
use Tests\TestCase;

/**
 * Base de la suite Integration: conecta los repositorios reales a la base exclusiva
 * mass_online_test (MySQL/MariaDB), recrea su esquema desde database/schema.sql y limpia
 * sus tablas antes de cada prueba. Si el servidor no responde, las pruebas se omiten.
 */
abstract class IntegrationTestCase extends TestCase
{
    public const TEST_DB = 'mass_online_test';

    /** @var list<string> Orden hijo -> padre para limpiar sin violar claves foráneas. */
    private const TABLAS = [
        'pedido_detalles', 'pedidos', 'inventario', 'cupones', 'productos',
        'categorias', 'tiendas', 'usuarios', 'roles',
    ];

    private static ?PDO $pdoPrueba = null;
    private static ?string $motivoOmision = null;

    private string|false $massUseDbAnterior = false;

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = self::conexionPrueba();
        if ($pdo === null) {
            $this->markTestSkipped((string) self::$motivoOmision);
        }

        $this->massUseDbAnterior = getenv('MASS_USE_DB');
        putenv('MASS_USE_DB=1');
        self::inyectarEnDatabase($pdo);

        self::exigirBaseDePrueba(Database::pdo());
        $this->limpiarTablas();
        $this->insertar('roles', ['id' => 1, 'nombre' => 'admin']);
        $this->insertar('roles', ['id' => 2, 'nombre' => 'cliente']);
    }

    protected function tearDown(): void
    {
        putenv($this->massUseDbAnterior === false ? 'MASS_USE_DB' : 'MASS_USE_DB=' . $this->massUseDbAnterior);
        Database::reset();
        parent::tearDown();
    }

    /**
     * Configuración de la base de pruebas: variables de entorno > database.test.local.php
     * > database.test.example.php.
     *
     * @return array{host:string,port:int,dbname:string,user:string,pass:string}
     */
    public static function configPrueba(): array
    {
        $dir = dirname(__DIR__, 2) . '/backend/config/';
        $archivo = is_file($dir . 'database.test.local.php')
            ? $dir . 'database.test.local.php'
            : $dir . 'database.test.example.php';
        /** @var array<string,mixed> $cfg */
        $cfg = is_file($archivo) ? require $archivo : [];

        $env = static fn (string $k): ?string => getenv($k) === false ? null : (string) getenv($k);

        return [
            'host' => $env('MASS_TEST_DB_HOST') ?? (string) ($cfg['host'] ?? '127.0.0.1'),
            'port' => (int) ($env('MASS_TEST_DB_PORT') ?? ($cfg['port'] ?? 3306)),
            'dbname' => $env('MASS_TEST_DB_NAME') ?? (string) ($cfg['dbname'] ?? self::TEST_DB),
            'user' => $env('MASS_TEST_DB_USER') ?? (string) ($cfg['user'] ?? 'root'),
            'pass' => $env('MASS_TEST_DB_PASS') ?? (string) ($cfg['pass'] ?? ''),
        ];
    }

    /**
     * Rechaza cualquier nombre distinto de mass_online_test o que coincida con la base
     * configurada para desarrollo (backend/config/database.local.php).
     */
    public static function validarNombreBase(string $dbname, string $dbDesarrollo): void
    {
        if ($dbname !== self::TEST_DB) {
            throw new RuntimeException("BLOQUEO DE SEGURIDAD: la suite Integration solo puede usar '" . self::TEST_DB . "' (configurado: '{$dbname}').");
        }
        if ($dbname === $dbDesarrollo) {
            throw new RuntimeException("BLOQUEO DE SEGURIDAD: la base de pruebas coincide con la base de desarrollo '{$dbDesarrollo}'.");
        }
    }

    /** Falla si la conexión activa no apunta a mass_online_test. */
    public static function exigirBaseDePrueba(?PDO $pdo): void
    {
        $actual = $pdo === null ? null : $pdo->query('SELECT DATABASE()')->fetchColumn();
        if ($actual !== self::TEST_DB) {
            throw new RuntimeException('BLOQUEO DE SEGURIDAD: la conexión activa apunta a ' . var_export($actual, true) . ', no a ' . self::TEST_DB . '.');
        }
    }

    protected function pdoPrueba(): PDO
    {
        self::exigirBaseDePrueba(self::$pdoPrueba);
        return self::$pdoPrueba;
    }

    /** Simula la aplicación sin MySQL: Database::pdo() devuelve null. */
    protected function desconectarBaseDeDatos(): void
    {
        putenv('MASS_USE_DB=0');
        Database::reset();
    }

    /**
     * Inserta una fila de prueba con sentencia preparada.
     *
     * @param array<string, mixed> $fila
     */
    protected function insertar(string $tabla, array $fila): int
    {
        if (!in_array($tabla, self::TABLAS, true)) {
            throw new RuntimeException("Tabla no permitida: {$tabla}");
        }
        foreach (array_keys($fila) as $columna) {
            if (!preg_match('/^[a-z_]+$/', (string) $columna)) {
                throw new RuntimeException("Columna no permitida: {$columna}");
            }
        }
        $pdo = $this->pdoPrueba();
        $columnas = implode(', ', array_keys($fila));
        $marcas = implode(', ', array_fill(0, count($fila), '?'));
        $pdo->prepare("INSERT INTO {$tabla} ({$columnas}) VALUES ({$marcas})")->execute(array_values($fila));
        return (int) $pdo->lastInsertId();
    }

    protected function contar(string $tabla, string $where = '1 = 1', array $params = []): int
    {
        if (!in_array($tabla, self::TABLAS, true)) {
            throw new RuntimeException("Tabla no permitida: {$tabla}");
        }
        $stmt = $this->pdoPrueba()->prepare("SELECT COUNT(*) FROM {$tabla} WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    protected function ejecutar(string $sql, array $params = []): int
    {
        $stmt = $this->pdoPrueba()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** @param array<string, mixed> $extra */
    protected function insertarProducto(array $extra = []): int
    {
        return $this->insertar('productos', $extra + [
            'nombre' => 'Producto de prueba',
            'marca' => 'MASS',
            'categoria' => 'Abarrotes',
            'precio' => 10.00,
            'costo' => 6.50,
        ]);
    }

    protected function insertarTienda(string $id, string $nombre = 'Tienda de prueba'): void
    {
        $this->insertar('tiendas', ['id' => $id, 'nombre' => $nombre]);
    }

    private function limpiarTablas(): void
    {
        $pdo = $this->pdoPrueba();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::TABLAS as $tabla) {
            $pdo->exec("DELETE FROM {$tabla}");
        }
        foreach (['usuarios', 'productos', 'inventario', 'pedidos', 'pedido_detalles'] as $tabla) {
            $pdo->exec("ALTER TABLE {$tabla} AUTO_INCREMENT = 1");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private static function conexionPrueba(): ?PDO
    {
        if (self::$pdoPrueba !== null || self::$motivoOmision !== null) {
            return self::$pdoPrueba;
        }

        $cfg = self::configPrueba();
        self::validarNombreBase($cfg['dbname'], (string) (Database::config()['dbname'] ?? 'mass_online'));

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $cfg['host'], $cfg['port']),
                $cfg['user'],
                $cfg['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 3,
                ]
            );
        } catch (PDOException $e) {
            self::$motivoOmision = sprintf(
                'MySQL/MariaDB no disponible en %s:%d (%s). Suite Integration omitida.',
                $cfg['host'],
                $cfg['port'],
                $e->getMessage()
            );
            return null;
        }

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . self::TEST_DB . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . self::TEST_DB . '`');
        self::exigirBaseDePrueba($pdo);
        self::cargarEsquema($pdo);

        self::$pdoPrueba = $pdo;
        return $pdo;
    }

    /**
     * Ejecuta database/schema.sql sobre mass_online_test omitiendo CREATE DATABASE y USE,
     * de modo que ninguna sentencia pueda alcanzar la base de desarrollo.
     */
    private static function cargarEsquema(PDO $pdo): void
    {
        $sql = (string) file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql');
        $sql = (string) preg_replace('/^\s*--.*$/m', '', $sql);

        foreach (explode(';', $sql) as $sentencia) {
            $sentencia = trim($sentencia);
            if ($sentencia === '' || preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $sentencia)) {
                continue;
            }
            if (preg_match('/\bmass_online\b|`\w+`\s*\./i', $sentencia)) {
                throw new RuntimeException('BLOQUEO DE SEGURIDAD: schema.sql contiene una sentencia que referencia otra base: ' . substr($sentencia, 0, 80));
            }
            self::exigirBaseDePrueba($pdo);
            $pdo->exec($sentencia);
        }
    }

    private static function inyectarEnDatabase(PDO $pdo): void
    {
        (new ReflectionProperty(Database::class, 'pdo'))->setValue(null, $pdo);
        (new ReflectionProperty(Database::class, 'tried'))->setValue(null, true);
        (new ReflectionProperty(Database::class, 'lastError'))->setValue(null, null);
    }
}
