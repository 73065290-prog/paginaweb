# Plan de pruebas — Mass Online (Tiendas Mass)

## 1. Descripción del proyecto

| Campo | Detalle |
| --- | --- |
| **Nombre** | Mass Online — Tiendas Mass (`paginaweb`) |
| **Objetivo** | Tienda en línea y panel admin WMS demo (catálogo, carrito, checkout retiro/express, login SGC, administración de productos) implementado en PHP nativo sobre XAMPP, sin MySQL de producción. |
| **Tecnologías** | PHP 8.2, HTML/CSS/JS, sesiones PHP, Apache (XAMPP). Persistencia demo: `$_SESSION` (carrito, auth, overrides de productos). Laboratorio de integración: SQLite en memoria solo en pruebas. |
| **Funcionalidades principales** | Storefront (`cliente_interfaces/`), carrito/pedido/confirmación, auth admin/cliente, dashboard admin, administración de productos (alta/edición con reglas), inventario/pedidos demo. |
| **Entorno de ejecución** | Windows + XAMPP (`http://localhost/paginaweb/`), CLI PHP 8.2, Composer, PHPUnit 11, PCOV para cobertura. |
| **Herramientas de pruebas** | PHPUnit 11 (unitarias/feature), PCOV (cobertura Clover/HTML), JUnit XML, SQLite `:memory:` (lab integración), scripts Composer `test` / `test:coverage`. |

## 2. Tipos de pruebas seleccionados (mínimo 3)

| Tipo | Finalidad | Justificación en este proyecto |
| --- | --- | --- |
| **Unitarias** | Verificar reglas de negocio aisladas (envío, cupones, validación de entrega, validación de producto admin, auth). | El núcleo del curso vive en `includes/functions.php` y `includes/data.php`; fallos aquí afectan checkout y WMS. |
| **Integración / persistencia** | Comprobar que carrito, auth, órdenes y productos admin se guardan y leen en el almacén de la app (`$_SESSION`) y en SQLite de laboratorio. | No hay BD MySQL de desarrollo; la sesión es el almacén real. SQLite aísla evidencias de “guardar/leer” sin tocar datos de demo. |
| **Funcionales** | Flujos de agregar al carrito → validar entrega → confirmar pedido; crear/editar producto admin. | Cubren el camino crítico del cliente y del administrador. |
| **Validación de datos** | Campos vacíos, EAN inválido, PVP ≤ costo, reorden ≥ capacidad, ABC sin seleccionar. | Ya hubo defectos UX/negocio en alta de productos; deben fallar de forma explícita. |
| **Seguridad (acceso)** | Credenciales vacías/incorrectas, rol incorrecto, sesión sin autenticar no es admin. | El panel admin y el login SGC deben rechazar acceso indebido. |
| **Regresión** | Totales express = retiro + 4.90; tracking express vs retiro; edición de producto conserva reglas. | Evitar que cambios en UI/admin rompan reglas ya estabilizadas. |

## 3. Matriz del plan (P01–P32)

| ID | Funcionalidad / componente | Tipo | Procedimiento / escenario | Resultado esperado |
| --- | --- | --- | --- | --- |
| P01 | Formato monetario `dinero()` | Unitaria | Invocar con 4.9 y 0 | `S/ 4.90`, `S/ 0.00` |
| P02 | Escape HTML `e()` | Seguridad / unitaria | Pasar `<script>` y `null` | Entidades HTML; cadena vacía |
| P03 | Normalizar modalidad entrega | Unitaria | `express`, `delivery`, `retiro` | Solo `express` o `retiro` |
| P04 | Costo de envío | Unitaria / negocio | Retiro vs express | 0.0 vs 4.9 |
| P05 | Cupones | Unitaria | CLUBMASS10/60 e inválido | 5 / 6 / 0 |
| P06 | Validar entrega vacía | Validación | POST vacío retiro | `ok=false`, ≥4 errores |
| P07 | Express dirección corta | Validación | Dirección &lt; 10 chars | Error de dirección |
| P08 | Retiro tienda con stock | Funcional | Magdalena + datos válidos | `ok=true`, dirección de tienda |
| P09 | Totales envío | Regresión / unitaria | Carrito + resumen retiro/express | Express = retiro + 4.90 |
| P10 | Tracking pasos | Regresión | Tracking express vs retiro | Express tiene `camino`; retiro `listo` |
| P11 | Auth vacía | Seguridad | usuario/pass vacíos | Falla con ≥2 errores |
| P12 | Auth admin OK | Funcional | admin / 123456 | `ok`, rol admin |
| P13 | Admin en modo cliente | Seguridad | admin + rol cliente | Falla |
| P14 | Cliente login | Seguridad | cliente / 123456 | Es cliente, no admin |
| P15 | Password incorrecta | Seguridad | admin + password mala | Falla |
| P16 | Alta producto nombre/EAN | Validación | Nombre corto + EAN corto | Errores nombre y EAN |
| P17 | Margen PVP/costo | Validación / negocio | PVP = costo | Error margen |
| P18 | Reorden vs capacidad | Validación | reorden ≥ capacidad | Error reorden |
| P19 | ABC no seleccionado | Validación | abc vacío | Error ABC |
| P20 | Payload producto válido | Validación | Datos completos | `ok=true` |
| P21 | Carrito agregar | Funcional / integración | Agregar SKU 1 ×3 | Unidades y monto en sesión |
| P22 | Carrito mutaciones | Funcional | +1 y set 0 | Totales correctos / vacío |
| P23 | Carrito inválido | Validación | id inexistente / qty 0 | No agrega |
| P24 | Login/logout sesión | Integración / seguridad | Login admin + logout | Sesión limpia |
| P25 | Sin autenticar | Seguridad | Estado inicial | No admin/cliente |
| P26 | Confirmar express | Funcional | Validar + confirmar | Orden MASS-, carrito vacío, envío 4.9 |
| P27 | Confirmar retiro | Funcional | Validar + confirmar | Envío 0 |
| P28 | Crear producto admin | Integración | Crear válido | +1 SKU en sesión |
| P29 | Crear vacío | Validación | Alta vacía | No persiste |
| P30 | Editar producto | Regresión / integración | Guardar id=1 | Override en sesión |
| P31 | Filtro catálogo | Funcional | cat Lácteos / q primor | Resultados coherentes |
| P32 | SQLite laboratorio | Integración BD aislada | Insert/select memoria | Lee lo guardado |

## 4. Alcance y fuera de alcance

- **En alcance:** lógica PHP en `includes/`, flujos de sesión, reglas de checkout y productos admin.
- **Fuera de alcance (manual / no automatizado aquí):** pixel-perfect Figma, navegación visual completa E2E con Playwright (opcional a futuro), despliegue SonarQube (lo ejecuta el estudiante).
