<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * POST real de admin/productos.php sin token válido.
 * La página rechaza con el mensaje de sesión inválida y no persiste.
 */
final class CsrfAdminProductosTest extends TestCase
{
    private const MENSAJE_RECHAZO = 'Sesión inválida. Recarga la página e intenta de nuevo.';

    protected function setUp(): void
    {
        parent::setUp();
        putenv('MASS_USE_DB=0');
        $_ENV['MASS_USE_DB'] = '0';
        $_SERVER['MASS_USE_DB'] = '0';
        \Database::reset();
        if (\Database::shouldUseDb() || \Database::connected()) {
            $this->fail('MySQL sigue activo. No se envía el POST para no escribir en una base real.');
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $login = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($login['ok'], 'No se abrió la sesión admin de prueba.');
        auth_login($login['usuario']);
        csrf_token();
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        parent::tearDown();
    }

    public function test_alta_sin_token_es_rechazada_y_no_crea_registro(): void
    {
        $ean = '7756999000023';
        $nombre = 'CSRF Alta Rechazada QA';
        $antes = $this->huellaCatalogo();

        $html = $this->enviar([
            'accion' => 'crear_producto',
            'nombre' => $nombre,
            'ean' => $ean,
            'unidad' => 'Bolsa',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'Snacks',
            'descripcion' => 'Alta rechazada por ausencia de token CSRF.',
            'precio' => '4.50',
            'costo' => '2.00',
            'puntoReorden' => '10',
            'capacidadGondola' => '80',
            'abc' => 'C',
            'activo' => '1',
            'marca' => 'MASS',
        ], ['nuevo' => '1']);

        $this->assertStringContainsString(self::MENSAJE_RECHAZO, $html);
        $this->assertStringNotContainsString($nombre, $html);
        $this->assertFalse($this->catalogoContiene($ean, $nombre));
        $this->assertSame($antes, $this->huellaCatalogo());
        $this->assertFalse(\Database::connected());
    }

    public function test_alta_con_token_invalido_es_rechazada_y_no_crea_registro(): void
    {
        $ean = '7756999000024';
        $nombre = 'CSRF Alta Token Invalido QA';
        $antes = $this->huellaCatalogo();

        $html = $this->enviar([
            'accion' => 'crear_producto',
            'csrf_token' => 'token-invalido',
            'nombre' => $nombre,
            'ean' => $ean,
            'unidad' => 'Bolsa',
            'categoria' => 'Abarrotes',
            'subcategoria' => 'Snacks',
            'descripcion' => 'Alta rechazada por token CSRF inválido.',
            'precio' => '4.50',
            'costo' => '2.00',
            'puntoReorden' => '10',
            'capacidadGondola' => '80',
            'abc' => 'C',
            'activo' => '1',
            'marca' => 'MASS',
        ], ['nuevo' => '1']);

        $this->assertStringContainsString(self::MENSAJE_RECHAZO, $html);
        $this->assertFalse($this->catalogoContiene($ean, $nombre));
        $this->assertSame($antes, $this->huellaCatalogo());
        $this->assertFalse(\Database::connected());
    }

    public function test_edicion_sin_token_es_rechazada_y_no_modifica_registro(): void
    {
        $id = 1;
        $original = mass_admin_producto_por_id($id);
        $this->assertNotNull($original);
        $antes = $this->huellaCatalogo();
        $nombreAtaque = 'CSRF Edicion Rechazada QA';

        $html = $this->enviar($this->payloadEdicion($original, $nombreAtaque), ['editar' => (string) $id]);

        $this->assertStringContainsString(self::MENSAJE_RECHAZO, $html);
        $this->assertStringNotContainsString($nombreAtaque, $html);
        $despues = mass_admin_producto_por_id($id);
        $this->assertNotNull($despues);
        $this->assertSame((string) $original['nombre'], (string) $despues['nombre']);
        $this->assertSame((string) $original['ean'], (string) $despues['ean']);
        $this->assertSame((float) $original['precio'], (float) $despues['precio']);
        $this->assertSame($antes, $this->huellaCatalogo());
        $this->assertFalse(\Database::connected());
    }

    public function test_edicion_con_token_invalido_es_rechazada_y_no_modifica_registro(): void
    {
        $id = 1;
        $original = mass_admin_producto_por_id($id);
        $this->assertNotNull($original);
        $antes = $this->huellaCatalogo();
        $nombreAtaque = 'CSRF Edicion Token Invalido QA';
        $post = $this->payloadEdicion($original, $nombreAtaque);
        $post['csrf_token'] = 'token-invalido';

        $html = $this->enviar($post, ['editar' => (string) $id]);

        $this->assertStringContainsString(self::MENSAJE_RECHAZO, $html);
        $despues = mass_admin_producto_por_id($id);
        $this->assertNotNull($despues);
        $this->assertSame((string) $original['nombre'], (string) $despues['nombre']);
        $this->assertSame((string) $original['ean'], (string) $despues['ean']);
        $this->assertSame($antes, $this->huellaCatalogo());
        $this->assertFalse(\Database::connected());
    }

    /**
     * @param array<string, string> $post
     * @param array<string, string> $get
     */
    private function enviar(array $post, array $get): string
    {
        $_GET = $get;
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SCRIPT_NAME'] = '/paginaweb/admin/productos.php';

        ob_start();
        require \MASS_ROOT . '/admin/productos.php';
        $html = ob_get_clean();

        return is_string($html) ? $html : '';
    }

    /**
     * @param array<string, mixed> $original
     * @return array<string, string>
     */
    private function payloadEdicion(array $original, string $nombre): array
    {
        return [
            'accion' => 'guardar_producto',
            'id' => (string) ($original['id'] ?? '1'),
            'nombre' => $nombre,
            'ean' => (string) $original['ean'],
            'unidad' => (string) ($original['unidad'] ?? 'Unidad'),
            'categoria' => (string) ($original['categoria'] ?? 'Abarrotes'),
            'subcategoria' => (string) ($original['subcategoria'] ?? 'Aceites'),
            'descripcion' => (string) (($original['descripcion'] ?? '') !== ''
                ? $original['descripcion']
                : 'Edición rechazada por la protección CSRF del admin.'),
            'precio' => number_format((float) $original['precio'], 2, '.', ''),
            'costo' => number_format(max(0.01, (float) $original['precio'] - 0.50), 2, '.', ''),
            'puntoReorden' => (string) max(1, (int) ($original['puntoReorden'] ?? 10)),
            'capacidadGondola' => (string) max(2, (int) ($original['capacidadGondola'] ?? 20)),
            'abc' => in_array((string) ($original['abc'] ?? ''), ['A', 'B', 'C'], true)
                ? (string) $original['abc']
                : 'C',
            'activo' => '1',
            'marca' => (string) (($original['marca'] ?? '') !== '' ? $original['marca'] : 'MASS'),
        ];
    }

    /**
     * @return array{total:int, extras:int, overrides:string}
     */
    private function huellaCatalogo(): array
    {
        $overrides = $_SESSION['admin_producto_overrides'] ?? [];
        if (!is_array($overrides)) {
            $overrides = [];
        }
        $extras = $_SESSION['admin_productos_extra'] ?? [];
        if (!is_array($extras)) {
            $extras = [];
        }

        return [
            'total' => count(mass_admin_productos()),
            'extras' => count($extras),
            'overrides' => json_encode($overrides) ?: '',
        ];
    }

    private function catalogoContiene(string $ean, string $nombre): bool
    {
        foreach (mass_admin_productos() as $producto) {
            if ((string) ($producto['ean'] ?? '') === $ean || (string) ($producto['nombre'] ?? '') === $nombre) {
                return true;
            }
        }
        return false;
    }
}
