# Ejecución de pruebas — Mass Online

## 1. Preparación del entorno

### Dependencias
- PHP 8.2.12 (XAMPP) con extensión `pdo_sqlite` y DLL `php_pcov.dll` (carga puntual con `-d extension=pcov`, sin modificar `php.ini` global).
- Composer 2.x
- PHPUnit 11.5 (devDependency en `composer.json`)

### Base de datos
- **App:** no usa MySQL; persistencia en `$_SESSION`.
- **Laboratorio:** SQLite `:memory:` en `Tests\Support\SqliteLabStore` (P32). No toca ninguna BD de desarrollo.

### Datos de prueba / condiciones iniciales
- Catálogo demo en `includes/data.php` (`mass_productos`, usuarios `admin`/`cliente` password `123456`).
- Cada test PHPUnit reinicia `$_SESSION` en `Tests\TestCase::setUp()`.

### Comandos para reproducir

```bat
cd d:\xampp\htdocs\paginaweb
composer install
composer test
composer test:coverage
```

Evidencia cruda de la corrida con cobertura: `docs/pruebas/evidencias/ejecucion-automatizada.txt`.

## 2. Resultados por caso (suite automatizada)

Fuente: ejecución PHPUnit + PCOV del **2026-10-28 01:58:35** (`OK (32 tests, 99 assertions)`, Time **00:00.402**, Lines **58.44%**).

| ID | Funcionalidad evaluada | Tipo | Datos de entrada | Procedimiento ejecutado | Resultado esperado | Resultado obtenido | Estado | Evidencia |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| P01 | `dinero()` | Unitaria | 4.9, 0 | `DineroYFormatoTest` | Formato S/ | Coincide | Aprobado | junit.xml / testdox.html |
| P02 | `e()` XSS | Unitaria/Seg. | `<script>`, null | Idem | Escape HTML | Coincide | Aprobado | idem |
| P03 | Normalizar entrega | Unitaria | express/delivery/retiro | `PedidoReglasTest` | express\|retiro | Coincide | Aprobado | idem |
| P04 | Costo envío | Unitaria | retiro/express | Idem | 0 / 4.9 | Coincide | Aprobado | idem |
| P05 | Cupones | Unitaria | CLUBMASS10/60/X | Idem | 5/6/0 | Coincide | Aprobado | idem |
| P06 | Entrega vacía | Validación | campos vacíos | Idem | ok=false | Coincide | Aprobado | idem |
| P07 | Express dir. corta | Validación | dirección corta | Idem | error dirección | Coincide | Aprobado | idem |
| P08 | Retiro OK | Funcional | Magdalena válida | Idem | ok=true | Coincide | Aprobado | idem |
| P09 | Totales envío | Regresión | carrito 2 und. | Idem | +4.90 express | Coincide | Aprobado | idem |
| P10 | Tracking | Regresión | express vs retiro | Idem | camino / listo | Coincide | Aprobado | idem |
| P11 | Auth vacía | Seguridad | ''/'' | `AuthReglasTest` | falla | Coincide | Aprobado | idem |
| P12 | Auth admin | Funcional | admin/123456 | Idem | ok admin | Coincide | Aprobado | idem |
| P13 | Rol cruzado | Seguridad | admin como cliente | Idem | falla | Coincide | Aprobado | idem |
| P14 | Auth cliente | Seguridad | cliente/123456 | Idem | cliente | Coincide | Aprobado | idem |
| P15 | Pass incorrecta | Seguridad | wrong | Idem | falla | Coincide | Aprobado | idem |
| P16 | Nombre/EAN | Validación | Ab + ean corto | `ProductoAdminValidacionTest` | errores | Coincide | Aprobado | idem |
| P17 | Margen | Validación | PVP=costo | Idem | error PVP | Coincide | Aprobado | idem |
| P18 | Reorden | Validación | 100≥50 | Idem | error | Coincide | Aprobado | idem |
| P19 | ABC vacío | Validación | abc='' | Idem | error ABC | Coincide | Aprobado | idem |
| P20 | Producto válido | Validación | payload OK | Idem | ok | Coincide | Aprobado | idem |
| P21 | Carrito add | Integración | id=1 qty=3 | `CarritoSesionTest` | sesión+totales | Coincide | Aprobado | idem |
| P22 | Carrito mutar | Funcional | +1 / set 0 | Idem | totales | Coincide | Aprobado | idem |
| P23 | Carrito inválido | Validación | id 99999 | Idem | no agrega | Coincide | Aprobado | idem |
| P24 | Login/logout | Integración | admin | `AuthSesionTest` | limpia sesión | Coincide | Aprobado | idem |
| P25 | Sin auth | Seguridad | sesión vacía | Idem | no roles | Coincide | Aprobado | idem |
| P26 | Confirm express | Funcional | pedido válido | `PedidoConfirmacionTest` | MASS- + envío 4.9 | Coincide | Aprobado | idem |
| P27 | Confirm retiro | Funcional | retiro | Idem | envío 0 | Coincide | Aprobado | idem |
| P28 | Crear producto | Integración | alta válida | `ProductoAdminPersistenciaTest` | +1 SKU | Coincide | Aprobado | idem |
| P29 | Alta vacía | Validación | vacío | Idem | no persiste | Coincide | Aprobado | idem |
| P30 | Editar producto | Regresión | id=1 | Idem | override | Coincide | Aprobado | idem |
| P31 | Filtro catálogo | Funcional | Lácteos / primor | `CatalogoYSqliteLabTest` | filtros OK | Coincide | Aprobado | idem |
| P32 | SQLite lab | Integración BD | insert/select | Idem | lee guardado | Coincide | Aprobado | idem |

## 3. Resumen cuantitativo (ejecución real con cobertura)

| Métrica | Valor |
| --- | --- |
| Total ejecutados | **32** |
| Aprobados | **32** |
| Fallidos | **0** |
| Omitidos / Bloqueados | **0** |
| Assertions | **99** |
| % aprobación | **100%** = (32/32)×100 |
| Tiempo | **0.402 s** (corrida con PCOV) |
| Cobertura de líneas (`includes/`) | **58.44%** (817/1398) |

## 4. Análisis de defectos / incidencias durante el desarrollo de pruebas

| # | Qué falló | Causa | Corrección | Archivo |
| --- | --- | --- | --- | --- |
| INC-01 | P10 falló al buscar clave `id` en tracking | El arreglo usa la clave `key`, no `id` (defecto del caso de prueba, no del software) | Ajustar asserts a `array_column(..., 'key')` y verificar `listo` en retiro | `tests/Unit/PedidoReglasTest.php` |
| INC-02 | Filtro catálogo vacío si no se pasan flags de stock | Contrato de `filtrar_productos`: sin `stock_ok`/`stock_bajo` excluye todos los tonos (comportamiento alineado con `catalogo.php`, que por defecto activa ambos) | La prueba pasa ambos flags como el front | `tests/Feature/CatalogoYSqliteLabTest.php` |

**Consecuencias:** sin INC-01 la suite reportaba falso negativo. INC-02 evita malinterpretar la API del catálogo.

**Código fuente de producción modificado por fallos de prueba:** ninguno en esta corrida (los arreglos fueron en tests).
