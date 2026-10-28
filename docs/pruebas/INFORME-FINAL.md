# Informe final — Pruebas y Calidad de Software

## 1. Portada

| Campo | Valor |
| --- | --- |
| Nombre del estudiante | [NOMBRE] |
| Código / ID | [CÓDIGO] |
| Docente | [DOCENTE] |
| Asignatura | Pruebas y Calidad de Software |
| Proyecto | Mass Online — Tiendas Mass (`paginaweb`) |
| Fecha | [FECHA] |

## 2. Descripción del proyecto

Aplicación web académica **Mass Online** (PHP 8.2 nativo + XAMPP): storefront en `cliente_interfaces/`, checkout con reglas retiro/express, autenticación SGC admin/cliente y panel admin (dashboard, productos, inventario, pedidos). Persistencia demo mediante **sesiones PHP** (sin MySQL de desarrollo). Suite de calidad con **PHPUnit + PCOV** y preparación SonarQube.

Detalle: `docs/pruebas/01-plan-de-pruebas.md`.

## 3. Identificación y planificación de las pruebas

Se planificaron **32 casos (P01–P32)** cubriendo unitarias, integración/persistencia, funcionales, validación de datos, seguridad y regresión, alineados a riesgos reales (checkout, auth, alta de productos WMS).

Ver matriz completa en `01-plan-de-pruebas.md`.

## 4. Ejecución de las pruebas y resultados obtenidos

Corrida con cobertura (evidencia `evidencias/ejecucion-automatizada.txt`):

| Métrica | Valor |
| --- | --- |
| Ejecutados | 32 |
| Aprobados | 32 |
| Fallidos | 0 |
| Bloqueados | 0 |
| % aprobación | 100% |
| Tiempo | 1.639 s |
| Cobertura líneas | 58.30% (811/1391) |

Tabla caso a caso: `02-ejecucion-pruebas.md`.

## 5. Implementación y ejecución de las pruebas automatizadas

- Framework: PHPUnit 11.5
- Ubicación: `tests/Unit`, `tests/Feature`, `tests/Support`
- Comando único: `composer test:coverage`
- Reportes: JUnit, Clover, HTML cobertura, TestDox

Detalle y mapeo ID→test: `03-pruebas-automatizadas.md`.

## 6. Comparación entre pruebas manuales y automatizadas

La automatización cubre reglas y persistencia; lo visual/Figma permanece manual. Ver `04-comparacion-manual-vs-automatizada.md`.

## 7. Análisis de calidad del código con SonarQube

Configurado en `sonar-project.properties` y guía `05-guia-sonarqube.md`.

| Métrica Sonar | Valor |
| --- | --- |
| Quality Gate | [COMPLETAR TRAS SONARQUBE] |
| Bugs | [COMPLETAR TRAS SONARQUBE] |
| Vulnerabilities | [COMPLETAR TRAS SONARQUBE] |
| Code Smells | [COMPLETAR TRAS SONARQUBE] |
| Coverage | [COMPLETAR TRAS SONARQUBE] |
| Duplicated Lines (%) | [COMPLETAR TRAS SONARQUBE] |
| Maintainability / Reliability / Security | [COMPLETAR TRAS SONARQUBE] |

## 8. Conclusiones

- **Estado del software (pruebas dinámicas):** la suite automatizada de 32 casos pasa al 100% con cobertura de líneas 58.30% sobre `includes/`.
- **Defectos identificados en el armado de pruebas:** INC-01 (assert de tracking) e INC-02 (contrato de filtros de stock); sin cambios forzados a producción para “hacer pasar” tests.
- **Nivel de automatización:** alto en lógica de negocio/auth/carrito/productos; medio-bajo en UI visual.
- **SonarQube:** [COMPLETAR TRAS SONARQUBE]

## 9. Evidencias y anexos

| Recurso | Ruta |
| --- | --- |
| Plan | `docs/pruebas/01-plan-de-pruebas.md` |
| Ejecución | `docs/pruebas/02-ejecucion-pruebas.md` |
| Automatización | `docs/pruebas/03-pruebas-automatizadas.md` |
| Comparación | `docs/pruebas/04-comparacion-manual-vs-automatizada.md` |
| Guía Sonar | `docs/pruebas/05-guia-sonarqube.md` |
| Consola cruda | `docs/pruebas/evidencias/ejecucion-automatizada.txt` |
| JUnit XML | `docs/pruebas/evidencias/junit.xml` |
| TestDox HTML | `docs/pruebas/evidencias/testdox.html` |
| Clover XML | `docs/pruebas/evidencias/coverage/clover.xml` |
| Cobertura HTML | `docs/pruebas/evidencias/coverage/html/index.html` |

### Capturas sugeridas (estudiante)

| Marcador | Qué capturar |
| --- | --- |
| [CAPTURA N°1] | Terminal `composer test:coverage` con OK 32 tests |
| [CAPTURA N°2] | `coverage/html/index.html` abierto en navegador |
| [CAPTURA N°3] | Quality Gate en SonarQube |
| [CAPTURA N°4] | Panel Bugs/Vulnerabilities/Code Smells |
| [CAPTURA N°5] | Coverage en Sonar vs Clover local |
| [CAPTURA N°6] | Login admin + alta producto con errores (manual) |
