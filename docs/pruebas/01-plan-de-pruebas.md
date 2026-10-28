# Plan de pruebas — Mass Online (Tiendas Mass)

| Campo | Valor |
| --- | --- |
| Documento | Plan de pruebas (dinámicas) |
| Proyecto | Mass Online — Tiendas Mass (`paginaweb`) |
| Versión del plan | 1.1 |
| Casos planificados | **P01–P32** (32) |
| Estrategia | Caja negra + caja blanca sobre `includes/` |
| Automatización | PHPUnit 11 + PCOV (ver `03-pruebas-automatizadas.md`) |
| Relacionado | Ejecución → `02-ejecucion-pruebas.md` · Informe → `INFORME-FINAL.md` |

---

## 1. Descripción del proyecto

| Campo | Detalle |
| --- | --- |
| **Nombre** | Mass Online — Tiendas Mass (`paginaweb`) |
| **Objetivo del software** | Tienda en línea y panel admin WMS demo: catálogo, carrito, checkout retiro/express, login SGC, administración de productos. |
| **Stack** | PHP 8.2 nativo, HTML/CSS/JS, sesiones PHP, Apache (XAMPP). Sin MySQL de producción. |
| **Persistencia demo** | `$_SESSION` (carrito, auth, overrides de productos). Laboratorio de integración: SQLite `:memory:` solo en pruebas (P32). |
| **Módulos bajo prueba** | Storefront (`cliente_interfaces/`), carrito/pedido/confirmación, auth admin/cliente, dashboard admin, alta/edición de productos con reglas de negocio. |
| **Entorno** | Windows + XAMPP (`http://localhost/paginaweb/`), CLI PHP 8.2, Composer, PHPUnit 11, PCOV. |
| **Herramientas de prueba** | PHPUnit 11 (unitarias/feature), PCOV (Clover/HTML), JUnit XML, SQLite lab, scripts `composer test` / `composer test:coverage`. |

### 1.1 Objetivos de las pruebas

1. Verificar que las **reglas de negocio** de checkout (envío, cupones, validación de entrega, tracking) se comportan según lo definido.
2. Asegurar que el **login SGC** rechaza credenciales inválidas y roles cruzados, y acepta usuarios demo válidos.
3. Validar las **reglas de alta/edición de productos** (nombre, EAN, margen PVP/costo, reorden vs capacidad, ABC).
4. Comprobar la **persistencia en sesión** (carrito, auth, productos admin) y un laboratorio SQLite aislado.
5. Dejar evidencia repetible (JUnit, cobertura) para el informe de calidad y SonarQube.

### 1.2 Criterios de entrada y salida

| Criterio de entrada | Criterio de salida |
| --- | --- |
| Código en `includes/` estable y accesible vía XAMPP | 32/32 casos ejecutados con veredicto |
| Composer + PHPUnit instalados (`composer install`) | 0 fallos bloqueantes en lógica crítica |
| Datos demo disponibles (`admin`/`cliente` · `123456`) | Evidencias en `docs/pruebas/evidencias/` |
| Extensión `pdo_sqlite` disponible (P32) | Cobertura Clover generada para Sonar |

---

## 2. Alcance

### 2.1 En alcance

| Área | Componentes / rutas |
| --- | --- |
| Utilidades | `dinero()`, `e()` en `includes/functions.php` |
| Pedido / checkout | Normalización entrega, envío, cupones, validación, resumen, tracking, confirmación |
| Autenticación | Login por rol, sesión, logout, estado anónimo |
| Carrito | Agregar, mutar cantidad, rechazar inválidos |
| Productos admin | Validación + crear/editar (overrides en sesión) |
| Catálogo | `filtrar_productos` (categoría / búsqueda) |
| Lab BD | SQLite `:memory:` (evidencia guardar/leer aislada) |

### 2.2 Fuera de alcance

- Pixel-perfect / maquetación Figma (manual visual).
- E2E con navegador (Playwright/Cypress) — opcional a futuro.
- MySQL / despliegue en producción.
- Ejecución del scanner SonarQube (lo realiza el estudiante; ver `05-guia-sonarqube.md`).
- Pruebas de rendimiento, carga o usabilidad formal.

---

## 3. Estrategia y tipos de prueba (≥ 3)

| Tipo | Finalidad | Justificación en Mass Online | Casos |
| --- | --- | --- | --- |
| **Unitarias** | Reglas aisladas sin HTTP | Núcleo en `functions.php` / `data.php`; un fallo rompe checkout o WMS | P01–P05, P09–P10, P16–P20 |
| **Integración / persistencia** | Guardar y leer en el almacén real de la app | La sesión es el “backend” demo; SQLite aísla evidencias de BD | P21, P24, P28–P30, P32 |
| **Funcionales** | Flujos de negocio extremo a extremo (lógica) | Camino crítico cliente (carrito→pedido) y admin (alta producto) | P08, P12, P22, P26–P27, P31 |
| **Validación de datos** | Rechazo explícito de entradas inválidas | Defectos previos en alta vacía / ABC / EAN / margen | P06–P07, P16–P19, P23, P29 |
| **Seguridad (acceso)** | Credenciales y roles | Panel admin y login SGC deben denegar acceso indebido | P02*, P11, P13–P15, P25 |
| **Regresión** | Congelar reglas ya estabilizadas | Evitar que cambios UI/admin rompan totales y tracking | P09–P10, P30 |

\*P02 cubre escape XSS básico (`e()`), clasificado como seguridad + unitaria.

### 3.1 Distribución por tipo (conteo primario)

| Tipo primario | Cantidad | % del plan |
| --- | --- | --- |
| Unitaria / negocio | 10 | 31% |
| Validación | 8 | 25% |
| Seguridad | 5 | 16% |
| Funcional | 5 | 16% |
| Integración | 4 | 12% |
| **Total** | **32** | **100%** |

*(Algunos casos son mixtos; la tabla de la §5 marca el tipo principal.)*

### 3.2 Prioridad

| Prioridad | Criterio | IDs orientativos |
| --- | --- | --- |
| **Alta** | Checkout, auth, validación producto, confirmación de pedido | P06–P15, P16–P20, P26–P29 |
| **Media** | Carrito, totales, tracking, edición | P01–P05, P09–P10, P21–P25, P30 |
| **Baja** | Formato auxiliar, lab SQLite, filtros | P01–P02, P31–P32 |

---

## 4. Riesgos y mitigación

| Riesgo | Impacto | Mitigación en el plan |
| --- | --- | --- |
| Reglas de envío/cupón incorrectas | Pedidos con montos erróneos | P04, P05, P09, P26, P27 |
| Alta de producto sin validar | Datos basura en WMS demo | P16–P20, P29 |
| Acceso admin con rol/credencial mala | Fuga de panel | P11–P15, P24–P25 |
| Sesión contaminada entre pruebas | Falsos positivos/negativos | `TestCase::setUp()` limpia `$_SESSION` |
| Confundir filtro de catálogo sin flags de stock | Suite en rojo por mal contrato | P31 documenta flags `stock_ok`/`stock_bajo` |
| No poder evidenciar “BD” sin MySQL | Brecha académica de integración | P32 con SQLite `:memory:` |

---

## 5. Matriz del plan (P01–P32)

Leyenda de **prioridad**: A = Alta · M = Media · B = Baja.

| ID | Módulo | Funcionalidad | Tipo | Prioridad | Precondiciones | Entrada / escenario | Resultado esperado | Automatizado en |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| P01 | Utilidades | Formato `dinero()` | Unitaria | B | — | Valores `4.9` y `0` | `S/ 4.90`, `S/ 0.00` | `DineroYFormatoTest` |
| P02 | Utilidades | Escape HTML `e()` | Seguridad / unitaria | B | — | `<script>` y `null` | Entidades HTML; cadena vacía | `DineroYFormatoTest` |
| P03 | Pedido | Normalizar modalidad | Unitaria | M | — | `express`, `delivery`, `retiro` | Solo `express` o `retiro` | `PedidoReglasTest` |
| P04 | Pedido | Costo de envío | Unitaria | A | — | Retiro vs express | `0.0` vs `4.9` | `PedidoReglasTest` |
| P05 | Pedido | Cupones | Unitaria | A | — | `CLUBMASS10`, `CLUBMASS60`, inválido | Descuentos `5` / `6` / `0` | `PedidoReglasTest` |
| P06 | Pedido | Validar entrega vacía | Validación | A | Modalidad retiro | POST vacío | `ok=false`, ≥ 4 errores | `PedidoReglasTest` |
| P07 | Pedido | Express dirección corta | Validación | A | Modalidad express | Dirección &lt; 10 caracteres | Error de dirección | `PedidoReglasTest` |
| P08 | Pedido | Retiro con stock | Funcional | A | Tienda Magdalena | Datos de retiro válidos | `ok=true`, dirección de tienda | `PedidoReglasTest` |
| P09 | Pedido | Totales envío | Regresión | A | Carrito con ítems | Resumen retiro vs express | Express = retiro + **4.90** | `PedidoReglasTest` |
| P10 | Pedido | Tracking pasos | Regresión | M | — | Tracking express vs retiro | Express incluye `camino`; retiro `listo` | `PedidoReglasTest` |
| P11 | Auth | Credenciales vacías | Seguridad | A | — | usuario/clave vacíos | Falla con ≥ 2 errores | `AuthReglasTest` |
| P12 | Auth | Login admin OK | Funcional | A | Usuario demo | `admin` / `123456` | `ok`, rol admin | `AuthReglasTest` |
| P13 | Auth | Admin como cliente | Seguridad | A | — | admin + rol cliente | Falla (rol cruzado) | `AuthReglasTest` |
| P14 | Auth | Login cliente | Seguridad | A | Usuario demo | `cliente` / `123456` | Es cliente, **no** admin | `AuthReglasTest` |
| P15 | Auth | Password incorrecta | Seguridad | A | — | admin + password mala | Falla | `AuthReglasTest` |
| P16 | Productos admin | Nombre / EAN | Validación | A | — | Nombre corto + EAN corto | Errores nombre y EAN | `ProductoAdminValidacionTest` |
| P17 | Productos admin | Margen PVP/costo | Validación | A | — | PVP ≤ costo | Error de margen | `ProductoAdminValidacionTest` |
| P18 | Productos admin | Reorden vs capacidad | Validación | A | — | reorden ≥ capacidad | Error de reorden | `ProductoAdminValidacionTest` |
| P19 | Productos admin | ABC vacío | Validación | A | — | `abc` sin seleccionar | Error ABC | `ProductoAdminValidacionTest` |
| P20 | Productos admin | Payload válido | Validación | A | — | Datos completos correctos | `ok=true` | `ProductoAdminValidacionTest` |
| P21 | Carrito | Agregar producto | Integración | M | Sesión limpia | SKU `1` × 3 | Unidades y monto en sesión | `CarritoSesionTest` |
| P22 | Carrito | Mutar / eliminar | Funcional | M | Línea en carrito | +1 y set `0` | Totales correctos / carrito vacío | `CarritoSesionTest` |
| P23 | Carrito | Entrada inválida | Validación | M | — | id inexistente / qty `0` | No agrega | `CarritoSesionTest` |
| P24 | Auth | Login + logout sesión | Integración | A | — | Login admin + logout | Sesión limpia | `AuthSesionTest` |
| P25 | Auth | Sin autenticar | Seguridad | A | Sesión vacía | Estado inicial | No es admin ni cliente | `AuthSesionTest` |
| P26 | Pedido | Confirmar express | Funcional | A | Carrito + entrega OK | Validar + confirmar | Orden `MASS-…`, carrito vacío, envío **4.9** | `PedidoConfirmacionTest` |
| P27 | Pedido | Confirmar retiro | Funcional | A | Carrito + retiro OK | Validar + confirmar | Envío **0** | `PedidoConfirmacionTest` |
| P28 | Productos admin | Crear válido | Integración | A | Auth admin (sesión) | Alta con payload OK | +1 SKU en sesión | `ProductoAdminPersistenciaTest` |
| P29 | Productos admin | Crear vacío | Validación | A | — | Alta vacía | No persiste | `ProductoAdminPersistenciaTest` |
| P30 | Productos admin | Editar producto | Regresión / integración | M | Producto id=`1` | Guardar edición | Override en sesión | `ProductoAdminPersistenciaTest` |
| P31 | Catálogo | Filtro categoría / búsqueda | Funcional | B | Flags stock activos | cat. Lácteos / q `primor` | Resultados coherentes | `CatalogoYSqliteLabTest` |
| P32 | Lab BD | SQLite memoria | Integración | B | PDO SQLite | Insert + select | Lee exactamente lo guardado | `CatalogoYSqliteLabTest` |

---

## 6. Trazabilidad módulo → casos

| Módulo de negocio | Casos | Archivo fuente principal |
| --- | --- | --- |
| Formato / XSS | P01–P02 | `includes/functions.php` |
| Checkout / pedido | P03–P10, P26–P27 | `includes/functions.php` |
| Autenticación | P11–P15, P24–P25 | `includes/functions.php` |
| Carrito | P21–P23 | `includes/functions.php` |
| Productos admin (reglas) | P16–P20 | `includes/data.php` |
| Productos admin (persistencia) | P28–P30 | `includes/data.php` + sesión |
| Catálogo | P31 | `includes/data.php` / `functions.php` |
| Laboratorio SQLite | P32 | `tests/Support/SqliteLabStore.php` |

---

## 7. Datos de prueba y entorno

| Recurso | Valor |
| --- | --- |
| URL local | `http://localhost/paginaweb/` |
| Admin demo | usuario `admin` · password `123456` |
| Cliente demo | usuario `cliente` · password `123456` |
| Catálogo | `mass_productos()` en `includes/data.php` |
| Aislamiento | Cada test reinicia `$_SESSION` en `Tests\TestCase::setUp()` |
| BD app | No aplica (sin MySQL) |
| BD lab | SQLite `:memory:` (solo P32) |

### Comandos de ejecución previstos

```bat
cd d:\xampp\htdocs\paginaweb
composer install
composer test
composer test:coverage
```

---

## 8. Oráculo y criterios de aceptación por caso

Un caso se marca **aprobado** solo si:

1. El assert PHPUnit pasa (resultado obtenido = esperado de la matriz §5).
2. No deja efectos colaterales en otros tests (sesión limpia / SQLite aislado).
3. Para validaciones negativas: el sistema **no** persiste ni confirma la operación.

Umbral del plan completo:

| Métrica | Umbral |
| --- | --- |
| Casos ejecutados | 32/32 |
| Fallidos | 0 en crítica (prioridad Alta) |
| Cobertura líneas `includes/` | ≥ 50% (meta de práctica; actual en ejecución documentada) |

---

## 9. Entregables del plan

| Entregable | Ubicación |
| --- | --- |
| Este plan | `docs/pruebas/01-plan-de-pruebas.md` |
| Ejecución y resultados | `docs/pruebas/02-ejecucion-pruebas.md` |
| Diseño automatizado | `docs/pruebas/03-pruebas-automatizadas.md` |
| Manual vs automático | `docs/pruebas/04-comparacion-manual-vs-automatizada.md` |
| Guía SonarQube | `docs/pruebas/05-guia-sonarqube.md` |
| Evidencias (JUnit, TestDox, log) | `docs/pruebas/evidencias/` |
| Informe final | `docs/pruebas/INFORME-FINAL.md` |

---

## 10. Historial del documento

| Versión | Cambio |
| --- | --- |
| 1.0 | Plan inicial P01–P32 con tipos y matriz resumida |
| 1.1 | Objetivos, entrada/salida, riesgos, prioridades, precondiciones, trazabilidad, oráculo y distribución por tipo |
