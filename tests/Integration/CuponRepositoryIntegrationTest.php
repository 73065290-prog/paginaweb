<?php
declare(strict_types=1);

namespace Tests\Integration;

use CuponRepository;
use PDOException;

/**
 * CuponRepository implementa descuento(codigo): consulta preparada que normaliza el código
 * (trim + mayúsculas) y solo devuelve cupones activos.
 */
final class CuponRepositoryIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->insertar('cupones', ['codigo' => 'CLUBTEST10', 'descuento_fijo' => 5.00, 'activo' => 1]);
        $this->insertar('cupones', ['codigo' => 'CLUBTEST60', 'descuento_fijo' => 6.50, 'activo' => 1]);
        $this->insertar('cupones', ['codigo' => 'VENCIDO', 'descuento_fijo' => 9.00, 'activo' => 0]);
    }

    public function test_descuento_de_cupon_activo(): void
    {
        $repo = new CuponRepository();

        $this->assertSame(5.0, $repo->descuento('CLUBTEST10'));
        $this->assertSame(6.5, $repo->descuento('CLUBTEST60'));
    }

    public function test_normaliza_mayusculas_y_espacios(): void
    {
        $this->assertSame(5.0, (new CuponRepository())->descuento("  clubtest10 \t"));
    }

    public function test_cupon_inactivo_o_inexistente_devuelve_cero(): void
    {
        $repo = new CuponRepository();

        $this->assertSame(0.0, $repo->descuento('VENCIDO'));
        $this->assertSame(0.0, $repo->descuento('NO-EXISTE'));
        $this->assertSame(0.0, $repo->descuento(''));
    }

    public function test_consulta_preparada_con_comillas_y_caracteres_especiales(): void
    {
        $codigo = "O'NEIL-\"50\"%_\\";
        $this->insertar('cupones', ['codigo' => $codigo, 'descuento_fijo' => 7.25]);
        $repo = new CuponRepository();

        $this->assertSame(7.25, $repo->descuento("o'neil-\"50\"%_\\"));
        $this->assertSame(0.0, $repo->descuento("' OR '1'='1"));
        $this->assertSame(0.0, $repo->descuento("CLUBTEST10' -- "));
        $this->assertSame(0.0, $repo->descuento('%'));
        $this->assertSame(4, $this->contar('cupones'));
    }

    public function test_refleja_alta_actualizacion_desactivacion_y_baja(): void
    {
        $repo = new CuponRepository();

        $this->insertar('cupones', ['codigo' => 'NUEVO20', 'descuento_fijo' => 20.00]);
        $this->assertSame(20.0, $repo->descuento('nuevo20'));

        $this->ejecutar('UPDATE cupones SET descuento_fijo = ? WHERE codigo = ?', [12.40, 'NUEVO20']);
        $this->assertSame(12.4, $repo->descuento('NUEVO20'));

        $this->ejecutar('UPDATE cupones SET activo = 0 WHERE codigo = ?', ['NUEVO20']);
        $this->assertSame(0.0, $repo->descuento('NUEVO20'));

        $this->assertSame(1, $this->ejecutar('DELETE FROM cupones WHERE codigo = ?', ['NUEVO20']));
        $this->assertSame(0.0, $repo->descuento('NUEVO20'));
        $this->assertSame(5.0, $repo->descuento('CLUBTEST10'));
    }

    public function test_clave_primaria_impide_codigos_duplicados(): void
    {
        try {
            $this->insertar('cupones', ['codigo' => 'CLUBTEST10', 'descuento_fijo' => 99.00]);
            $this->fail('Se esperaba violación de PRIMARY KEY');
        } catch (PDOException $e) {
            $this->assertSame(1062, $e->errorInfo[1]);
        }
        $this->assertSame(5.0, (new CuponRepository())->descuento('CLUBTEST10'));
    }

    public function test_sin_conexion_devuelve_senal_de_fallback(): void
    {
        $this->desconectarBaseDeDatos();

        $this->assertSame(-1.0, (new CuponRepository())->descuento('CLUBTEST10'));
    }
}
