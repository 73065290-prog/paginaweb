<?php
declare(strict_types=1);

/**
 * Verificación de consistencia de la BD MySQL/MariaDB (PROMPT 16, INT-004-BOLT-003).
 *
 * Compara la base importada desde database/schema.sql + database/seed.sql con los datos demo
 * de includes/data.php: tablas, columnas, conteos, valores clave, claves foráneas, índices,
 * restricciones y registros huérfanos.
 *
 * Uso: php scripts/verificar_bd.php [--dbname=mass_online]
 * Credenciales: backend/config/database.local.php (o database.example.php si no existe).
 * Código de salida: 0 sin errores, 1 con al menos un ERROR, 2 sin conexión.
 *
 * Las pruebas de rechazo (FK, UNIQUE, NOT NULL) se ejecutan dentro de transacciones que siempre
 * se revierten: no modifican datos, aunque InnoDB puede consumir valores AUTO_INCREMENT.
 */

// Los datos de referencia deben salir de los arrays demo, no de la propia BD que se verifica.
putenv('MASS_USE_DB=0');
$_SESSION = [];

require dirname(__DIR__) . '/includes/data.php';
require dirname(__DIR__) . '/includes/functions.php';

$resumen = ['OK' => 0, 'ERROR' => 0, 'ADVERTENCIA' => 0];

function reportar(string $nivel, string $mensaje): void
{
    global $resumen;
    $resumen[$nivel]++;
    echo str_pad('[' . $nivel . ']', 14) . $mensaje . PHP_EOL;
}

function seccion(string $titulo): void
{
    echo PHP_EOL . '== ' . $titulo . ' ==' . PHP_EOL;
}

function comparar(string $contexto, string $campo, mixed $esperado, mixed $actual): bool
{
    if (is_float($esperado) || is_int($esperado)) {
        $iguales = $actual !== null && abs((float) $esperado - (float) $actual) < 0.005;
    } elseif ($esperado === null) {
        $iguales = $actual === null;
    } else {
        $iguales = (string) $esperado === (string) $actual;
    }
    if (!$iguales) {
        reportar('ERROR', sprintf(
            '%s.%s: esperado %s, BD %s',
            $contexto,
            $campo,
            var_export($esperado, true),
            var_export($actual, true)
        ));
    }
    return $iguales;
}

/**
 * @param array<string, mixed> $esperados
 * @param array<string, mixed> $fila
 */
function compararFila(string $contexto, array $esperados, array $fila): bool
{
    $ok = true;
    foreach ($esperados as $campo => $valor) {
        $ok = comparar($contexto, $campo, $valor, $fila[$campo] ?? null) && $ok;
    }
    return $ok;
}

/**
 * @return array<int|string, array<string, mixed>>
 */
function filasPorClave(PDO $pdo, string $sql, string $clave): array
{
    $out = [];
    foreach ($pdo->query($sql)->fetchAll() as $row) {
        $out[$row[$clave]] = $row;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// 1. Conexión PDO
// ---------------------------------------------------------------------------
$cfg = Database::config();
$dbname = (string) ($cfg['dbname'] ?? 'mass_online');
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--dbname=')) {
        $dbname = substr($arg, 9);
    }
}
if (!preg_match('/^\w+$/', $dbname)) {
    fwrite(STDERR, "Nombre de base de datos no válido: {$dbname}\n");
    exit(2);
}

echo '=== Verificación BD Mass Online (scripts/verificar_bd.php) ===' . PHP_EOL;
echo 'Fecha: ' . date('Y-m-d H:i:s T') . PHP_EOL;

seccion('1. Conexión PDO');
try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            (string) ($cfg['host'] ?? '127.0.0.1'),
            (int) ($cfg['port'] ?? 3306),
            $dbname
        ),
        (string) ($cfg['user'] ?? 'root'),
        (string) ($cfg['pass'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => true,
        ]
    );
} catch (PDOException $e) {
    reportar('ERROR', 'No se pudo conectar a ' . $dbname . ': ' . $e->getMessage());
    echo PHP_EOL . 'RESULTADO: VERIFICACION_BD_SIN_CONEXION' . PHP_EOL;
    exit(2);
}
$version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
reportar('OK', sprintf('Conectado a %s@%s:%d/%s (servidor %s)', $cfg['user'] ?? 'root', $cfg['host'] ?? '127.0.0.1', (int) ($cfg['port'] ?? 3306), $dbname, $version));

$schemaInfo = $pdo->prepare('SELECT DEFAULT_CHARACTER_SET_NAME AS cs, DEFAULT_COLLATION_NAME AS co FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
$schemaInfo->execute([$dbname]);
$charset = $schemaInfo->fetch() ?: ['cs' => '', 'co' => ''];
if ($charset['cs'] === 'utf8mb4') {
    reportar('OK', 'Base de datos con charset utf8mb4 (' . $charset['co'] . ')');
} else {
    reportar('ERROR', 'Charset de la base de datos: ' . $charset['cs'] . ' (se esperaba utf8mb4)');
}

// ---------------------------------------------------------------------------
// 2. Tablas, motor, collation y columnas
// ---------------------------------------------------------------------------
$columnasEsperadas = [
    'roles' => ['id', 'nombre'],
    'usuarios' => ['id', 'usuario', 'password_hash', 'rol_id', 'nombre', 'cargo', 'activo', 'created_at'],
    'categorias' => ['id', 'nombre', 'badge', 'badge_tone', 'count_hint', 'cat_filtro', 'img'],
    'productos' => ['id', 'nombre', 'marca', 'categoria', 'subcategoria', 'unidad', 'descripcion', 'ean', 'precio', 'precio_antes', 'costo', 'stock_label', 'stock_tone', 'badge_text', 'badge_tone', 'img', 'img_nombre', 'favorito', 'punto_reorden', 'capacidad_gondola', 'abc', 'activo', 'created_at', 'updated_at'],
    'tiendas' => ['id', 'nombre', 'direccion', 'distrito', 'stock', 'eta', 'estado', 'img'],
    'inventario' => ['id', 'producto_id', 'tienda_id', 'stock_piso', 'stock_almacen'],
    'cupones' => ['codigo', 'descuento_fijo', 'activo'],
    'pedidos' => ['id', 'codigo', 'usuario_id', 'entrega', 'pago', 'comprobante', 'nombre', 'apellido', 'documento', 'telefono', 'direccion', 'referencia', 'tienda_id', 'horario', 'cupon', 'subtotal', 'descuento', 'envio', 'total', 'estado', 'created_at'],
    'pedido_detalles' => ['id', 'pedido_id', 'producto_id', 'nombre', 'precio_unit', 'cantidad', 'subtotal'],
];

seccion('2. Tablas y columnas (' . count($columnasEsperadas) . ' esperadas)');
$stmt = $pdo->prepare('SELECT TABLE_NAME AS t, ENGINE AS e, TABLE_COLLATION AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?');
$stmt->execute([$dbname]);
$tablas = [];
foreach ($stmt->fetchAll() as $row) {
    $tablas[$row['t']] = $row;
}
$stmt = $pdo->prepare('SELECT TABLE_NAME AS t, COLUMN_NAME AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?');
$stmt->execute([$dbname]);
$columnas = [];
foreach ($stmt->fetchAll() as $row) {
    $columnas[$row['t']][] = $row['c'];
}

$faltanTablas = false;
foreach ($columnasEsperadas as $tabla => $cols) {
    if (!isset($tablas[$tabla])) {
        reportar('ERROR', "Tabla {$tabla} no existe");
        $faltanTablas = true;
        continue;
    }
    $faltan = array_diff($cols, $columnas[$tabla] ?? []);
    $sobran = array_diff($columnas[$tabla] ?? [], $cols);
    $motorOk = $tablas[$tabla]['e'] === 'InnoDB' && str_starts_with((string) $tablas[$tabla]['c'], 'utf8mb4');
    if ($faltan === [] && $motorOk) {
        reportar('OK', sprintf('%s existe (%s, %s, %d columnas)', $tabla, $tablas[$tabla]['e'], $tablas[$tabla]['c'], count($cols)));
    } else {
        if ($faltan !== []) {
            reportar('ERROR', "{$tabla}: faltan columnas " . implode(', ', $faltan));
        }
        if (!$motorOk) {
            reportar('ERROR', "{$tabla}: motor/collation {$tablas[$tabla]['e']}/{$tablas[$tabla]['c']} (se esperaba InnoDB/utf8mb4)");
        }
    }
    if ($sobran !== []) {
        reportar('ADVERTENCIA', "{$tabla}: columnas no definidas en schema.sql: " . implode(', ', $sobran));
    }
}
$extras = array_diff(array_keys($tablas), array_keys($columnasEsperadas));
if ($extras !== []) {
    reportar('ADVERTENCIA', 'Tablas adicionales no definidas en schema.sql: ' . implode(', ', $extras));
}
if ($faltanTablas) {
    echo PHP_EOL . 'RESULTADO: VERIFICACION_BD_FAIL (faltan tablas; importa database/schema.sql)' . PHP_EOL;
    exit(1);
}

// ---------------------------------------------------------------------------
// Datos demo de referencia (includes/data.php y servicios con fallback demo)
// ---------------------------------------------------------------------------
$demoProductos = mass_productos();
$demoCategorias = mass_categorias();
$demoTiendas = mass_tiendas();
$demoUsuarios = (new AuthService())->usuariosDemo();
$demoCupones = (new CuponService())->cuponesValidos();
$demoAdmin = [];
foreach ((new ProductoService())->listarAdmin() as $a) {
    $demoAdmin[(int) $a['id']] = $a;
}

// ---------------------------------------------------------------------------
// 3. Conteos por tabla
// ---------------------------------------------------------------------------
seccion('3. Conteo de registros por tabla');
$conteos = [];
foreach (array_keys($columnasEsperadas) as $tabla) {
    $conteos[$tabla] = (int) $pdo->query("SELECT COUNT(*) FROM `{$tabla}`")->fetchColumn();
}
$esperadosConteo = [
    'roles' => 2,
    'usuarios' => count($demoUsuarios),
    'categorias' => count($demoCategorias),
    'productos' => count($demoProductos),
    'tiendas' => count($demoTiendas),
    'inventario' => count($demoProductos),
    'cupones' => count($demoCupones),
    'pedidos' => 0,
    'pedido_detalles' => 0,
];
foreach ($esperadosConteo as $tabla => $esperado) {
    $actual = $conteos[$tabla];
    $msg = sprintf('%-16s BD=%-4d demo=%d', $tabla, $actual, $esperado);
    if ($actual === $esperado) {
        reportar('OK', $msg);
    } elseif ($actual > $esperado) {
        reportar('ADVERTENCIA', $msg . ' (registros adicionales creados por la aplicación: pedidos o altas admin)');
    } else {
        reportar('ERROR', $msg . ' (faltan registros del seed)');
    }
}

// ---------------------------------------------------------------------------
// 4. Comparación de valores con los datos demo
// ---------------------------------------------------------------------------
seccion('4. Comparación de valores con includes/data.php');

$roles = filasPorClave($pdo, 'SELECT id, nombre FROM roles', 'nombre');
$rolesOk = isset($roles['admin'], $roles['cliente']) && (int) $roles['admin']['id'] === 1 && (int) $roles['cliente']['id'] === 2;
reportar($rolesOk ? 'OK' : 'ERROR', 'roles: admin=1 y cliente=2' . ($rolesOk ? '' : ' (valores distintos)'));

$usuarios = filasPorClave($pdo, 'SELECT u.usuario, u.password_hash, u.nombre, u.cargo, u.activo, r.nombre AS rol FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id', 'usuario');
$okUsuarios = 0;
foreach ($demoUsuarios as $u) {
    $fila = $usuarios[$u['usuario']] ?? null;
    if ($fila === null) {
        reportar('ERROR', "usuarios: falta '{$u['usuario']}'");
        continue;
    }
    $ok = compararFila('usuarios[' . $u['usuario'] . ']', [
        'rol' => $u['rol'],
        'nombre' => $u['nombre'],
        'cargo' => $u['cargo'],
        'password_hash' => $u['password_hash'],
        'activo' => 1,
    ], $fila);
    $okUsuarios += $ok ? 1 : 0;
}
reportar($okUsuarios === count($demoUsuarios) ? 'OK' : 'ERROR', "usuarios: {$okUsuarios}/" . count($demoUsuarios) . ' coinciden (usuario, rol, nombre, cargo, hash demo)');
$idsUsuarios = array_map('intval', $pdo->query('SELECT id FROM usuarios ORDER BY id LIMIT ' . count($demoUsuarios))->fetchAll(PDO::FETCH_COLUMN));
$idsEsperados = range(1, count($demoUsuarios));
if ($idsUsuarios === $idsEsperados) {
    reportar('OK', 'usuarios: ids del seed = ' . implode(',', $idsEsperados));
} else {
    reportar('ADVERTENCIA', 'usuarios: ids ' . implode(',', $idsUsuarios) . ' (se esperaban ' . implode(',', $idsEsperados) . '; seed reimportado sin reiniciar AUTO_INCREMENT)');
}

$categorias = filasPorClave($pdo, 'SELECT * FROM categorias', 'id');
$okCat = 0;
foreach ($demoCategorias as $c) {
    $fila = $categorias[$c['id']] ?? null;
    if ($fila === null) {
        reportar('ERROR', "categorias: falta '{$c['id']}'");
        continue;
    }
    $ok = compararFila('categorias[' . $c['id'] . ']', [
        'nombre' => $c['nombre'],
        'badge' => $c['badge'] ?? '',
        'badge_tone' => $c['badgeTone'] ?? '',
        'count_hint' => (int) ($c['count'] ?? 0),
        'cat_filtro' => $c['cat'] ?? '',
        'img' => $c['img'] ?? '',
    ], $fila);
    $okCat += $ok ? 1 : 0;
}
reportar($okCat === count($demoCategorias) ? 'OK' : 'ERROR', "categorias: {$okCat}/" . count($demoCategorias) . ' coinciden (nombre, badge, count_hint, cat_filtro, img)');

$tiendas = filasPorClave($pdo, 'SELECT * FROM tiendas', 'id');
$okTiendas = 0;
foreach ($demoTiendas as $t) {
    $fila = $tiendas[$t['id']] ?? null;
    if ($fila === null) {
        reportar('ERROR', "tiendas: falta '{$t['id']}'");
        continue;
    }
    $ok = compararFila('tiendas[' . $t['id'] . ']', [
        'nombre' => $t['nombre'],
        'direccion' => $t['direccion'] ?? '',
        'stock' => (int) ($t['stock'] ?? 0),
        'estado' => $t['estado'] ?? 'ok',
    ], $fila);
    $okTiendas += $ok ? 1 : 0;
}
reportar($okTiendas === count($demoTiendas) ? 'OK' : 'ERROR', "tiendas: {$okTiendas}/" . count($demoTiendas) . ' coinciden (nombre, direccion, stock, estado)');
reportar('ADVERTENCIA', 'tiendas: distancia, estadoLabel, misma, lat y lng existen solo en data.php (no forman parte de schema.sql); distrito, eta e img quedan vacíos en el seed');

$productos = filasPorClave($pdo, 'SELECT * FROM productos', 'id');
$okProd = 0;
foreach ($demoProductos as $p) {
    $id = (int) $p['id'];
    $fila = $productos[$id] ?? null;
    if ($fila === null) {
        reportar('ERROR', "productos: falta id={$id}");
        continue;
    }
    $badge = is_array($p['badge'] ?? null) ? $p['badge'] : null;
    $a = $demoAdmin[$id] ?? [];
    $ok = compararFila('productos[' . $id . ']', [
        'nombre' => $p['nombre'],
        'marca' => $p['marca'],
        'categoria' => $p['categoria'],
        'unidad' => $p['unidad'],
        'precio' => (float) $p['precio'],
        'precio_antes' => isset($p['precioAntes']) ? (float) $p['precioAntes'] : null,
        'stock_label' => $p['stockLabel'],
        'stock_tone' => $p['stockTone'],
        'badge_text' => $badge['text'] ?? null,
        'badge_tone' => $badge['tone'] ?? null,
        'img' => $p['img'],
        'favorito' => !empty($p['favorito']) ? 1 : 0,
        'ean' => $a['ean'] ?? null,
        'subcategoria' => $a['subcategoria'] ?? '',
        'descripcion' => $a['descripcion'] ?? '',
        'costo' => (float) ($a['costo'] ?? 0),
        'punto_reorden' => (int) ($a['puntoReorden'] ?? 0),
        'capacidad_gondola' => (int) ($a['capacidadGondola'] ?? 0),
        'abc' => $a['abc'] ?? '',
        'img_nombre' => $a['imgNombre'] ?? '',
        'activo' => 1,
    ], $fila);
    $okProd += $ok ? 1 : 0;
}
reportar($okProd === count($demoProductos) ? 'OK' : 'ERROR', "productos: {$okProd}/" . count($demoProductos) . ' coinciden (21 campos: catálogo de data.php + campos WMS de ProductoService::listarAdmin)');

$sumaPrecios = array_sum(array_map(static fn (array $p): float => (float) $p['precio'], $demoProductos));
$sumaBd = (float) $pdo->query('SELECT COALESCE(SUM(precio), 0) FROM productos WHERE id IN (' . implode(',', array_map(static fn (array $p): int => (int) $p['id'], $demoProductos)) . ')')->fetchColumn();
reportar(abs($sumaPrecios - $sumaBd) < 0.005 ? 'OK' : 'ERROR', sprintf('productos: suma de precios demo S/ %.2f vs BD S/ %.2f', $sumaPrecios, $sumaBd));

$inventario = filasPorClave($pdo, "SELECT producto_id, stock_piso, stock_almacen FROM inventario WHERE tienda_id = 'magdalena'", 'producto_id');
$okInv = 0;
foreach ($demoProductos as $p) {
    $id = (int) $p['id'];
    $fila = $inventario[$id] ?? null;
    if ($fila === null) {
        reportar('ERROR', "inventario: producto {$id} sin registro en tienda magdalena");
        continue;
    }
    $a = $demoAdmin[$id] ?? [];
    $ok = compararFila('inventario[' . $id . '@magdalena]', [
        'stock_piso' => (int) ($a['stockPiso'] ?? 0),
        'stock_almacen' => (int) ($a['stockAlmacen'] ?? 0),
    ], $fila);
    $okInv += $ok ? 1 : 0;
}
reportar($okInv === count($demoProductos) ? 'OK' : 'ERROR', "inventario: {$okInv}/" . count($demoProductos) . ' coinciden con stockPiso/stockAlmacen de ProductoService::listarAdmin (tienda magdalena)');
reportar('ADVERTENCIA', 'inventario: data.php solo define stock por producto para Magdalena; pueblo_libre, san_miguel y brena tienen stock agregado en tiendas.stock pero sin filas por producto (diferencia legítima, no se inventan registros)');

$cupones = filasPorClave($pdo, 'SELECT * FROM cupones', 'codigo');
$okCup = 0;
foreach ($demoCupones as $codigo => $descuento) {
    $fila = $cupones[$codigo] ?? null;
    if ($fila === null) {
        reportar('ERROR', "cupones: falta '{$codigo}'");
        continue;
    }
    $ok = compararFila('cupones[' . $codigo . ']', ['descuento_fijo' => (float) $descuento, 'activo' => 1], $fila);
    $okCup += $ok ? 1 : 0;
}
reportar($okCup === count($demoCupones) ? 'OK' : 'ERROR', "cupones: {$okCup}/" . count($demoCupones) . ' coinciden con CuponService::cuponesValidos()');

$filtros = mass_filtros_catalogo();
$porCategoria = [];
foreach ($pdo->query('SELECT categoria, COUNT(*) AS n FROM productos GROUP BY categoria')->fetchAll() as $row) {
    $porCategoria[$row['categoria']] = (int) $row['n'];
}
foreach ($filtros['categorias'] as $f) {
    $real = $f['id'] === 'todos' ? array_sum($porCategoria) : ($porCategoria[$f['id']] ?? 0);
    if ($real === (int) $f['count']) {
        reportar('OK', sprintf('filtro catálogo "%s": %d productos (data.php indica %d)', $f['id'], $real, $f['count']));
    } else {
        reportar('ADVERTENCIA', sprintf('filtro catálogo "%s": BD tiene %d productos, data.php muestra count=%d (etiqueta fija en mass_filtros_catalogo())', $f['id'], $real, $f['count']));
    }
}

// ---------------------------------------------------------------------------
// 5. Claves foráneas
// ---------------------------------------------------------------------------
seccion('5. Claves foráneas');
$fksEsperadas = [
    'fk_usuarios_rol' => ['usuarios', 'rol_id', 'roles', 'id', 'RESTRICT'],
    'fk_inventario_producto' => ['inventario', 'producto_id', 'productos', 'id', 'CASCADE'],
    'fk_inventario_tienda' => ['inventario', 'tienda_id', 'tiendas', 'id', 'CASCADE'],
    'fk_pedidos_usuario' => ['pedidos', 'usuario_id', 'usuarios', 'id', 'SET NULL'],
    'fk_pedidos_tienda' => ['pedidos', 'tienda_id', 'tiendas', 'id', 'SET NULL'],
    'fk_detalles_pedido' => ['pedido_detalles', 'pedido_id', 'pedidos', 'id', 'CASCADE'],
    'fk_detalles_producto' => ['pedido_detalles', 'producto_id', 'productos', 'id', 'SET NULL'],
];
$stmt = $pdo->prepare(
    'SELECT k.CONSTRAINT_NAME AS n, k.TABLE_NAME AS t, k.COLUMN_NAME AS c, k.REFERENCED_TABLE_NAME AS rt,
            k.REFERENCED_COLUMN_NAME AS rc, r.DELETE_RULE AS d
       FROM information_schema.KEY_COLUMN_USAGE k
       JOIN information_schema.REFERENTIAL_CONSTRAINTS r
         ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
      WHERE k.TABLE_SCHEMA = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL'
);
$stmt->execute([$dbname]);
$fks = [];
foreach ($stmt->fetchAll() as $row) {
    $fks[$row['n']] = $row;
}
foreach ($fksEsperadas as $nombre => [$t, $c, $rt, $rc, $regla]) {
    $fk = $fks[$nombre] ?? null;
    $desc = "{$t}.{$c} -> {$rt}.{$rc} ON DELETE {$regla}";
    if ($fk === null) {
        reportar('ERROR', "{$nombre} no existe ({$desc})");
    } elseif ($fk['t'] !== $t || $fk['c'] !== $c || $fk['rt'] !== $rt || $fk['rc'] !== $rc || $fk['d'] !== $regla) {
        reportar('ERROR', "{$nombre} difiere: {$fk['t']}.{$fk['c']} -> {$fk['rt']}.{$fk['rc']} ON DELETE {$fk['d']} (esperado {$desc})");
    } else {
        reportar('OK', "{$nombre}: {$desc}");
    }
}
foreach (array_diff(array_keys($fks), array_keys($fksEsperadas)) as $extra) {
    reportar('ADVERTENCIA', "Clave foránea no definida en schema.sql: {$extra}");
}

$fkChecks = (int) $pdo->query('SELECT @@FOREIGN_KEY_CHECKS')->fetchColumn();
reportar($fkChecks === 1 ? 'OK' : 'ERROR', 'FOREIGN_KEY_CHECKS de la sesión = ' . $fkChecks);

/**
 * Ejecuta una escritura que debe ser rechazada por el motor y la revierte siempre.
 */
function probarRechazo(PDO $pdo, string $descripcion, string $sql, array $params, string $sqlstateEsperado, int $codigoEsperado): void
{
    $pdo->beginTransaction();
    try {
        $pdo->prepare($sql)->execute($params);
        reportar('ERROR', $descripcion . ': el motor aceptó la escritura inválida');
    } catch (PDOException $e) {
        $codigo = (int) ($e->errorInfo[1] ?? 0);
        if ($e->getCode() === $sqlstateEsperado && $codigo === $codigoEsperado) {
            reportar('OK', $descripcion . ": rechazado (SQLSTATE {$sqlstateEsperado}, error {$codigo})");
        } else {
            reportar('ERROR', $descripcion . ': error inesperado ' . $e->getMessage());
        }
    } finally {
        $pdo->rollBack();
    }
}

probarRechazo($pdo, 'FK inventario.producto_id inexistente', "INSERT INTO inventario (producto_id, tienda_id) VALUES (?, 'magdalena')", [999999], '23000', 1452);
probarRechazo($pdo, 'FK inventario.tienda_id inexistente', 'INSERT INTO inventario (producto_id, tienda_id) VALUES (1, ?)', ['tienda_inexistente'], '23000', 1452);
probarRechazo($pdo, 'FK usuarios.rol_id inexistente', "INSERT INTO usuarios (usuario, password_hash, rol_id, nombre) VALUES ('verif_fk', 'x', ?, 'x')", [99], '23000', 1452);
probarRechazo($pdo, 'FK pedido_detalles.pedido_id inexistente', "INSERT INTO pedido_detalles (pedido_id, nombre) VALUES (?, 'x')", [999999], '23000', 1452);
probarRechazo($pdo, 'Borrar rol con usuarios asociados (RESTRICT)', 'DELETE FROM roles WHERE id = ?', [1], '23000', 1451);

// ---------------------------------------------------------------------------
// 6. Índices y restricciones
// ---------------------------------------------------------------------------
seccion('6. Índices y restricciones');
$indicesEsperados = [
    'roles' => ['PRIMARY' => [true, 'id'], 'uq_roles_nombre' => [true, 'nombre']],
    'usuarios' => ['PRIMARY' => [true, 'id'], 'uq_usuarios_usuario' => [true, 'usuario'], 'idx_usuarios_rol' => [false, 'rol_id']],
    'categorias' => ['PRIMARY' => [true, 'id']],
    'productos' => ['PRIMARY' => [true, 'id'], 'uq_productos_ean' => [true, 'ean'], 'idx_productos_categoria' => [false, 'categoria'], 'idx_productos_nombre' => [false, 'nombre']],
    'tiendas' => ['PRIMARY' => [true, 'id']],
    'inventario' => ['PRIMARY' => [true, 'id'], 'uq_inventario_prod_tienda' => [true, 'producto_id,tienda_id'], 'idx_inventario_tienda' => [false, 'tienda_id']],
    'cupones' => ['PRIMARY' => [true, 'codigo']],
    'pedidos' => ['PRIMARY' => [true, 'id'], 'uq_pedidos_codigo' => [true, 'codigo'], 'idx_pedidos_usuario' => [false, 'usuario_id'], 'idx_pedidos_tienda' => [false, 'tienda_id']],
    'pedido_detalles' => ['PRIMARY' => [true, 'id'], 'idx_detalles_pedido' => [false, 'pedido_id']],
];
$stmt = $pdo->prepare(
    'SELECT TABLE_NAME AS t, INDEX_NAME AS i, NON_UNIQUE AS nu,
            GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols
       FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = ?
      GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE'
);
$stmt->execute([$dbname]);
$indices = [];
foreach ($stmt->fetchAll() as $row) {
    $indices[$row['t']][$row['i']] = $row;
}
foreach ($indicesEsperados as $tabla => $lista) {
    foreach ($lista as $nombre => [$unico, $cols]) {
        $idx = $indices[$tabla][$nombre] ?? null;
        $tipo = $nombre === 'PRIMARY' ? 'PRIMARY KEY' : ($unico ? 'UNIQUE' : 'INDEX');
        if ($idx === null) {
            reportar('ERROR', "{$tabla}: falta {$tipo} {$nombre} ({$cols})");
        } elseif ($idx['cols'] !== $cols || ((int) $idx['nu'] === 0) !== $unico) {
            reportar('ERROR', "{$tabla}.{$nombre}: columnas/unicidad distintas ({$idx['cols']}, non_unique={$idx['nu']})");
        } else {
            reportar('OK', "{$tabla}: {$tipo} {$nombre} ({$cols})");
        }
    }
}
foreach ($fksEsperadas as $nombre => [$t, $c]) {
    $cubierta = false;
    foreach ($indices[$t] ?? [] as $idx) {
        if (explode(',', (string) $idx['cols'])[0] === $c) {
            $cubierta = true;
            break;
        }
    }
    reportar($cubierta ? 'OK' : 'ERROR', "Columna FK {$t}.{$c} indexada" . ($cubierta ? '' : ' (sin índice)'));
}

probarRechazo($pdo, 'UNIQUE productos.ean duplicado', "INSERT INTO productos (nombre, ean) VALUES ('verif_ean', ?)", ['7756123400001'], '23000', 1062);
probarRechazo($pdo, 'UNIQUE usuarios.usuario duplicado', "INSERT INTO usuarios (usuario, password_hash, rol_id, nombre) VALUES (?, 'x', 1, 'x')", ['admin'], '23000', 1062);
probarRechazo($pdo, 'UNIQUE inventario (producto_id, tienda_id) duplicado', "INSERT INTO inventario (producto_id, tienda_id) VALUES (1, ?)", ['magdalena'], '23000', 1062);
probarRechazo($pdo, 'PRIMARY KEY cupones.codigo duplicado', 'INSERT INTO cupones (codigo) VALUES (?)', ['CLUBMASS10'], '23000', 1062);
probarRechazo($pdo, 'NOT NULL productos.nombre', 'INSERT INTO productos (nombre, ean) VALUES (?, NULL)', [null], '23000', 1048);

$autoInc = (int) $pdo->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos'")->fetchColumn();
$maxProd = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM productos')->fetchColumn();
reportar($autoInc > $maxProd ? 'OK' : 'ERROR', "productos AUTO_INCREMENT={$autoInc} (> id máximo {$maxProd}; altas admin no colisionan con el seed)");

// ---------------------------------------------------------------------------
// 7. Registros huérfanos e integridad lógica
// ---------------------------------------------------------------------------
seccion('7. Registros huérfanos e integridad lógica');
$huerfanos = [
    'usuarios sin rol' => 'SELECT COUNT(*) FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE r.id IS NULL',
    'inventario sin producto' => 'SELECT COUNT(*) FROM inventario i LEFT JOIN productos p ON p.id = i.producto_id WHERE p.id IS NULL',
    'inventario sin tienda' => 'SELECT COUNT(*) FROM inventario i LEFT JOIN tiendas t ON t.id = i.tienda_id WHERE t.id IS NULL',
    'pedidos con usuario inexistente' => 'SELECT COUNT(*) FROM pedidos p LEFT JOIN usuarios u ON u.id = p.usuario_id WHERE p.usuario_id IS NOT NULL AND u.id IS NULL',
    'pedidos con tienda inexistente' => 'SELECT COUNT(*) FROM pedidos p LEFT JOIN tiendas t ON t.id = p.tienda_id WHERE p.tienda_id IS NOT NULL AND t.id IS NULL',
    'pedido_detalles sin pedido' => 'SELECT COUNT(*) FROM pedido_detalles d LEFT JOIN pedidos p ON p.id = d.pedido_id WHERE p.id IS NULL',
    'pedido_detalles con producto inexistente' => 'SELECT COUNT(*) FROM pedido_detalles d LEFT JOIN productos p ON p.id = d.producto_id WHERE d.producto_id IS NOT NULL AND p.id IS NULL',
];
foreach ($huerfanos as $desc => $sql) {
    $n = (int) $pdo->query($sql)->fetchColumn();
    reportar($n === 0 ? 'OK' : 'ERROR', "{$desc}: {$n}");
}

$sinCategoria = $pdo->query('SELECT p.id, p.categoria FROM productos p WHERE NOT EXISTS (SELECT 1 FROM categorias c WHERE c.cat_filtro = p.categoria)')->fetchAll();
if ($sinCategoria === []) {
    reportar('OK', 'productos.categoria: todas existen en categorias.cat_filtro (relación lógica, sin FK en schema.sql)');
} else {
    reportar('ADVERTENCIA', 'productos sin categoría equivalente en categorias.cat_filtro: ' . implode(', ', array_map(static fn (array $r): string => $r['id'] . '=' . $r['categoria'], $sinCategoria)));
}

$sinInventario = $pdo->query('SELECT p.id FROM productos p LEFT JOIN inventario i ON i.producto_id = p.id WHERE i.id IS NULL')->fetchAll(PDO::FETCH_COLUMN);
reportar($sinInventario === [] ? 'OK' : 'ADVERTENCIA', 'productos sin fila de inventario: ' . ($sinInventario === [] ? '0' : implode(', ', $sinInventario)));

$cuponesHuerfanos = (int) $pdo->query("SELECT COUNT(*) FROM pedidos p LEFT JOIN cupones c ON c.codigo = p.cupon WHERE p.cupon <> '' AND c.codigo IS NULL")->fetchColumn();
reportar($cuponesHuerfanos === 0 ? 'OK' : 'ADVERTENCIA', "pedidos con cupón no registrado en cupones: {$cuponesHuerfanos} (relación lógica, sin FK)");

$tonoIncoherente = $pdo->query(
    "SELECT p.id FROM productos p JOIN inventario i ON i.producto_id = p.id AND i.tienda_id = 'magdalena'
      WHERE (p.stock_tone = 'bajo') <> (i.stock_piso < p.punto_reorden)"
)->fetchAll(PDO::FETCH_COLUMN);
reportar($tonoIncoherente === [] ? 'OK' : 'ADVERTENCIA', 'stock_tone coherente con stock_piso < punto_reorden en Magdalena' . ($tonoIncoherente === [] ? '' : ': revisar ids ' . implode(', ', $tonoIncoherente)));

// LIKE BINARY: con utf8mb4_unicode_ci 'Ã' equivale a 'a' y daría falsos positivos.
$noUtf8 = (int) $pdo->query("SELECT COUNT(*) FROM productos WHERE nombre LIKE BINARY '%Ã%' OR marca LIKE BINARY '%Ã%' OR descripcion LIKE BINARY '%Ã%'")->fetchColumn()
    + (int) $pdo->query("SELECT COUNT(*) FROM categorias WHERE nombre LIKE BINARY '%Ã%'")->fetchColumn()
    + (int) $pdo->query("SELECT COUNT(*) FROM tiendas WHERE nombre LIKE BINARY '%Ã%' OR direccion LIKE BINARY '%Ã%'")->fetchColumn()
    + (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE nombre LIKE BINARY '%Ã%'")->fetchColumn();
reportar($noUtf8 === 0 ? 'OK' : 'ERROR', "Textos sin doble codificación (mojibake 'Ã'): {$noUtf8} filas afectadas");

// ---------------------------------------------------------------------------
// Resumen
// ---------------------------------------------------------------------------
seccion('Resumen');
printf("OK=%d  ERROR=%d  ADVERTENCIA=%d\n", $resumen['OK'], $resumen['ERROR'], $resumen['ADVERTENCIA']);
echo 'RESULTADO: ' . ($resumen['ERROR'] === 0 ? 'VERIFICACION_BD_OK' : 'VERIFICACION_BD_FAIL') . PHP_EOL;
exit($resumen['ERROR'] === 0 ? 0 : 1);
