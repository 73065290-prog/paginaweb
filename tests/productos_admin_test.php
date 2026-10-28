<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';

$ok = 0;
$fail = 0;

function assert_true(bool $cond, string $msg): void
{
    global $ok, $fail;
    if ($cond) {
        echo "OK  $msg\n";
        $ok++;
    } else {
        echo "FAIL  $msg\n";
        $fail++;
    }
}

echo "=== Admin Productos (Figma 35-9928) ===\n";

$_SESSION['admin_producto_overrides'] = [];
$_SESSION['admin_productos_extra'] = [];

$list = mass_admin_productos();
assert_true(count($list) > 0, 'lista admin productos no vacía');
assert_true(isset($list[0]['sku'], $list[0]['ean'], $list[0]['abc'], $list[0]['costo']), 'campos WMS presentes');

$p1 = mass_admin_producto_por_id(1);
assert_true($p1 !== null, 'producto id=1 existe');

// Reglas de negocio
$badNombre = producto_admin_validar([
    'nombre' => 'Ab',
    'ean' => '7756123489123',
    'unidad' => 'Botella',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites',
    'descripcion' => 'Descripción válida de góndola',
    'precio' => 10,
    'costo' => 5,
    'puntoReorden' => 10,
    'capacidadGondola' => 100,
    'abc' => 'A',
    'activo' => 1,
]);
assert_true(!$badNombre['ok'], 'nombre corto falla');

$badEan = producto_admin_validar([
    'nombre' => 'Aceite Primor Extra',
    'ean' => '123',
    'unidad' => 'Botella',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites',
    'descripcion' => 'Descripción válida de góndola',
    'precio' => 10,
    'costo' => 5,
    'puntoReorden' => 10,
    'capacidadGondola' => 100,
    'abc' => 'A',
]);
assert_true(!$badEan['ok'], 'EAN inválido falla');

$badMargen = producto_admin_validar([
    'nombre' => 'Aceite Primor Extra',
    'ean' => '7756123489991',
    'unidad' => 'Botella',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites',
    'descripcion' => 'Descripción válida de góndola',
    'precio' => 5,
    'costo' => 5,
    'puntoReorden' => 10,
    'capacidadGondola' => 100,
    'abc' => 'A',
]);
assert_true(!$badMargen['ok'], 'PVP <= costo falla');

$badReorden = producto_admin_validar([
    'nombre' => 'Aceite Primor Extra',
    'ean' => '7756123489992',
    'unidad' => 'Botella',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites',
    'descripcion' => 'Descripción válida de góndola',
    'precio' => 10,
    'costo' => 5,
    'puntoReorden' => 100,
    'capacidadGondola' => 50,
    'abc' => 'A',
]);
assert_true(!$badReorden['ok'], 'reorden >= capacidad falla');

$badAbc = producto_admin_validar([
    'nombre' => 'Aceite Primor Extra',
    'ean' => '7756123489993',
    'unidad' => 'Botella',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites',
    'descripcion' => 'Descripción válida de góndola',
    'precio' => 10,
    'costo' => 5,
    'puntoReorden' => 10,
    'capacidadGondola' => 100,
    'abc' => '',
]);
assert_true(!$badAbc['ok'], 'ABC vacío falla');

$eanExistente = (string) ($p1['ean'] ?? '');
$dupEan = producto_admin_validar([
    'nombre' => 'Producto Duplicado EAN XX',
    'ean' => $eanExistente,
    'unidad' => 'Botella',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites',
    'descripcion' => 'Descripción válida de góndola',
    'precio' => 10,
    'costo' => 5,
    'puntoReorden' => 10,
    'capacidadGondola' => 100,
    'abc' => 'A',
], 0);
assert_true(!$dupEan['ok'], 'EAN duplicado en alta falla');

$okValid = producto_admin_validar([
    'nombre' => 'Aceite Primor Clásico 900ml Edición',
    'ean' => '7756123489123',
    'unidad' => 'Unidad (Botella 900ml)',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites y Grasas',
    'descripcion' => 'Aceite de soya y palma en botella PET.',
    'precio' => 7.90,
    'costo' => 5.40,
    'puntoReorden' => 40,
    'capacidadGondola' => 200,
    'abc' => 'A',
    'activo' => 1,
], 1);
assert_true($okValid['ok'], 'payload válido pasa reglas (edición id=1)');

$saved = mass_admin_producto_guardar(1, [
    'nombre' => 'Aceite Primor Clásico 900ml',
    'ean' => '7756123489123',
    'unidad' => 'Unidad (Botella 900ml)',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'Aceites y Grasas',
    'descripcion' => 'Aceite de soya y palma en botella PET.',
    'precio' => 7.90,
    'costo' => 5.40,
    'puntoReorden' => 40,
    'capacidadGondola' => 200,
    'abc' => 'A',
    'activo' => '1',
]);
assert_true($saved['ok'], 'guardar producto OK');

$after = mass_admin_producto_por_id(1);
assert_true($after !== null && $after['nombre'] === 'Aceite Primor Clásico 900ml', 'override nombre aplicado');
assert_true($after !== null && (float) $after['precio'] === 7.90, 'override PVP aplicado');
assert_true($after !== null && $after['abc'] === 'A', 'override ABC aplicado');
assert_true($after !== null && abs((float) $after['margenPct'] - 31.65) < 0.1, 'margen calculado');

$bad = mass_admin_producto_guardar(9999, [
    'nombre' => 'Producto fantasma XX',
    'ean' => '7756123489999',
    'unidad' => 'Unidad',
    'categoria' => 'Abarrotes',
    'subcategoria' => 'General',
    'descripcion' => 'Descripción suficiente',
    'precio' => 10,
    'costo' => 5,
    'puntoReorden' => 10,
    'capacidadGondola' => 100,
    'abc' => 'B',
]);
assert_true(!$bad['ok'], 'id inexistente falla');

// Alta inválida no crea
$antes = count(mass_admin_productos());
$altaVacia = mass_admin_producto_crear([
    'nombre' => '',
    'ean' => '',
    'unidad' => '',
    'categoria' => '',
    'subcategoria' => '',
    'descripcion' => '',
    'precio' => 0,
    'costo' => 0,
    'puntoReorden' => 0,
    'capacidadGondola' => 0,
    'abc' => '',
]);
assert_true(!$altaVacia['ok'], 'alta vacía rechazada por reglas');
assert_true(count(mass_admin_productos()) === $antes, 'alta inválida no incrementa catálogo');

$creado = mass_admin_producto_crear([
    'nombre' => 'Yogurt Bebible Fresa 1L',
    'ean' => '7756987456123',
    'unidad' => 'Botella 1L',
    'categoria' => 'Lácteos',
    'subcategoria' => 'Yogures',
    'descripcion' => 'Yogurt bebible sabor fresa listo para góndola.',
    'precio' => 6.50,
    'costo' => 3.80,
    'puntoReorden' => 20,
    'capacidadGondola' => 120,
    'abc' => 'B',
    'activo' => 1,
]);
assert_true($creado['ok'] && $creado['id'] > 0, 'crear producto OK con reglas');
assert_true(count(mass_admin_productos()) === $antes + 1, 'lista incrementa al crear válido');
$nuevo = mass_admin_producto_por_id($creado['id']);
assert_true($nuevo !== null && !empty($nuevo['esNuevo']), 'producto nuevo visible en catálogo');

$dupAlta = mass_admin_producto_crear([
    'nombre' => 'Otro yogurt distinto',
    'ean' => '7756987456123',
    'unidad' => 'Botella 1L',
    'categoria' => 'Lácteos',
    'subcategoria' => 'Yogures',
    'descripcion' => 'Intento de EAN duplicado en alta.',
    'precio' => 6.50,
    'costo' => 3.80,
    'puntoReorden' => 20,
    'capacidadGondola' => 120,
    'abc' => 'B',
]);
assert_true(!$dupAlta['ok'], 'alta con EAN duplicado falla');

echo "\nResultado: $ok OK, $fail FAIL\n";
exit($fail > 0 ? 1 : 0);
