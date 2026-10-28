<?php
declare(strict_types=1);

namespace Tests\Feature;

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

    public function test_logout_admin_limpia_sesion_y_permite_login_cliente(): void
    {
        $login = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($login['ok']);
        auth_login($login['usuario']);
        $this->assertTrue(auth_es_admin());

        auth_logout();
        $this->assertNull(auth_usuario());
        $this->assertFalse(auth_es_admin());

        $cliente = auth_intentar('cliente', '123456', 'cliente');
        $this->assertTrue($cliente['ok']);
        auth_login($cliente['usuario']);
        $this->assertTrue(auth_es_cliente());
        $this->assertFalse(auth_es_admin());
    }

    public function test_cambiar_de_admin_a_cliente_reemplaza_sesion(): void
    {
        $admin = auth_intentar('admin', '123456', 'admin');
        auth_login($admin['usuario']);
        $this->assertTrue(auth_es_admin());

        auth_logout();
        $cliente = auth_intentar('cliente', '123456', 'cliente');
        auth_login($cliente['usuario']);

        $this->assertTrue(auth_es_cliente());
        $this->assertSame('cliente', auth_usuario()['rol'] ?? '');
    }
}
