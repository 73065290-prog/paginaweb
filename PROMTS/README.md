# PROMTS

Carpeta permanente del repositorio para guardar los prompts usados en el proyecto.

- **No borrar** al actualizar código, documentación o ramas.
- Convención de nombres: `01.Nombre-del-prompt.md` en los primeros prompts y `20-nombre-del-prompt.md` a partir del prompt 20. No se reutiliza un número.
- Todo lo que esté aquí se versiona en Git.
- La plantilla de un prompt nuevo está en [`PLANTILLA.md`](./PLANTILLA.md).

## Cómo usar la plantilla

1. Copia `PROMTS/PLANTILLA.md` con el siguiente número libre. El último prompt es el 30. No hay prompt 06 ni prompt 19. `06.evidencia-conteo-archivos.txt` es una evidencia, no un prompt.
2. Completa Intent, HU, bolts, modelo, fecha y responsable solo con datos ya conocidos. Si un dato no existe, escribe `Pendiente` y no inventes un identificador.
3. Redacta contexto, objetivo, alcance, pasos, restricciones, criterios, pruebas y entrega.
4. Añade una fila a la tabla de este README con el número, el archivo, el Intent y la HU.
5. Guarda la evidencia de esa ejecución en `PROMTS/evidencias/`, sin credenciales.

## Trazabilidad

| Pieza | Forma | Regla |
| :--- | :--- | :--- |
| Intent | `INT-XXX` | Sale del enunciado o de un prompt ya guardado. |
| HU | `INT-XXX-HU-NN` | El `NN` de los prompts de esta carpeta coincide con el número del prompt cuando el propio archivo lo declara. |
| Prompt | `PROMTS/NN...md` | Un número, un archivo. No se duplica el identificador. |
| Ticket | ID del sistema | Solo se anota si Trello o Jira devolvieron ese ID. |
| Bolt | `INT-XXX-BOLT-NNN` | Solo si el prompt o la matriz ya lo asignan. «Transversal» no es un ID. |
| Rama | `tipo/int-xxx-bolt-nnn` | Prefijos ya usados: `feature/`, `fix/`, `mejora/`, `docs/`. |
| Commit | `tipo(INT-XXX-BOLT-NNN): descripción` | Un commit local por bolt completado. Sin bolt asignado, no se fabrica el ID. |

La rama cita el bolt. El encabezado del prompt cita Intent, HU y bolts. El mensaje de commit repite el bolt. El ticket, cuando exista, se agrega en el prompt o en este README con el ID verificable.

## Tickets

No hay integración disponible con Trello ni con Jira en esta sesión. No se creó ningún ticket. Para crearlos hacen falta el sistema, el tablero o la clave del proyecto, y la autorización para escribir en él. Hasta entonces los tickets de estos prompts quedan pendientes y sin ID.

## Índice de prompts registrados

| Nº | Archivo | Intent | HU | Descripción |
| :---: | :--- | :---: | :---: | :--- |
| **01** | [`01.Correccion-de-incidencias-de-SonarQube.md`](./01.Correccion-de-incidencias-de-SonarQube.md) | `INT-003` | `INT-003-HU-01` | Corrección de incidencias de SonarQube (accesibilidad y `Number.parse`). |
| **02** | [`02.Refactorizacion-frontend-backend-mysql.md`](./02.Refactorizacion-frontend-backend-mysql.md) | `INT-004` | `INT-004-HU-02` | Refactorización frontend/backend e implementación de MySQL (referencia histórica; sustituido por prompts 06-19). |
| **03** | [`03.Correccion-logout-cambio-de-rol.md`](./03.Correccion-logout-cambio-de-rol.md) | `INT-005` | `INT-005-HU-03` | Corrección de logout y cambio de rol admin y cliente (`evidencia-prompt-03.txt`). |
| **04** | [`04.Registro-suite-pruebas-phpunit.md`](./04.Registro-suite-pruebas-phpunit.md) | `INT-001` | `INT-001-HU-04` | Registro retroactivo de suite PHPUnit (`P01`–`P32`), informe QA y preparación SonarQube (`evidencia-prompt-04.txt`). |
| **05** | [`05.Registro-mejoras-seguridad-accesibilidad.md`](./05.Registro-mejoras-seguridad-accesibilidad.md) | `INT-002` | `INT-002-HU-05` | Registro retroactivo de las 14 mejoras de seguridad, fiabilidad, accesibilidad y documentación por ramas (`INT-002-BOLT-001` a `014`). |
| **07** | [`07.Crear-backend-models-entidades.md`](./07.Crear-backend-models-entidades.md) | `INT-006` | `INT-006-HU-07` | Creación de la capa `backend/models/` con las 8 entidades tipadas del sistema y sus pruebas unitarias (`INT-006-BOLT-001`, `docs/pruebas/evidencias/07-modelos-backend.txt`). |
| **08** | [`08.Service-productos-inventario.md`](./08.Service-productos-inventario.md) | `INT-006` | `INT-006-HU-08` | Creación de `ProductoService`, `InventarioService` y `ProductoValidator` (`INT-006-BOLT-002`, `docs/pruebas/evidencias/08-service-productos-inventario.txt`). |
| **09** | [`09.Service-carrito-cupones.md`](./09.Service-carrito-cupones.md) | `INT-006` | `INT-006-HU-09` | Refactorización de lógica de carrito y cupones hacia `CarritoService` y `CuponService` (`INT-006-BOLT-002`, `docs/pruebas/evidencias/09-service-carrito-cupones.txt`). |
| **10** | [`10.Service-pedidos-checkout.md`](./10.Service-pedidos-checkout.md) | `INT-006` | `INT-006-HU-10` | Centralización de lógica de pedidos, validación de entrega y checkout en `PedidoService` y `PedidoValidator` (`INT-006-BOLT-002`, `docs/pruebas/evidencias/10-service-pedidos-checkout.txt`). |
| **11** | [`11.AuthService-real-login-logout.md`](./11.AuthService-real-login-logout.md) | `INT-006` | `INT-006-HU-11` | Centralización de autenticación, sesión y cambio de rol en `AuthService`, `AuthController` y `UsuarioRepository` (`INT-006-BOLT-002`, `docs/pruebas/evidencias/11-authservice-real-login-logout.txt`). |
| **12** | [`12.Controllers-router-real.md`](./12.Controllers-router-real.md) | `INT-006` | `INT-006-HU-12` | Controllers y router real a partir de `backend/routes/web.php` (`INT-006-BOLT-003`, `12.evidencias-pruebas.txt`). |
| **13** | [`13.Endpoints-json-carrito-stock.md`](./13.Endpoints-json-carrito-stock.md) | `INT-006` | `INT-006-HU-13` | Endpoints JSON para carrito y stock consumidos con `fetch()` (`INT-006-BOLT-004`, `13.evidencias-pruebas.txt`). |
| **14** | [`14.Middleware-validators.md`](./14.Middleware-validators.md) | `INT-004` | `INT-004-HU-14` | Conexión de middleware y validators o eliminación del código muerto (`INT-004-BOLT-005` y `INT-004-BOLT-006`, `14.evidencias-pruebas.txt`). |
| **15** | [`15.Limpieza-archivos-duplicados.md`](./15.Limpieza-archivos-duplicados.md) | `INT-006` | `INT-006-HU-15` | Limpieza de copias heredadas en `assets/` e `includes/` y consolidación en `frontend/` (`INT-006-BOLT-005`, `15.evidencias-pruebas.txt`). |
| **16** | [`16.Verificacion-importacion-base-datos.md`](./16.Verificacion-importacion-base-datos.md) | `INT-004` | `INT-004-HU-16` | Verificación de la importación de `schema.sql` y `seed.sql`, corrección de `generate_seed.php` y nuevo `scripts/verificar_bd.php` (`INT-004-BOLT-003`, `16.evidencias-pruebas.txt`). |
| **17** | [`17.Pruebas-integracion-repositorios-mysql.md`](./17.Pruebas-integracion-repositorios-mysql.md) | `INT-006` | `INT-006-HU-17` | Pruebas de integración de repositorios con MySQL (`INT-006-BOLT-006`). |
| **18** | [`18.Retiro-fallback-demo-aviso-modo-demo.md`](./18.Retiro-fallback-demo-aviso-modo-demo.md) | `INT-004` | `INT-004-HU-18` | Retiro del fallback demo y aviso de modo demo (`INT-004-BOLT-002`, `INT-004-BOLT-004`). |
| **20** | [`20-Actualizar-sonar-y-reejecutar-analisis.md`](./20-Actualizar-sonar-y-reejecutar-analisis.md) | `INT-006` | `INT-006-HU-20` | Actualizar SonarQube y volver a analizar (`INT-006-BOLT-007`). |
| **21** | [`21-Ampliar-cobertura-phpunit-backend.md`](./21-Ampliar-cobertura-phpunit-backend.md) | `INT-001` | `INT-001-HU-21` | Cobertura PHPUnit de backend (`INT-001-BOLT-002`; G10 y G11 sin definición en el repositorio). |
| **22** | [`22-Validacion-accesibilidad-labels-aria-toast.md`](./22-Validacion-accesibilidad-labels-aria-toast.md) | `INT-002` | `INT-002-HU-22` | Validación de accesibilidad de labels, ARIA y toast. |
| **23** | [`23-Pruebas-cabeceras-http-csrf-admin.md`](./23-Pruebas-cabeceras-http-csrf-admin.md) | `INT-002` | `INT-002-HU-23` | Pruebas de cabeceras HTTP y CSRF admin. |
| **24** | [`24-ci-github-actions-composer-audit.md`](./24-ci-github-actions-composer-audit.md) | `INT-006` | `INT-006-HU-24` | CI con GitHub Actions y auditoría de Composer (`INT-006-BOLT-008`). |
| **25** | [`25-resolver-ramas-pull-request.md`](./25-resolver-ramas-pull-request.md) | `INT-006` | `INT-006-HU-25` | Ramas sin fusionar y flujo de pull request (`INT-006-BOLT-009`). |
| **26** | [`26-completar-informe-final-documentacion.md`](./26-completar-informe-final-documentacion.md) | `INT-006` | `INT-006-HU-26` | Informe final y documentación (`INT-006-BOLT-010`, `INT-001-BOLT-004`). |
| **27** | [`27-unificar-codificacion-utf8.md`](./27-unificar-codificacion-utf8.md) | `INT-001` | `INT-001-HU-27` | Unificar textos en UTF-8 sin BOM (`INT-001-BOLT-005`; G21 sin definición en el repositorio). |
| **28** | [`28-gestion-credenciales-demo-autenticacion.md`](./28-gestion-credenciales-demo-autenticacion.md) | `INT-001` | `INT-001-HU-28` | Credenciales demo y autenticación (`INT-001-BOLT-006`; G22 sin definición en el repositorio). |
| **29** | [`29-despliegue-prueba-evidencia.md`](./29-despliegue-prueba-evidencia.md) | `INT-006` | `INT-006-HU-29` | Despliegue de prueba opcional. Columnas W y AZ de la matriz; sin ID de bolt. |
| **30** | [`30-plantilla-estandar-prompts-modelo-ia-tickets.md`](./30-plantilla-estandar-prompts-modelo-ia-tickets.md) | `INT-006` | `INT-006-HU-30` | Plantilla estándar, registro de modelo y tickets. Bolt indicado como transversal, sin ID. |

No hay prompt **06** ni prompt **19**. Esos números no se reasignan. `06.evidencia-conteo-archivos.txt` no ocupa el lugar del prompt 06.
