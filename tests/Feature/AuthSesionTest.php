<?php
declare(strict_types=1);

namespace Tests\Feature;

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
    }

    /** // P25 */
    public function test_acceso_sin_autenticar_no_es_admin_ni_cliente(): void
    {
        $this->assertFalse(auth_es_admin());
        $this->assertFalse(auth_es_cliente());
        $this->assertNull(auth_usuario());
    }
}
