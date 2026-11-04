<?php
declare(strict_types=1);

namespace Tests\Integration;

use PDOException;
use UsuarioRepository;

/**
 * UsuarioRepository implementa allAuthUsers() (usuarios activos con su rol) y
 * findByIdentifier() (por usuario/correo o alias previo a '@', contra BD o una fuente dada).
 */
final class UsuarioRepositoryIntegrationTest extends IntegrationTestCase
{
    private string $hash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hash = password_hash('ClaveDePrueba!', PASSWORD_BCRYPT);
        $this->insertar('usuarios', ['usuario' => 'admin.it@mass.pe', 'password_hash' => $this->hash, 'rol_id' => 1, 'nombre' => 'Gonzalo Méndez', 'cargo' => 'Administrador']);
        $this->insertar('usuarios', ['usuario' => 'cliente.it', 'password_hash' => $this->hash, 'rol_id' => 2, 'nombre' => 'Cliente Demo', 'cargo' => '']);
        $this->insertar('usuarios', ['usuario' => 'inactivo.it', 'password_hash' => $this->hash, 'rol_id' => 2, 'nombre' => 'Inactivo', 'activo' => 0]);
    }

    public function test_all_auth_users_devuelve_activos_con_su_rol(): void
    {
        $usuarios = array_column((new UsuarioRepository())->allAuthUsers(), null, 'usuario');

        $this->assertEqualsCanonicalizing(['admin.it@mass.pe', 'cliente.it'], array_keys($usuarios));
        $this->assertSame([
            'usuario' => 'admin.it@mass.pe',
            'password_hash' => $this->hash,
            'rol' => 'admin',
            'nombre' => 'Gonzalo Méndez',
            'cargo' => 'Administrador',
        ], $usuarios['admin.it@mass.pe']);
        $this->assertSame('cliente', $usuarios['cliente.it']['rol']);
        $this->assertTrue(password_verify('ClaveDePrueba!', $usuarios['cliente.it']['password_hash']));
    }

    public function test_find_by_identifier_contra_bd_por_usuario_alias_y_mayusculas(): void
    {
        $repo = new UsuarioRepository();

        $this->assertSame('admin.it@mass.pe', $repo->findByIdentifier('admin.it@mass.pe')['usuario'] ?? null);
        $this->assertSame('admin.it@mass.pe', $repo->findByIdentifier('  ADMIN.IT  ')['usuario'] ?? null);
        $this->assertSame('cliente', $repo->findByIdentifier('Cliente.IT')['rol'] ?? null);
        $this->assertNull($repo->findByIdentifier('inactivo.it'), 'Los usuarios inactivos no se devuelven');
        $this->assertNull($repo->findByIdentifier('no.existe'));
        $this->assertNull($repo->findByIdentifier('   '));
    }

    public function test_find_by_identifier_con_fuente_explicita_no_consulta_bd(): void
    {
        $fuente = [['usuario' => 'externo@mass.pe', 'password_hash' => 'h', 'rol' => 'admin', 'nombre' => 'Externo', 'cargo' => '']];
        $repo = new UsuarioRepository();

        $this->assertSame('Externo', $repo->findByIdentifier('externo', $fuente)['nombre'] ?? null);
        $this->assertNull($repo->findByIdentifier('cliente.it', $fuente));
    }

    public function test_comillas_apostrofes_y_caracteres_especiales(): void
    {
        $usuario = "o'brien\"mj\"%_\\@mass.pe";
        $nombre = "María José O'Brien \"MJ\" — ñ 😀";
        $this->insertar('usuarios', ['usuario' => $usuario, 'password_hash' => $this->hash, 'rol_id' => 2, 'nombre' => $nombre, 'cargo' => "Jefa d'Operaciones"]);
        $repo = new UsuarioRepository();

        $encontrado = $repo->findByIdentifier($usuario);

        $this->assertSame($nombre, $encontrado['nombre'] ?? null);
        $this->assertSame("Jefa d'Operaciones", $encontrado['cargo'] ?? null);
        $this->assertSame($usuario, $repo->findByIdentifier("o'brien\"mj\"%_\\")['usuario'] ?? null);
        $this->assertNull($repo->findByIdentifier("' OR '1'='1"));
        $this->assertNull($repo->findByIdentifier("admin' --"));
        $this->assertSame(4, $this->contar('usuarios'));
    }

    public function test_refleja_actualizacion_desactivacion_y_baja(): void
    {
        $repo = new UsuarioRepository();

        $this->ejecutar('UPDATE usuarios SET nombre = ?, rol_id = 1 WHERE usuario = ?', ['Cliente Ascendido', 'cliente.it']);
        $ascendido = $repo->findByIdentifier('cliente.it');
        $this->assertSame('Cliente Ascendido', $ascendido['nombre'] ?? null);
        $this->assertSame('admin', $ascendido['rol'] ?? null);

        $this->ejecutar('UPDATE usuarios SET activo = 0 WHERE usuario = ?', ['cliente.it']);
        $this->assertNull($repo->findByIdentifier('cliente.it'));

        $this->ejecutar('UPDATE usuarios SET activo = 1 WHERE usuario = ?', ['inactivo.it']);
        $this->assertNotNull($repo->findByIdentifier('inactivo.it'));

        $this->assertSame(1, $this->ejecutar('DELETE FROM usuarios WHERE usuario = ?', ['inactivo.it']));
        $this->assertNull($repo->findByIdentifier('inactivo.it'));
        $this->assertSame(['admin.it@mass.pe'], array_column($repo->allAuthUsers(), 'usuario'));
    }

    public function test_restricciones_de_rol_y_usuario_unico(): void
    {
        try {
            $this->insertar('usuarios', ['usuario' => 'sin.rol', 'password_hash' => 'x', 'rol_id' => 99, 'nombre' => 'x']);
            $this->fail('Se esperaba violación de fk_usuarios_rol');
        } catch (PDOException $e) {
            $this->assertSame(1452, $e->errorInfo[1]);
        }
        try {
            $this->insertar('usuarios', ['usuario' => 'cliente.it', 'password_hash' => 'x', 'rol_id' => 2, 'nombre' => 'Duplicado']);
            $this->fail('Se esperaba violación de uq_usuarios_usuario');
        } catch (PDOException $e) {
            $this->assertSame(1062, $e->errorInfo[1]);
        }
        try {
            $this->ejecutar('DELETE FROM roles WHERE id = ?', [2]);
            $this->fail('Se esperaba RESTRICT al borrar un rol con usuarios');
        } catch (PDOException $e) {
            $this->assertSame(1451, $e->errorInfo[1]);
        }
        $this->assertSame(3, $this->contar('usuarios'));
        $this->assertSame(2, $this->contar('roles'));
    }

    public function test_sin_conexion_devuelve_vacio(): void
    {
        $this->desconectarBaseDeDatos();
        $repo = new UsuarioRepository();

        $this->assertSame([], $repo->allAuthUsers());
        $this->assertNull($repo->findByIdentifier('cliente.it'));
    }
}
