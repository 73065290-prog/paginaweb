<?php
declare(strict_types=1);

namespace Tests\Feature;

use AuthController;
use AuthService;
use Tests\TestCase;

final class AuthLogoutCambioRolTest extends TestCase
{
    public function test_mass_asset_no_rompe_rutas_de_logout_ni_admin(): void
    {
        if (!defined('MASS_BASE_HREF')) {
            define('MASS_BASE_HREF', '../');
        }

        $this->assertSame('../logout.php', mass_url('logout.php'));
        $this->assertSame('../admin/login.php', mass_url('admin/login.php'));
        $this->assertSame('../logout.php', mass_asset('logout.php'));
        $this->assertStringContainsString('frontend/assets/css/styles.css', mass_asset('assets/css/styles.css'));
        $this->assertStringNotContainsString('frontend/assets/logout.php', mass_asset('logout.php'));
    }

    public function test_escenario_a_logout_admin_redirige_a_admin_login(): void
    {
        $controller = new AuthController(new AuthService());
        $login = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($login['ok']);
        auth_login($login['usuario']);
        $this->assertTrue(auth_es_admin());

        $resLogout = $controller->logout();
        $destino = $resLogout['redirect'];

        $this->assertNull(auth_usuario());
        $this->assertFalse(auth_es_admin());
        $this->assertSame('admin/login.php', $destino);
        $this->assertSame('admin/login.php', auth_logout_destino(true));
        $this->assertSame('Sesión cerrada correctamente', $_SESSION['flash'] ?? '');
    }

    public function test_escenario_b_logout_cliente_redirige_a_cliente_index(): void
    {
        $controller = new AuthController(new AuthService());
        $cliente = auth_intentar('cliente', '123456', 'cliente');
        $this->assertTrue($cliente['ok']);
        auth_login($cliente['usuario']);
        $this->assertTrue(auth_es_cliente());

        $resLogout = $controller->logout();
        $destino = $resLogout['redirect'];

        $this->assertNull(auth_usuario());
        $this->assertFalse(auth_es_cliente());
        $this->assertSame('cliente_interfaces/index.php', $destino);
        $this->assertSame('cliente_interfaces/index.php', auth_logout_destino(false));
        $this->assertSame('Sesión cerrada correctamente', $_SESSION['flash'] ?? '');
    }

    public function test_escenario_c_cambiar_de_rol_cierra_sesion_anterior_y_establece_nueva(): void
    {
        $controller = new AuthController(new AuthService());

        // 1. Admin activo -> cambio de rol en vista a cliente cierra sesión previa
        $admin = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($admin['ok']);
        auth_login($admin['usuario']);
        $this->assertTrue(auth_es_admin());

        $prep = $controller->cambioRol('cliente');
        $this->assertSame('cambio_rol', $prep['accion']);
        $this->assertSame('Sesión anterior cerrada. Inicia sesión con el rol seleccionado.', $prep['mensaje']);
        $this->assertNull(auth_usuario());

        // 2. Autenticación en el nuevo rol (Cliente) mediante AuthController::cambioRol
        $cambioCliente = $controller->cambioRol('cliente', 'cliente', '123456');
        $this->assertTrue($cambioCliente['ok']);
        $this->assertTrue(auth_es_cliente());
        $this->assertFalse(auth_es_admin());
        $this->assertSame('cliente', auth_usuario()['rol'] ?? '');
        $this->assertSame('../cliente_interfaces/pedido.php?demo=1', $cambioCliente['redirect']);

        // 3. Cliente -> Admin mediante AuthController::cambioRol (cierra sesión cliente e inicia admin)
        $cambioAdmin = $controller->cambioRol('admin', 'admin', '123456');
        $this->assertTrue($cambioAdmin['ok']);
        $this->assertTrue(auth_es_admin());
        $this->assertFalse(auth_es_cliente());
        $this->assertSame('admin', auth_usuario()['rol'] ?? '');
        $this->assertSame('index.php', $cambioAdmin['redirect']);
    }
}
