<?php
declare(strict_types=1);

namespace Tests\Integration;

use Database;
use RuntimeException;
use Tests\TestCase;

/**
 * Barreras que impiden ejecutar la suite Integration fuera de mass_online_test.
 * No necesitan servidor MySQL, por eso no heredan de IntegrationTestCase.
 */
final class BaseDePruebaSeguridadTest extends TestCase
{
    public function test_configuracion_de_prueba_apunta_a_mass_online_test(): void
    {
        $cfg = IntegrationTestCase::configPrueba();

        $this->assertSame('mass_online_test', $cfg['dbname']);
        $this->assertNotSame($cfg['dbname'], Database::config()['dbname'] ?? 'mass_online');
    }

    public function test_acepta_solo_mass_online_test_distinta_de_desarrollo(): void
    {
        IntegrationTestCase::validarNombreBase('mass_online_test', 'mass_online');

        $this->addToAssertionCount(1);
    }

    /** @return array<string, array{string, string}> */
    public static function nombresRechazados(): array
    {
        return [
            'base de desarrollo' => ['mass_online', 'mass_online'],
            'otra base' => ['sgc_tiendas_mass', 'mass_online'],
            'mayúsculas' => ['MASS_ONLINE_TEST', 'mass_online'],
            'sufijo' => ['mass_online_test2', 'mass_online'],
            'vacía' => ['', 'mass_online'],
            'desarrollo configurado como test' => ['mass_online_test', 'mass_online_test'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nombresRechazados')]
    public function test_rechaza_cualquier_otra_base(string $dbname, string $desarrollo): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BLOQUEO DE SEGURIDAD');

        IntegrationTestCase::validarNombreBase($dbname, $desarrollo);
    }

    public function test_exige_conexion_activa_a_la_base_de_prueba(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BLOQUEO DE SEGURIDAD');

        IntegrationTestCase::exigirBaseDePrueba(null);
    }
}
