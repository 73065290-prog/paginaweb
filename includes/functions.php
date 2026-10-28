<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dinero(float $n): string
{
    return 'S/ ' . number_format($n, 2, '.', '');
}

function producto_por_id(int $id): ?array
{
    foreach (mass_productos() as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function carrito_items(): array
{
    return $_SESSION['carrito'] ?? [];
}

function carrito_total_unidades(): int
{
    $total = 0;
    foreach (carrito_items() as $item) {
        $total += (int) ($item['cantidad'] ?? 0);
    }
    return $total;
}

function carrito_total_monto(): float
{
    $total = 0.0;
    foreach (carrito_items() as $item) {
        $p = producto_por_id((int) $item['id']);
        if ($p) {
            $total += (float) $p['precio'] * (int) $item['cantidad'];
        }
    }
    return $total;
}

function carrito_agregar(int $id, int $cantidad = 1): void
{
    if ($cantidad < 1 || !producto_por_id($id)) {
        return;
    }
    foreach ($_SESSION['carrito'] as &$item) {
        if ((int) $item['id'] === $id) {
            $item['cantidad'] = (int) $item['cantidad'] + $cantidad;
            return;
        }
    }
    unset($item);
    $_SESSION['carrito'][] = ['id' => $id, 'cantidad' => $cantidad];
}

function carrito_cambiar(int $id, int $delta): void
{
    foreach ($_SESSION['carrito'] as $i => $item) {
        if ((int) $item['id'] !== $id) {
            continue;
        }
        $nueva = (int) $item['cantidad'] + $delta;
        if ($nueva <= 0) {
            unset($_SESSION['carrito'][$i]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
        } else {
            $_SESSION['carrito'][$i]['cantidad'] = $nueva;
        }
        return;
    }
}

function carrito_vaciar(): void
{
    $_SESSION['carrito'] = [];
}

function carrito_eliminar(int $id): void
{
    $_SESSION['carrito'] = array_values(array_filter(
        carrito_items(),
        static fn (array $item): bool => (int) $item['id'] !== $id
    ));
}

function carrito_set_cantidad(int $id, int $cantidad): void
{
    if ($cantidad < 1) {
        carrito_eliminar($id);
        return;
    }
    foreach ($_SESSION['carrito'] as $i => $item) {
        if ((int) $item['id'] === $id) {
            $_SESSION['carrito'][$i]['cantidad'] = min(99, $cantidad);
            return;
        }
    }
}

function carrito_guardar_despues(int $id): void
{
    $p = producto_por_id($id);
    if (!$p) {
        return;
    }
    $qty = 1;
    foreach (carrito_items() as $item) {
        if ((int) $item['id'] === $id) {
            $qty = (int) $item['cantidad'];
            break;
        }
    }
    carrito_eliminar($id);
    if (!isset($_SESSION['guardados']) || !is_array($_SESSION['guardados'])) {
        $_SESSION['guardados'] = [];
    }
    foreach ($_SESSION['guardados'] as &$g) {
        if ((int) $g['id'] === $id) {
            $g['cantidad'] = (int) $g['cantidad'] + $qty;
            return;
        }
    }
    unset($g);
    $_SESSION['guardados'][] = ['id' => $id, 'cantidad' => $qty];
}

function pedido_defaults(): array
{
    return [
        'cupon' => '',
        'entrega' => 'retiro',
        'pago' => 'yape',
        'comprobante' => 'boleta',
        'nombre' => '',
        'apellido' => '',
        'documento' => '',
        'telefono' => '',
        'direccion' => 'Av. Brasil 2340, Dpto 802, Magdalena del Mar',
        'referencia' => '',
        'tienda_id' => 'magdalena',
        'horario' => 'hoy_tarde',
    ];
}

function pedido_estado(): array
{
    if (!isset($_SESSION['pedido']) || !is_array($_SESSION['pedido'])) {
        $_SESSION['pedido'] = pedido_defaults();
    }
    return array_merge(pedido_defaults(), $_SESSION['pedido']);
}

function pedido_set(array $data): void
{
    $_SESSION['pedido'] = array_merge(pedido_estado(), $data);
}

/** Normaliza modalidad: solo retiro | express. */
function pedido_normalizar_entrega(string $modo): string
{
    return $modo === 'express' ? 'express' : 'retiro';
}

function pedido_costo_envio(string $modo): float
{
    return pedido_normalizar_entrega($modo) === 'express' ? 4.9 : 0.0;
}

function pedido_descuento_cupon(string $cupon): float
{
    $cupon = strtoupper(trim($cupon));
    return match ($cupon) {
        'CLUBMASS10' => 5.0,
        'CLUBMASS60' => 6.0,
        default => 0.0,
    };
}

/**
 * Valida datos de entrega según modalidad (reglas de negocio).
 *
 * @param array<string, mixed> $data
 * @return array{ok:bool, errores:array<int,string>, data:array<string,mixed>}
 */
function pedido_validar_entrega(array $data): array
{
    $errores = [];
    $entrega = pedido_normalizar_entrega((string) ($data['entrega'] ?? 'retiro'));
    $nombre = trim((string) ($data['nombre'] ?? ''));
    $apellido = trim((string) ($data['apellido'] ?? ''));
    $documento = preg_replace('/\D+/', '', (string) ($data['documento'] ?? '')) ?? '';
    $telefono = trim((string) ($data['telefono'] ?? ''));
    $horario = (string) ($data['horario'] ?? '');
    $horariosOk = array_keys(mass_horarios_entrega($entrega));

    if ($nombre === '' || mb_strlen($nombre) < 2) {
        $errores[] = 'Ingresa tus nombres';
    }
    if ($apellido === '' || mb_strlen($apellido) < 2) {
        $errores[] = 'Ingresa tus apellidos';
    }
    if (strlen($documento) < 8) {
        $errores[] = 'Documento inválido (mín. 8 dígitos)';
    }
    if ($telefono === '' || strlen(preg_replace('/\D+/', '', $telefono) ?? '') < 9) {
        $errores[] = 'Teléfono inválido (mín. 9 dígitos)';
    }
    if ($horario === '' || !in_array($horario, $horariosOk, true)) {
        $errores[] = 'Selecciona un horario válido para ' . ($entrega === 'express' ? 'delivery' : 'retiro');
    }

    $tiendaId = (string) ($data['tienda_id'] ?? 'magdalena');
    $direccion = trim((string) ($data['direccion'] ?? ''));
    $referencia = trim((string) ($data['referencia'] ?? ''));
    $tienda = mass_tienda_por_id($tiendaId);

    if ($entrega === 'retiro') {
        if ($tienda === null) {
            $errores[] = 'Selecciona una tienda Mass para el retiro';
        } elseif (($tienda['estado'] ?? '') === 'agotado' || (int) ($tienda['stock'] ?? 0) <= 0) {
            $errores[] = 'La tienda seleccionada no tiene stock para retiro';
        } else {
            $direccion = (string) $tienda['direccion'];
        }
    } else {
        // Delivery: tienda de origen = misma tienda de inventario (default Magdalena)
        if ($tienda === null || ($tienda['estado'] ?? '') === 'agotado') {
            $tiendaId = 'magdalena';
            $tienda = mass_tienda_por_id($tiendaId);
        }
        if (mb_strlen($direccion) < 10) {
            $errores[] = 'Ingresa una dirección de entrega completa';
        }
    }

    $comprobante = ((string) ($data['comprobante'] ?? 'boleta')) === 'factura' ? 'factura' : 'boleta';

    return [
        'ok' => $errores === [],
        'errores' => $errores,
        'data' => [
            'entrega' => $entrega,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'documento' => $documento,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'referencia' => $referencia,
            'tienda_id' => $tiendaId,
            'horario' => $horario !== '' ? $horario : ($horariosOk[0] ?? 'hoy_tarde'),
            'comprobante' => $comprobante,
        ],
    ];
}

/**
 * Totales del checkout (frontend demo, sin backend).
 *
 * @param array<int, array{id:int|string, cantidad:int|string}>|null $items
 * @param array<string, mixed>|null $pedidoOverride
 * @return array{
 *   items:int,
 *   subtotal:float,
 *   ahorroOfertas:float,
 *   ahorroAprovecha:float,
 *   descuentoCupon:float,
 *   envio:float,
 *   ahorroTotal:float,
 *   total:float,
 *   cupon:string,
 *   entrega:string,
 *   lineas:array<int, array<string, mixed>>
 * }
 */
function pedido_resumen(?array $items = null, ?array $pedidoOverride = null): array
{
    $pedido = $pedidoOverride ?? pedido_estado();
    $lista = $items ?? carrito_items();
    $subtotal = 0.0;
    $ahorroOfertas = 0.0;
    $unidades = 0;
    $lineas = [];

    foreach ($lista as $item) {
        $p = producto_por_id((int) $item['id']);
        if (!$p) {
            continue;
        }
        $qty = (int) $item['cantidad'];
        $unidades += $qty;
        $precio = (float) $p['precio'];
        $subtotal += $precio * $qty;
        if (!empty($p['precioAntes']) && (float) $p['precioAntes'] > $precio) {
            $ahorroOfertas += ((float) $p['precioAntes'] - $precio) * $qty;
        }
        $lineas[] = [
            'id' => (int) $p['id'],
            'nombre' => $p['nombre'],
            'img' => $p['img'],
            'precio' => $precio,
            'cantidad' => $qty,
            'subtotal' => $precio * $qty,
        ];
    }

    $ahorroAprovecha = $unidades >= 3 ? 6.95 : 0.0;
    $cupon = strtoupper(trim((string) ($pedido['cupon'] ?? '')));
    $descuentoCupon = pedido_descuento_cupon($cupon);
    $entrega = pedido_normalizar_entrega((string) ($pedido['entrega'] ?? 'retiro'));
    $envio = pedido_costo_envio($entrega);

    $ahorroTotal = $ahorroOfertas + $ahorroAprovecha + $descuentoCupon;
    $total = max(0, $subtotal - $ahorroAprovecha - $descuentoCupon + $envio);

    return [
        'items' => $unidades,
        'subtotal' => $subtotal,
        'ahorroOfertas' => $ahorroOfertas,
        'ahorroAprovecha' => $ahorroAprovecha,
        'descuentoCupon' => $descuentoCupon,
        'envio' => $envio,
        'ahorroTotal' => $ahorroTotal,
        'total' => $total,
        'cupon' => $cupon,
        'entrega' => $entrega,
        'lineas' => $lineas,
    ];
}

/**
 * Steps de tracking según modalidad (Figma 20-6246).
 *
 * @return array<int, array{key:string, titulo:string, detalle:string, estado:string}>
 */
function pedido_tracking_pasos(string $entrega, string $estadoActual = 'gondola'): array
{
    $entrega = pedido_normalizar_entrega($entrega);
    $orden = $entrega === 'express'
        ? ['confirmado', 'gondola', 'camino', 'entregado']
        : ['confirmado', 'gondola', 'listo', 'retirado'];

    $labels = $entrega === 'express'
        ? [
            'confirmado' => ['Pedido confirmado & pagado', 'Pago verificado correctamente'],
            'gondola' => ['En góndola: separación y empaque', 'Personal shopper en tu tienda Mass'],
            'camino' => ['En camino: Motorizado Express', 'Tu pedido va hacia la dirección indicada'],
            'entregado' => ['Entregado en destino', 'Pedido recibido'],
        ]
        : [
            'confirmado' => ['Pedido confirmado & pagado', 'Pago verificado correctamente'],
            'gondola' => ['En góndola: separación y empaque', 'Preparando tu retiro en tienda'],
            'listo' => ['Listo para retiro', 'Presenta tu QR en caja de retiro'],
            'retirado' => ['Retirado en tienda', 'Pedido entregado en mostrador'],
        ];

    $idxActual = array_search($estadoActual, $orden, true);
    if ($idxActual === false) {
        $idxActual = 1;
    }

    $out = [];
    foreach ($orden as $i => $key) {
        $estado = $i < $idxActual ? 'done' : ($i === $idxActual ? 'current' : 'pending');
        $out[] = [
            'key' => $key,
            'titulo' => $labels[$key][0],
            'detalle' => $labels[$key][1],
            'estado' => $estado,
        ];
    }
    return $out;
}

/**
 * Confirma pedido demo: guarda snapshot y vacía carrito.
 *
 * @return array<string, mixed>|null
 */
function pedido_confirmar(string $pago = 'yape'): ?array
{
    $items = carrito_items();
    if (!$items) {
        return null;
    }

    $pedido = pedido_estado();
    $pago = in_array($pago, ['yape', 'tarjeta', 'contra'], true) ? $pago : 'yape';
    $resumen = pedido_resumen($items, $pedido);
    $tienda = mass_tienda_por_id((string) ($pedido['tienda_id'] ?? 'magdalena'));
    $codigo = 'MASS-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $boleta = 'B004-' . random_int(100, 999);

    $orden = [
        'codigo' => $codigo,
        'boleta' => $boleta,
        'creado' => date('c'),
        'estado' => 'gondola',
        'pago' => $pago,
        'pedido' => $pedido,
        'resumen' => $resumen,
        'tienda' => $tienda,
        'rider' => [
            'nombre' => 'Carlos Huamán',
            'rating' => 4.8,
            'vehiculo' => 'Motorizado Express',
            'telefono' => '999888777',
        ],
        'impacto' => [
            'co2' => 1.0,
            'plastico' => 1.4,
            'puntos' => max(10, (int) round($resumen['total'])),
        ],
    ];

    $_SESSION['ultimo_pedido'] = $orden;
    carrito_vaciar();
    return $orden;
}

function pedido_ultimo(): ?array
{
    $o = $_SESSION['ultimo_pedido'] ?? null;
    return is_array($o) ? $o : null;
}

/** Usuarios demo SGC-Mass (Figma 30-9006). Sin MySQL. */
function mass_usuarios_auth(): array
{
    return [
        [
            'usuario' => 'admin.operaciones@mass.pe',
            'password' => '123456',
            'rol' => 'admin',
            'nombre' => 'Gonzalo Méndez',
            'cargo' => 'Administrador de Tienda',
        ],
        [
            'usuario' => 'admin',
            'password' => '123456',
            'rol' => 'admin',
            'nombre' => 'Gonzalo Méndez',
            'cargo' => 'Administrador WMS',
        ],
        [
            'usuario' => 'proveedor@mass.pe',
            'password' => '123456',
            'rol' => 'cliente',
            'nombre' => 'Ana Proveedor',
            'cargo' => 'Cliente / Proveedor Pedidos',
        ],
        [
            'usuario' => 'cliente',
            'password' => '123456',
            'rol' => 'cliente',
            'nombre' => 'Cliente Demo',
            'cargo' => 'Portal Pedidos',
        ],
    ];
}

/**
 * @return array{ok:bool, errores:array<int,string>, usuario:?array<string,mixed>}
 */
function auth_intentar(string $usuario, string $password, string $rolEsperado = 'admin'): array
{
    $usuario = mb_strtolower(trim($usuario));
    $password = trim($password);
    $rolEsperado = $rolEsperado === 'cliente' ? 'cliente' : 'admin';
    $errores = [];

    if ($usuario === '') {
        $errores[] = 'Ingresa tu correo o usuario corporativo';
    }
    if ($password === '') {
        $errores[] = 'Ingresa tu contraseña';
    }
    if ($errores) {
        return ['ok' => false, 'errores' => $errores, 'usuario' => null];
    }

    foreach (mass_usuarios_auth() as $u) {
        $login = mb_strtolower((string) $u['usuario']);
        $loginShort = explode('@', $login)[0];
        $matchUser = $usuario === $login || $usuario === $loginShort;
        if (!$matchUser || $password !== (string) $u['password']) {
            continue;
        }
        if (($u['rol'] ?? '') !== $rolEsperado) {
            return [
                'ok' => false,
                'errores' => ['Este usuario no corresponde al modo seleccionado (' . $rolEsperado . ')'],
                'usuario' => null,
            ];
        }
        return ['ok' => true, 'errores' => [], 'usuario' => $u];
    }

    return ['ok' => false, 'errores' => ['Credenciales incorrectas'], 'usuario' => null];
}

function auth_login(array $usuario, bool $recordar = false): void
{
    $_SESSION['auth'] = [
        'usuario' => (string) $usuario['usuario'],
        'rol' => (string) $usuario['rol'],
        'nombre' => (string) $usuario['nombre'],
        'cargo' => (string) ($usuario['cargo'] ?? ''),
        'login_at' => date('c'),
        'recordar' => $recordar,
    ];
}

function auth_logout(): void
{
    unset($_SESSION['auth']);
}

function auth_usuario(): ?array
{
    $a = $_SESSION['auth'] ?? null;
    return is_array($a) ? $a : null;
}

function auth_es_admin(): bool
{
    $u = auth_usuario();
    return $u !== null && ($u['rol'] ?? '') === 'admin';
}

function auth_es_cliente(): bool
{
    $u = auth_usuario();
    return $u !== null && ($u['rol'] ?? '') === 'cliente';
}

/** Protege páginas admin: redirige a login si no hay sesión admin. */
function auth_requerir_admin(): void
{
    if (!auth_es_admin()) {
        flash_set('Inicia sesión como Administrador WMS');
        redirect('login.php');
    }
}

function flash_set(string $mensaje): void
{
    $_SESSION['flash'] = $mensaje;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function mass_base(): string
{
    return defined('MASS_BASE_HREF') ? MASS_BASE_HREF : '';
}

/** Ruta a asset o recurso desde la carpeta del script actual. */
function mass_asset(string $path): string
{
    return mass_base() . ltrim($path, '/');
}

/** URL relativa a una página de la tienda (cliente_interfaces). */
function mass_cliente_url(string $page = 'index.php'): string
{
    $page = ltrim($page, '/');
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_ends_with($scriptDir, '/cliente_interfaces')) {
        return $page;
    }
    if (str_ends_with($scriptDir, '/admin')) {
        return '../cliente_interfaces/' . $page;
    }
    return 'cliente_interfaces/' . $page;
}

function pagina_actual(): string
{
    return basename($_SERVER['PHP_SELF'] ?? 'index.php');
}

/**
 * @param array<string, mixed> $filtros
 * @return array<int, array<string, mixed>>
 */
function filtrar_productos(array $filtros): array
{
    $lista = mass_productos();
    $cat = $filtros['cat'] ?? 'todos';
    $q = mb_strtolower(trim((string) ($filtros['q'] ?? '')));
    $min = (float) ($filtros['min'] ?? 0);
    $max = (float) ($filtros['max'] ?? 999);
    $marcas = $filtros['marcas'] ?? [];
    $chip = $filtros['chip'] ?? 'todos';
    $orden = $filtros['orden'] ?? 'relevancia';
    $stockOk = !empty($filtros['stock_ok']);
    $stockBajo = !empty($filtros['stock_bajo']);

    $lista = array_values(array_filter($lista, static function (array $p) use ($cat, $q, $min, $max, $marcas, $chip, $stockOk, $stockBajo): bool {
        if ($cat !== 'todos' && $p['categoria'] !== $cat) {
            return false;
        }
        if ($q !== '') {
            $hay = mb_strtolower($p['nombre'] . ' ' . $p['marca'] . ' ' . $p['categoria']);
            if (!str_contains($hay, $q)) {
                return false;
            }
        }
        if ((float) $p['precio'] < $min || (float) $p['precio'] > $max) {
            return false;
        }
        if ($marcas && !in_array($p['marca'], $marcas, true)) {
            return false;
        }
        if ($p['stockTone'] === 'ok' && !$stockOk) {
            return false;
        }
        if ($p['stockTone'] === 'bajo' && !$stockBajo) {
            return false;
        }
        if ($chip === 'precio_bajo' && (float) $p['precio'] > 10) {
            return false;
        }
        if ($chip === 'ofertas') {
            $badge = $p['badge']['text'] ?? '';
            if ($badge === '' || !preg_match('/OFERTA|AHORRO|-/i', $badge)) {
                return false;
            }
        }
        if ($chip === 'vendidos') {
            $badge = $p['badge']['text'] ?? '';
            if ($badge === '' || !preg_match('/VENDIDO|AHORRO/i', $badge)) {
                return false;
            }
        }
        return true;
    }));

    usort($lista, static function (array $a, array $b) use ($orden): int {
        return match ($orden) {
            'precio_asc' => $a['precio'] <=> $b['precio'],
            'precio_desc' => $b['precio'] <=> $a['precio'],
            'nombre' => strcmp($a['nombre'], $b['nombre']),
            default => 0,
        };
    });

    return $lista;
}

function render_product_card(array $p): void
{
    require __DIR__ . '/components/product-card.php';
}

function render_category_card(array $c): void
{
    require __DIR__ . '/components/category-card.php';
}

function render_store_card(array $tienda): void
{
    require __DIR__ . '/components/store-card.php';
}

/**
 * @return array<int, array<string, mixed>>
 */
function productos_por_ids(array $ids): array
{
    $map = [];
    foreach (mass_productos() as $p) {
        $map[(int) $p['id']] = $p;
    }
    $out = [];
    foreach ($ids as $id) {
        $id = (int) $id;
        if (isset($map[$id])) {
            $out[] = $map[$id];
        }
    }
    return $out;
}
