<?php
declare(strict_types=1);

namespace Tests\Unit;

use AuthService;
use Tests\TestCase;
use UsuarioRepository;

final class AuthReglasTest extends TestCase
{
    /** // P11 */
    public function test_auth_falla_con_credenciales_vacias(): void
    {
        $service = new AuthService();
        $rService = $service->intentar('', '', 'admin');
        $this->assertFalse($rService['ok']);
        $this->assertGreaterThanOrEqual(2, count($rService['errores']));
        $this->assertContains('Ingresa tu correo o usuario corporativo', $rService['errores']);
        $this->assertContains('Ingresa tu contraseña', $rService['errores']);

        // Compatibilidad de función delegadora
        $r = auth_intentar('', '', 'admin');
        $this->assertFalse($r['ok']);
        $this->assertGreaterThanOrEqual(2, count($r['errores']));
    }

    /** // P12 */
    public function test_auth_admin_ok_con_password_demo(): void
    {
        $service = new AuthService(new UsuarioRepository());
        $rService = $service->intentar('admin', '123456', 'admin');
        $this->assertTrue($rService['ok']);
        $this->assertSame('admin', $rService['usuario']['rol'] ?? null);
        $this->assertArrayNotHasKey('password_hash', $rService['usuario'] ?? []);

        // Compatibilidad de función delegadora y alias attempt()
        $r = auth_intentar('admin', '123456', 'admin');
        $this->assertTrue($r['ok']);
        $this->assertSame('admin', $r['usuario']['rol'] ?? null);
        $this->assertTrue($service->attempt('admin.operaciones@mass.pe', '123456', 'admin')['ok']);
    }

    /** // P13 */
    public function test_auth_admin_no_entra_como_cliente(): void
    {
        $service = new AuthService();
        $rService = $service->intentar('admin', '123456', 'cliente');
        $this->assertFalse($rService['ok']);
        $this->assertStringContainsString('no corresponde al modo seleccionado (cliente)', implode(' ', $rService['errores']));

        $r = auth_intentar('admin', '123456', 'cliente');
        $this->assertFalse($r['ok']);
    }

    /** // P14 */
    public function test_auth_cliente_ok_y_no_es_admin(): void
    {
        $service = new AuthService();
        $r = auth_intentar('cliente', '123456', 'cliente');
        $this->assertTrue($r['ok']);
        auth_login($r['usuario']);
        $this->assertTrue(auth_es_cliente());
        $this->assertFalse(auth_es_admin());
        $this->assertTrue($service->esCliente());
        $this->assertFalse($service->esAdmin());
    }

    /** // P15 */
    public function test_auth_password_incorrecta_falla(): void
    {
        $service = new AuthService();
        $rService = $service->intentar('admin', 'wrong-password', 'admin');
        $this->assertFalse($rService['ok']);
        $this->assertNull($rService['usuario']);
        $this->assertContains('Credenciales incorrectas', $rService['errores']);

        $r = auth_intentar('admin', 'wrong-password', 'admin');
        $this->assertFalse($r['ok']);
        $this->assertNull($r['usuario']);
    }
}
