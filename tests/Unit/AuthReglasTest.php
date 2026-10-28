<?php
declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class AuthReglasTest extends TestCase
{
    /** // P11 */
    public function test_auth_falla_con_credenciales_vacias(): void
    {
        $r = auth_intentar('', '', 'admin');
        $this->assertFalse($r['ok']);
        $this->assertGreaterThanOrEqual(2, count($r['errores']));
    }

    /** // P12 */
    public function test_auth_admin_ok_con_password_demo(): void
    {
        $r = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($r['ok']);
        $this->assertSame('admin', $r['usuario']['rol'] ?? null);
    }

    /** // P13 */
    public function test_auth_admin_no_entra_como_cliente(): void
    {
        $r = auth_intentar('admin', '123456', 'cliente');
        $this->assertFalse($r['ok']);
    }

    /** // P14 */
    public function test_auth_cliente_ok_y_no_es_admin(): void
    {
        $r = auth_intentar('cliente', '123456', 'cliente');
        $this->assertTrue($r['ok']);
        auth_login($r['usuario']);
        $this->assertTrue(auth_es_cliente());
        $this->assertFalse(auth_es_admin());
        $this->assertFalse(auth_es_proveedor());
    }

    /** // P15 */
    public function test_auth_password_incorrecta_falla(): void
    {
        $r = auth_intentar('admin', 'wrong-password', 'admin');
        $this->assertFalse($r['ok']);
        $this->assertNull($r['usuario']);
    }

    /** // P16 */
    public function test_auth_proveedor_separado_de_cliente(): void
    {
        $cruzado = auth_intentar('proveedor', '123456', 'cliente');
        $this->assertFalse($cruzado['ok']);
        $r = auth_intentar('proveedor', '123456', 'proveedor');
        $this->assertTrue($r['ok']);
        auth_login($r['usuario']);
        $this->assertTrue(auth_es_proveedor());
        $this->assertFalse(auth_es_cliente());
    }
}
