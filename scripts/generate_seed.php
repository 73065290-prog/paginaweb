<?php
declare(strict_types=1);

/**
 * Genera database/seed.sql a partir de los datos demo actuales.
 */
require dirname(__DIR__) . '/includes/data.php';
require dirname(__DIR__) . '/includes/functions.php';

function sql_str(?string $v): string
{
    if ($v === null) {
        return 'NULL';
    }
    return "'" . str_replace(["\\", "'"], ["\\\\", "''"], $v) . "'";
}

function sql_dec(float|int|null $v): string
{
    if ($v === null) {
        return 'NULL';
    }
    return number_format((float) $v, 2, '.', '');
}

$out = [];
$out[] = '-- Seed Mass Online (generado desde datos demo)';
$out[] = 'USE mass_online;';
$out[] = 'SET NAMES utf8mb4;';
$out[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$out[] = 'DELETE FROM pedido_detalles;';
$out[] = 'DELETE FROM pedidos;';
$out[] = 'DELETE FROM inventario;';
$out[] = 'DELETE FROM productos;';
$out[] = 'DELETE FROM categorias;';
$out[] = 'DELETE FROM tiendas;';
$out[] = 'DELETE FROM cupones;';
$out[] = 'DELETE FROM usuarios;';
$out[] = 'DELETE FROM roles;';
$out[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$out[] = '';
$out[] = "INSERT INTO roles (id, nombre) VALUES (1, 'admin'), (2, 'cliente');";
$out[] = '';

$hash = mass_demo_password_hash();
$out[] = 'INSERT INTO usuarios (usuario, password_hash, rol_id, nombre, cargo) VALUES';
$userRows = [];
foreach (mass_usuarios_auth() as $u) {
    $rolId = ($u['rol'] ?? '') === 'admin' ? 1 : 2;
    $userRows[] = sprintf(
        '(%s, %s, %d, %s, %s)',
        sql_str((string) $u['usuario']),
        sql_str($hash),
        $rolId,
        sql_str((string) $u['nombre']),
        sql_str((string) ($u['cargo'] ?? ''))
    );
}
$out[] = implode(",\n", $userRows) . ';';
$out[] = '';

$out[] = 'INSERT INTO categorias (id, nombre, badge, badge_tone, count_hint, cat_filtro, img) VALUES';
$catRows = [];
foreach (mass_categorias() as $c) {
    $catRows[] = sprintf(
        '(%s, %s, %s, %s, %d, %s, %s)',
        sql_str((string) $c['id']),
        sql_str((string) $c['nombre']),
        sql_str((string) ($c['badge'] ?? '')),
        sql_str((string) ($c['badgeTone'] ?? '')),
        (int) ($c['count'] ?? 0),
        sql_str((string) ($c['cat'] ?? '')),
        sql_str((string) ($c['img'] ?? ''))
    );
}
$out[] = implode(",\n", $catRows) . ';';
$out[] = '';

$out[] = 'INSERT INTO tiendas (id, nombre, direccion, distrito, stock, eta, estado, img) VALUES';
$storeRows = [];
foreach (mass_tiendas() as $t) {
    $storeRows[] = sprintf(
        '(%s, %s, %s, %s, %d, %s, %s, %s)',
        sql_str((string) $t['id']),
        sql_str((string) ($t['nombre'] ?? '')),
        sql_str((string) ($t['direccion'] ?? ($t['dir'] ?? ''))),
        sql_str((string) ($t['distrito'] ?? '')),
        (int) ($t['stock'] ?? 0),
        sql_str((string) ($t['eta'] ?? '')),
        sql_str((string) ($t['estado'] ?? 'ok')),
        sql_str((string) ($t['img'] ?? ''))
    );
}
$out[] = implode(",\n", $storeRows) . ';';
$out[] = '';

$out[] = "INSERT INTO cupones (codigo, descuento_fijo, activo) VALUES ('CLUBMASS10', 5.00, 1), ('CLUBMASS60', 6.00, 1);";
$out[] = '';

$adminById = [];
foreach (mass_admin_productos() as $ap) {
    $adminById[(int) $ap['id']] = $ap;
}

$out[] = 'INSERT INTO productos (id, nombre, marca, categoria, subcategoria, unidad, descripcion, ean, precio, precio_antes, costo, stock_label, stock_tone, badge_text, badge_tone, img, img_nombre, favorito, punto_reorden, capacidad_gondola, abc, activo) VALUES';
$prodRows = [];
foreach (mass_productos() as $p) {
    $id = (int) $p['id'];
    $a = $adminById[$id] ?? [];
    $badge = is_array($p['badge'] ?? null) ? $p['badge'] : null;
    $ean = isset($a['ean']) ? (string) $a['ean'] : sprintf('%013d', 7750000000000 + $id);
    $prodRows[] = sprintf(
        '(%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s, %d)',
        $id,
        sql_str((string) $p['nombre']),
        sql_str((string) ($p['marca'] ?? 'MASS')),
        sql_str((string) ($p['categoria'] ?? '')),
        sql_str((string) ($a['subcategoria'] ?? '')),
        sql_str((string) ($p['unidad'] ?? '')),
        sql_str((string) ($a['descripcion'] ?? ($p['nombre'] . ' — producto Mass Online'))),
        sql_str($ean),
        sql_dec((float) $p['precio']),
        isset($p['precioAntes']) ? sql_dec((float) $p['precioAntes']) : 'NULL',
        sql_dec((float) ($a['costo'] ?? max(0.1, ((float) $p['precio']) * 0.7))),
        sql_str((string) ($p['stockLabel'] ?? '')),
        sql_str((string) ($p['stockTone'] ?? 'ok')),
        $badge ? sql_str((string) ($badge['text'] ?? '')) : 'NULL',
        $badge ? sql_str((string) ($badge['tone'] ?? '')) : 'NULL',
        sql_str((string) ($p['img'] ?? '')),
        sql_str((string) ($a['imgNombre'] ?? ('sku_' . $id . '.jpg'))),
        !empty($p['favorito']) ? 1 : 0,
        (int) ($a['puntoReorden'] ?? 10),
        (int) ($a['capacidadGondola'] ?? 50),
        sql_str((string) ($a['abc'] ?? 'B')),
        !empty($a['activo']) || !isset($a['activo']) ? 1 : 0
    );
}
$out[] = implode(",\n", $prodRows) . ';';
$out[] = '';
$out[] = 'ALTER TABLE productos AUTO_INCREMENT = 100;';
$out[] = '';

$out[] = 'INSERT INTO inventario (producto_id, tienda_id, stock_piso, stock_almacen) VALUES';
$invRows = [];
foreach (mass_productos() as $p) {
    $id = (int) $p['id'];
    $a = $adminById[$id] ?? [];
    $invRows[] = sprintf(
        "(%d, 'magdalena', %d, %d)",
        $id,
        (int) ($a['stockPiso'] ?? 20),
        (int) ($a['stockAlmacen'] ?? 40)
    );
}
$out[] = implode(",\n", $invRows) . ';';
$out[] = '';

$path = dirname(__DIR__) . '/database/seed.sql';
file_put_contents($path, implode("\n", $out) . "\n");
echo "OK wrote {$path}\n";
