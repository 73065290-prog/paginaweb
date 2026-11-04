<?php
declare(strict_types=1);

namespace Tests\Integration;

use Database;

/**
 * Confirma, con el servidor real, que los repositorios reciben la conexión a mass_online_test
 * y que el esquema cargado es el de database/schema.sql.
 */
final class ConexionBaseDePruebaIntegrationTest extends IntegrationTestCase
{
    public function test_database_pdo_de_los_repositorios_apunta_a_mass_online_test(): void
    {
        $pdo = Database::pdo();

        $this->assertNotNull($pdo);
        $this->assertTrue(Database::connected());
        $this->assertSame('mass_online_test', $pdo->query('SELECT DATABASE()')->fetchColumn());
    }

    public function test_esquema_de_prueba_tiene_las_nueve_tablas_utf8mb4(): void
    {
        $tablas = $this->pdoPrueba()->query(
            "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = 'mass_online_test' ORDER BY TABLE_NAME"
        )->fetchAll();

        $this->assertEqualsCanonicalizing(
            ['categorias', 'cupones', 'inventario', 'pedido_detalles', 'pedidos', 'productos', 'roles', 'tiendas', 'usuarios'],
            array_column($tablas, 'TABLE_NAME')
        );
        foreach ($tablas as $t) {
            $this->assertSame('InnoDB', $t['ENGINE']);
            $this->assertSame('utf8mb4_unicode_ci', $t['TABLE_COLLATION']);
        }
        $fks = (int) $this->pdoPrueba()->query(
            "SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = 'mass_online_test'"
        )->fetchColumn();
        $this->assertSame(7, $fks);
    }

    public function test_cada_prueba_parte_de_tablas_vacias_salvo_roles(): void
    {
        foreach (['usuarios', 'categorias', 'productos', 'tiendas', 'inventario', 'cupones', 'pedidos', 'pedido_detalles'] as $tabla) {
            $this->assertSame(0, $this->contar($tabla), $tabla);
        }
        $this->assertSame(2, $this->contar('roles'));
    }
}
