<?php
declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Usuario;

final class ModelsUsuarioTest extends TestCase
{
    public function test_creacion_directa_y_valores_por_defecto(): void
    {
        $user = new Usuario(
            id: 1,
            usuario: 'admin@tiendasmass.pe',
            password_hash: '$2y$10$hashdemo',
            rol_id: 1,
            nombre: 'Administrador SGC',
        );

        $this->assertSame(1, $user->id);
        $this->assertSame('admin@tiendasmass.pe', $user->usuario);
        $this->assertSame('$2y$10$hashdemo', $user->password_hash);
        $this->assertSame(1, $user->rol_id);
        $this->assertSame('Administrador SGC', $user->nombre);
        $this->assertSame('', $user->cargo);
        $this->assertTrue($user->activo);
        $this->assertNull($user->created_at);
        $this->assertNull($user->rol);
    }

    public function test_from_row_y_to_array_con_campos_nulos_y_rol_join(): void
    {
        $row = [
            'id' => '2',
            'usuario' => 'cliente@mass.pe',
            'password_hash' => '$2y$10$hashcliente',
            'rol_id' => '2',
            'nombre' => 'Cliente Club Mass',
            'cargo' => 'Cliente',
            'activo' => '1',
            'created_at' => '2026-10-27 09:15:00',
            'rol' => 'cliente',
        ];

        $user = Usuario::fromRow($row);
        $arr = $user->toArray();

        $this->assertSame(2, $user->id);
        $this->assertSame(2, $user->rol_id);
        $this->assertTrue($user->activo);
        $this->assertSame('cliente', $user->rol);
        $this->assertSame('2026-10-27 09:15:00', $arr['created_at']);
        $this->assertSame('cliente', $arr['rol']);

        $rowSinJoin = [
            'id' => 3,
            'usuario' => 'inactivo@mass.pe',
            'password_hash' => '$2y$10$hashinactivo',
            'rol_id' => 2,
            'nombre' => 'Usuario Inactivo',
            'cargo' => '',
            'activo' => 0,
            'created_at' => null,
        ];

        $inactivo = Usuario::fromRow($rowSinJoin);
        $this->assertFalse($inactivo->activo);
        $this->assertNull($inactivo->created_at);
        $this->assertNull($inactivo->rol);
    }
}

