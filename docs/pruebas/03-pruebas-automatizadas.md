# Pruebas automatizadas — Mass Online

## 1. Herramienta y propósito

| Ítem | Detalle |
| --- | --- |
| Herramienta | **PHPUnit 11.5** + **PCOV 1.0.11** |
| Propósito | Ejecutar de forma repetible las reglas de negocio, validaciones, seguridad de auth y persistencia en sesión/SQLite lab. |
| Pertinencia | El proyecto es PHP nativo (no Laravel/React). PHPUnit es el estándar de facto; PCOV genera Clover XML consumible por SonarQube. |

## 2. Casos del plan automatizados

Se automatizaron **P01–P32** (32/32 del plan). Criterio: toda lógica en `includes/` sin dependencia de navegador.

## 3. Tabla ID → archivo → test

| ID | Archivo | Método / test | Qué verifica |
| --- | --- | --- | --- |
| P01–P02 | `tests/Unit/DineroYFormatoTest.php` | `test_dinero_*`, `test_escape_*` | Formato y escape |
| P03–P10 | `tests/Unit/PedidoReglasTest.php` | varios `test_*` | Entrega, cupones, totales, tracking |
| P11–P15 | `tests/Unit/AuthReglasTest.php` | varios `test_auth_*` | Login/roles |
| P16–P20 | `tests/Unit/ProductoAdminValidacionTest.php` | `test_validar_*` | Reglas alta/edición producto |
| P21–P23 | `tests/Feature/CarritoSesionTest.php` | carrito | Persistencia sesión carrito |
| P24–P25 | `tests/Feature/AuthSesionTest.php` | sesión auth | Login/logout / anónimo |
| P26–P27 | `tests/Feature/PedidoConfirmacionTest.php` | confirmar | Orden + vaciado carrito |
| P28–P30 | `tests/Feature/ProductoAdminPersistenciaTest.php` | crear/guardar | Overrides sesión admin |
| P31–P32 | `tests/Feature/CatalogoYSqliteLabTest.php` | filtro + SQLite | Catálogo y BD lab |

## 4. Fragmentos clave

Bootstrap sesión aislada:

```1:24:tests/bootstrap.php
<?php
declare(strict_types=1);
// ...
$_SESSION['carrito'] = [];
// ...
```

Validación de producto (comentario de plan en docblock del test):

```php
/** // P17 */
public function test_validar_rechaza_pvp_menor_o_igual_a_costo(): void
```

SQLite laboratorio:

```1:40:tests/Support/SqliteLabStore.php
// PDO sqlite::memory: — no toca BD de desarrollo
```

## 5. Comandos de ejecución

```bat
composer install
composer test
composer test:coverage
```

Equivale a:

```bat
php -d extension=pcov -d pcov.enabled=1 vendor\bin\phpunit --coverage-text --coverage-clover=docs/pruebas/evidencias/coverage/clover.xml --coverage-html=docs/pruebas/evidencias/coverage/html --log-junit=docs/pruebas/evidencias/junit.xml
```

## 6. Resultados reales (corrida con cobertura)

| Métrica | Valor medido |
| --- | --- |
| Tests | 32 |
| Assertions | 99 |
| Fallidos | 0 |
| Tiempo | 1.639 s |
| Cobertura líneas | 58.30% (811/1391) |
| Evidencia | `docs/pruebas/evidencias/ejecucion-automatizada.txt` |
| JUnit | `docs/pruebas/evidencias/junit.xml` |
| Clover | `docs/pruebas/evidencias/coverage/clover.xml` |
| HTML cobertura | `docs/pruebas/evidencias/coverage/html/index.html` |
| TestDox HTML | `docs/pruebas/evidencias/testdox.html` |

## 7. Análisis de fallos

Durante el armado de la suite:

1. **P10** falló una vez por assert incorrecto (`id` vs `key`) → corregido en el test (INC-01).
2. Suite final: **0 fallos**. No se debilitaron asserts ni se usó `markTestSkipped` para forzar verde.
