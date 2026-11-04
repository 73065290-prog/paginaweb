<?php
declare(strict_types=1);

namespace Tests\Feature;

use AuthController;
use AuthService;
use Tests\TestCase;

final class AuthSesionTest extends TestCase
{
    /** // P24 */
    public function test_login_admin_persiste_y_logout_limpia_sesion(): void
    {
        $r = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($r['ok']);
        auth_login($r['usuario']);
        $this->assertTrue(auth_es_admin());
        $this->assertNotNull(auth_usuario());
        auth_logout();
        $this->assertNull(auth_usuario());
        $this->assertFalse(auth_es_admin());

        // Verificación mediante AuthController y AuthService
        $controller = new AuthController(new AuthService());
        $token = csrf_token();
        $resLogin = $controller->login([
            'usuario' => 'admin',
            'password' => '123456',
            'rol' => 'admin',
            'recordar' => '1',
            'csrf_token' => $token,
        ], 'admin');
        $this->assertTrue($resLogin['ok']);
        $this->assertSame('index.php', $resLogin['redirect']);
        $this->assertTrue(auth_es_admin());
        $this->assertTrue((bool) (auth_usuario()['recordar'] ?? false));

        $resLogout = $controller->logout();
        $this->assertTrue($resLogout['ok']);
        $this->assertTrue($resLogout['era_admin']);
        $this->assertSame('admin/login.php', $resLogout['redirect']);
        $this->assertNull(auth_usuario());
    }

    /** // P25 */
    public function test_acceso_sin_autenticar_no_es_admin_ni_cliente(): void
    {
        $service = new AuthService();
        $this->assertFalse(auth_es_admin());
        $this->assertFalse(auth_es_cliente());
        $this->assertNull(auth_usuario());
        $this->assertFalse($service->esAdmin());
        $this->assertFalse($service->esCliente());
        $this->assertNull($service->usuarioActual());
    }
}
