<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_SESSION['carrito'] = [];
        $_SESSION['guardados'] = [];
        $_SESSION['pedido'] = pedido_defaults();
        $_SESSION['admin_producto_overrides'] = [];
        $_SESSION['admin_productos_extra'] = [];
        unset($_SESSION['auth'], $_SESSION['flash'], $_SESSION['ultimo_pedido']);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }
}
