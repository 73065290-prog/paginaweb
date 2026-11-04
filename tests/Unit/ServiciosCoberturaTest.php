<?php
declare(strict_types=1);

namespace Tests\Unit;

use AuthService;
use CarritoService;
use CuponService;
use Database;
use InventarioService;
use PedidoService;
use ProductoService;
use Tests\TestCase;

/**
 * Cubre servicios de backend que la suite PHPUnit no ejecutaba
 * (el catálogo demo, el alta/edición admin, el inventario y la sesión).
 * MASS_USE_DB queda en 0: no escribe en MySQL de desarrollo ni de pruebas.
 */
final class ServiciosCoberturaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        putenv('MASS_USE_DB=0');
        Database::reset();
    }

    protected function tearDown(): void
    {
        putenv('MASS_USE_DB=0');
        Database::reset();
        parent::tearDown();
    }

    /** @return array<string, mixed> */
    private function payloadNuevo(array $override = []): array
    {
        return array_merge([
            'nombre' => 'Galleta "Soda" D\'Angelo QA',
            'ean' => '7756123491111',
            'unidad' => 'Paquete 6 und',
            'categoria' => 'Snacks',
            'subcategoria' => 'Galletas',
            'descripcion' => 'Alta de laboratorio con comillas y apóstrofe.',
            'marca' => 'D\'ANGELO',
            'precio' => 4.50,
            'costo' => 2.10,
            'puntoReorden' => 8,
            'capacidadGondola' => 40,
            'abc' => 'B',
            'activo' => 1,
        ], $override);
    }

    public function test_catalogo_sin_mysql_usa_fallback_y_busca_por_id(): void
    {
        $this->assertFalse(Database::connected());
        $service = new ProductoService();

        $fallback = [['id' => 'lab', 'nombre' => 'Categoría D\'Onofrio']];
        $this->assertSame($fallback, $service->listarCategorias($fallback));
        $this->assertSame([], $service->listarCatalogo());

        $uno = $service->obtenerPorId(1);
        $this->assertNotNull($uno);
        $this->assertSame('PRIMOR', $uno['marca']);
        $this->assertNull($service->obtenerPorId(999999));

        $varios = $service->obtenerPorIds([1, '2', 999999, 1]);
        $this->assertCount(3, $varios);
        $this->assertSame(1, $varios[0]['id']);
        $this->assertSame(2, $varios[1]['id']);
        $this->assertSame(1, $varios[2]['id']);
    }

    public function test_filtrar_catalogo_respeta_texto_acentos_precio_oferta_y_comillas(): void
    {
        $service = new ProductoService();
        $base = [
            'stock_ok' => true,
            'stock_bajo' => true,
            'max' => 9999,
        ];

        $ofertas = $service->filtrarCatalogo($base + [
            'q' => 'clásico',
            'chip' => 'ofertas',
        ]);
        $this->assertNotEmpty($ofertas);
        foreach ($ofertas as $p) {
            $hay = mb_strtolower($p['nombre'] . ' ' . $p['marca'] . ' ' . $p['categoria']);
            $this->assertStringContainsString('clásico', $hay);
            $badge = (string) ($p['badge']['text'] ?? '');
            $this->assertMatchesRegularExpression('/OFERTA|AHORRO|-/i', $badge);
        }

        $asc = $service->filtrarCatalogo($base + ['orden' => 'precio_asc', 'cat' => 'Abarrotes']);
        $precios = array_map(static fn (array $p): float => (float) $p['precio'], $asc);
        $ordenados = $precios;
        sort($ordenados);
        $this->assertSame($ordenados, $precios);
        $this->assertNotEmpty($asc);
        foreach ($asc as $p) {
            $this->assertSame('Abarrotes', $p['categoria']);
        }

        $this->assertSame([], $service->filtrarCatalogo($base + [
            'q' => 'o\'higgins "drop table"',
        ]));
    }

    public function test_crear_y_guardar_admin_persisten_en_sesion_y_rechazan_datos_invalidos(): void
    {
        $service = new ProductoService();
        $invalido = $service->crearAdmin($this->payloadNuevo([
            'nombre' => 'Ab',
            'ean' => '12',
        ]));
        $this->assertFalse($invalido['ok']);
        $this->assertSame(0, $invalido['id']);
        $this->assertNotEmpty($invalido['errores']);

        $alta = $service->crearAdmin($this->payloadNuevo());
        $this->assertTrue($alta['ok'], implode(' · ', $alta['errores']));
        $this->assertGreaterThan(0, $alta['id']);

        $lista = $service->listarAdmin();
        $creado = null;
        foreach ($lista as $fila) {
            if ((int) $fila['id'] === $alta['id']) {
                $creado = $fila;
            }
        }
        $this->assertNotNull($creado);
        $this->assertSame('Galleta "Soda" D\'Angelo QA', $creado['nombre']);
        $this->assertSame('D\'ANGELO', $creado['marca']);

        $filtrado = $service->listarAdminFiltrado('d\'angelo', 'b', 'todos', 1, 100);
        $this->assertSame('B', $filtrado['filtroAbc']);
        $this->assertGreaterThanOrEqual(1, $filtrado['total']);
        $ids = array_map(static fn (array $p): int => (int) $p['id'], $filtrado['items']);
        $this->assertContains($alta['id'], $ids);

        $fuera = $service->listarAdminFiltrado('', 'zzz', 'todos', 999, 8);
        $this->assertSame('todos', $fuera['filtroAbc']);
        $this->assertSame($fuera['totalPaginas'], $fuera['pagina']);

        $editado = $service->guardarAdmin($alta['id'], $this->payloadNuevo([
            'nombre' => 'Galleta "Soda" D\'Angelo Editada',
            'precio' => 5.20,
        ]));
        $this->assertTrue($editado['ok'], implode(' · ', $editado['errores']));
        $otraVez = $service->obtenerAdminPorId($alta['id']);
        $this->assertNotNull($otraVez);
        $this->assertSame('Galleta "Soda" D\'Angelo Editada', $otraVez['nombre']);
        $this->assertSame(5.20, $otraVez['precio']);

        $ausente = $service->guardarAdmin(999999, $this->payloadNuevo([
            'nombre' => 'Producto que no existe QA',
            'ean' => '7756123492222',
        ]));
        $this->assertFalse($ausente['ok']);
        $this->assertContains('Producto no encontrado', $ausente['errores']);
    }

    public function test_modal_admin_etiquetas_y_rotacion(): void
    {
        $service = new ProductoService();
        $alta = $service->prepararModalAdmin(true, 0, [
            'nombre' => 'Sticky "ñandú"',
            'ean' => '7756123493333',
            'unidad' => 'Unidad',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'General',
            'descripcion' => 'Descripción sticky de más de diez caracteres.',
            'precio' => 3,
            'costo' => 1,
            'puntoReorden' => 2,
            'capacidadGondola' => 10,
            'abc' => 'C',
            'activo' => 1,
        ]);
        $this->assertTrue($alta['esAlta']);
        $this->assertSame('Sticky "ñandú"', $alta['editando']['nombre']);
        $this->assertSame(0, $alta['editando']['id']);

        $edicion = $service->prepararModalAdmin(false, 1);
        $this->assertFalse($edicion['esAlta']);
        $this->assertNotNull($edicion['editando']);
        $this->assertSame(1, $edicion['editando']['id']);

        $vacio = $service->prepararModalAdmin(false, 0);
        $this->assertNull($vacio['editando']);

        $this->assertSame('Stock bajo en góndola', $service->etiquetaStockGondola('bajo'));
        $this->assertSame('Quiebre / crítico', $service->etiquetaStockGondola('critico'));
        $this->assertSame('Nivel estable', $service->etiquetaStockGondola('ok'));
        $this->assertNotEmpty($service->obtenerTopRotacion());
        $this->assertNotSame([], $service->plantillaNuevaAdmin());
    }

    public function test_inventario_sin_mysql_no_escribe_stock_y_filtra_por_texto(): void
    {
        $service = new InventarioService();
        $this->assertFalse($service->actualizarStockPorTienda(1, 'magdalena', 3, 4));
        $this->assertSame([], $service->obtenerStockPorTienda('magdalena'));
        $this->assertSame([], $service->listarTiendas());

        $critico = $service->evaluarReorden(0, 4, 25, 100);
        $this->assertSame('critico', $critico['estado']);
        $this->assertTrue($critico['requiereReorden']);
        $this->assertSame(100, $critico['cantidadSugerida']);

        $ok = $service->evaluarReorden(40, 10, 25, 100);
        $this->assertSame('ok', $ok['estado']);
        $this->assertFalse($ok['requiereReorden']);
        $this->assertSame(0, $ok['cantidadSugerida']);

        $filas = $service->listarInventarioFiltrado('primor', 'ok');
        $this->assertNotEmpty($filas);
        foreach ($filas as $fila) {
            $hay = mb_strtolower($fila['nombre'] . ' ' . $fila['sku'] . ' ' . $fila['marca']);
            $this->assertStringContainsString('primor', $hay);
            $this->assertSame('ok', $fila['estado']);
        }
        $this->assertSame([], $service->listarInventarioFiltrado('o\'higgins "no-existe"', 'critico'));

        $this->assertSame('Stock OK', $service->etiquetaEstado('ok'));
        $this->assertSame('Bajo', $service->etiquetaEstado('bajo'));
        $this->assertSame('Crítico', $service->etiquetaEstado('otro'));
        $this->assertArrayHasKey('ventaDia', $service->obtenerKpis());
        $this->assertCount(3, $service->obtenerStockGlobal());
        $this->assertNotEmpty($service->obtenerAlertasGondola());
        $this->assertNotEmpty($service->listarGondola());
    }

    public function test_auth_normaliza_rol_destinos_y_cierra_sesion_al_cambiar(): void
    {
        $service = new AuthService();
        $this->assertSame(AuthService::ROL_CLIENTE, $service->normalizarRol('cliente'));
        $this->assertSame(AuthService::ROL_ADMIN, $service->normalizarRol('super'));
        $this->assertSame('admin/login.php', $service->logoutDestino(true));
        $this->assertSame('cliente_interfaces/index.php', $service->logoutDestino(false));
        $this->assertSame('../cliente_interfaces/pedido.php?demo=1', $service->loginDestino('cliente'));
        $this->assertSame('index.php', $service->loginDestino('admin'));

        $sinSesion = $service->prepararCambioRol('cliente');
        $this->assertSame('mostrar', $sinSesion['accion']);
        $this->assertNull($sinSesion['redirect']);

        $service->login([
            'usuario' => 'admin',
            'rol' => AuthService::ROL_ADMIN,
            'nombre' => 'Gonzalo Méndez',
            'cargo' => 'WMS',
        ]);
        $this->assertTrue($service->esAdmin());
        $mismo = $service->prepararCambioRol('admin');
        $this->assertSame('redirect', $mismo['accion']);
        $this->assertSame('index.php', $mismo['redirect']);
        $this->assertTrue($service->esAdmin());

        $cambio = $service->prepararCambioRol('cliente');
        $this->assertSame('cambio_rol', $cambio['accion']);
        $this->assertStringContainsString('Sesión anterior cerrada', (string) $cambio['mensaje']);
        $this->assertNull($service->usuarioActual());
        $this->assertFalse($service->esAdmin());
        $this->assertFalse($service->esCliente());
    }

    public function test_carrito_capa_en_99_y_pedido_vacio_no_confirma(): void
    {
        $carrito = new CarritoService();
        $this->assertSame(99, $carrito->cantidadMaxima());
        $carrito->agregar(1, 150);
        $this->assertSame(99, $carrito->items()[0]['cantidad']);
        $carrito->agregar(1, 5);
        $this->assertSame(99, $carrito->items()[0]['cantidad']);
        $this->assertGreaterThan(0, $carrito->stockDisponible(1));
        $this->assertSame(0, $carrito->stockDisponible(999999));
        $carrito->vaciar();
        $this->assertSame([], $carrito->items());

        $pedido = new PedidoService();
        $this->assertNull($pedido->confirmar('yape'));
        $codigo = $pedido->generarCodigo();
        $this->assertMatchesRegularExpression('/^MASS-[A-F0-9]{6}$/', $codigo);
        $this->assertNotSame($codigo, $pedido->generarCodigo());
    }

    public function test_cupon_vacio_se_quita_y_el_codigo_con_espacios_se_normaliza(): void
    {
        $service = new CuponService();
        $quitado = $service->evaluarAplicacion("  \t ");
        $this->assertTrue($quitado['ok']);
        $this->assertSame('', $quitado['codigo']);
        $this->assertSame(0.0, $quitado['descuento']);
        $this->assertSame('Cupón quitado', $quitado['mensaje']);

        $aplicado = $service->evaluarAplicacion("  clubmass60 \n");
        $this->assertTrue($aplicado['ok']);
        $this->assertSame('CLUBMASS60', $aplicado['codigo']);
        $this->assertSame(6.0, $aplicado['descuento']);
        $this->assertStringContainsString('CLUBMASS60', $aplicado['mensaje']);

        $this->assertFalse($service->esValido("o'higgins"));
        $this->assertSame(0.0, $service->descuento("o'higgins"));
    }
}
