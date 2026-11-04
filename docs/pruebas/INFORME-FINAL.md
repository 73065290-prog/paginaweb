# Informe final — Pruebas y Calidad de Software

Este informe no está cerrado. Faltan el nombre, el código y el docente, las métricas de SonarQube, la cobertura medida en este equipo y las capturas N.º 1 a N.º 6.

## 1. Portada

| Campo | Valor |
| --- | --- |
| Nombre del estudiante | Pendiente de confirmación |
| Código / ID | Pendiente de confirmación |
| Docente | Pendiente de confirmación |
| Asignatura | Pruebas y Calidad de Software |
| Proyecto | Mass Online — Tiendas Mass (`paginaweb`) |
| Fecha de esta revisión | 2026-11-03 |

La fecha de la portada es la de esta revisión del documento. No es la fecha de un análisis de SonarQube. El repositorio tiene autores de Git distintos y ninguno fue confirmado para estos tres campos.

## 2. Descripción del proyecto

Aplicación web académica **Mass Online** (PHP 8.2 nativo + XAMPP): storefront en `cliente_interfaces/`, checkout con reglas retiro/express, autenticación SGC admin/cliente y panel admin (dashboard, productos, inventario, pedidos).

La persistencia está en `backend/Database.php` y usa PDO contra **MySQL/MariaDB** cuando el modo es `mysql`. Hay tres modos:

| Modo | Cuándo | Dónde quedan los datos |
| --- | --- | --- |
| `mysql` | Se pidió base de datos y PDO conectó | Tablas de `mass_online` |
| `demo` | `MASS_USE_DB` es `0` o `false`, o la configuración no pide base (`enabled` false; es el caso si no existe `database.local.php`) | Sesión PHP y datos demo de `includes/data.php`. No se escriben en MySQL |
| `error` | Se pidió MySQL y la conexión falló | No se activa el modo demo. El admin muestra «Sin conexión a MySQL» |

`phpunit.xml` fija `MASS_USE_DB=0`. Esa suite está pensada para no usar la base de desarrollo. El laboratorio P32 sigue siendo SQLite `:memory:` y no sustituye a MySQL.

Detalle del plan original: [01-plan-de-pruebas.md](01-plan-de-pruebas.md).

## 3. Identificación y planificación de las pruebas

El plan documenta **32 casos (P01–P32)**: unitarias, integración/persistencia, funcionales, validación de datos, seguridad y regresión.

Ese plan ya no describe toda la suite. `phpunit.xml` también incluye `tests/Integration`, y el `junit.xml` que está hoy en la carpeta de evidencias tiene 130 pruebas. La matriz P01–P32 sigue en [01-plan-de-pruebas.md](01-plan-de-pruebas.md).

## 4. Ejecución de las pruebas y resultados obtenidos

Hay dos ejecuciones distintas. No se mezclan.

### 4.1 Corrida histórica con PCOV

Fuente única: [evidencias/ejecucion-automatizada.txt](evidencias/ejecucion-automatizada.txt).

| Dato del archivo | Valor |
| --- | --- |
| PHPUnit | 11.5.0 |
| PHP | 8.2.12 con PCOV 1.0.11 |
| Configuración | `D:\xampp\htdocs\paginaweb\phpunit.xml` |
| Resultado | OK (32 tests, 99 assertions) |
| Tiempo de PHPUnit | 00:01.639 |
| Informe de cobertura | 2026-10-28 01:29:33 |
| Líneas | 58.30% (811/1391) |

El HTML [evidencias/coverage/html/index.html](evidencias/coverage/html/index.html) también es de esa otra ruta: el título dice `Code Coverage for D:\xampp\htdocs\paginaweb\includes` y el total que pinta es **58.44% (817/1398)**. No coincide con el 58.30% (811/1391) del texto. Ninguno de los dos es la medición de `C:\xampp`. El 0.402 s que figuraba antes en este informe no aparece en esos archivos.

### 4.2 Este equipo

| Evidencia | Qué dice | Cobertura |
| --- | --- | --- |
| [evidencias/junit.xml](evidencias/junit.xml) | 130 tests, 770 assertions, 0 errors, 0 failures, 0 skipped. Rutas de `C:\Users\LENOVO\Downloads\paginaweb (2)\paginaweb` | No incluye porcentaje. No hay `clover.xml` junto a este JUnit |
| `PROMTS/evidencias/21-cobertura-phpunit-backend/resumen.txt` | 2026-11-02. `php tests/run_coverage.php`: 130 tests, 770 assertions y el aviso `No code coverage driver available`. Exit code 1 | No disponible. No hay `php_pcov.dll` ni `php_xdebug.dll` en `C:\xampp\php\ext` |
| `PROMTS/evidencias/24-ci-github-actions-composer-audit/comandos.txt` | 2026-11-03. PHPUnit 11.5.50. `composer test`: 134 tests, 794 assertions, 1 fallo (`ProductoAdminPersistenciaTest::test_crear_producto_valido_persiste_en_sesion_admin`) | No medida. La misma evidencia dice que la suite llegó a la base `mass_online` y que el producto 1 se restauró al seed |

La tabla caso a caso de los 32 casos históricos está en [02-ejecucion-pruebas.md](02-ejecucion-pruebas.md). No se presenta como el resultado de la suite actual.

## 5. Implementación y ejecución de las pruebas automatizadas

- Framework declarado en el lock actual: PHPUnit 11.5.50 (`composer.json`). La corrida de la sección 4.1 usó 11.5.0.
- Ubicación: `tests/Unit`, `tests/Feature`, `tests/Integration`, `tests/Support`.
- `phpunit.xml` mide `includes/` y `backend/`, y excluye `backend/config`.
- En este equipo el comando de cobertura no generó Clover: falta el driver. El detalle del mapeo P01–P32 está en [03-pruebas-automatizadas.md](03-pruebas-automatizadas.md).

## 6. Comparación entre pruebas manuales y automatizadas

La automatización del plan cubre reglas, sesión y el laboratorio SQLite. Lo visual permanece manual. Ver [04-comparacion-manual-vs-automatizada.md](04-comparacion-manual-vs-automatizada.md).

## 7. Análisis de calidad del código con SonarQube

Configuración vigente en `sonar-project.properties`: clave `Pagina_Web_V2`. Fuentes: `includes`, `backend`, `frontend`, `admin`, `cliente_interfaces`, `index.php`, `logout.php`. Guía: [05-guia-sonarqube.md](05-guia-sonarqube.md).

| Métrica Sonar | Valor | Fuente |
| --- | --- | --- |
| Quality Gate | No disponible | Intento del 2026-11-02. El análisis no se envió |
| Bugs | No disponible | La misma ejecución |
| Vulnerabilities | No disponible | La misma ejecución |
| Code Smells | No disponible | La misma ejecución |
| Coverage | No disponible | Sonar no recibió un análisis ni un Clover de este equipo |
| Duplicated Lines (%) | No disponible | La misma ejecución |
| Maintainability / Reliability / Security | No disponible | La misma ejecución |

Registro: [../../PROMTS/evidencias/20-sonarqube-reescaneo.txt](../../PROMTS/evidencias/20-sonarqube-reescaneo.txt).

Hechos comprobados de ese registro:

- El servidor local respondió HTTP 200 en `http://127.0.0.1:9000/api/system/status`: SonarQube Community 26.10.0.133078, id `147B411E-AaFcHgWAYUQ3W8oeAG7p`, estado UP.
- No había `SONAR_TOKEN`. El login de instalación respondió HTTP 401.
- `scripts\scan-sonar.bat` terminó con código 1: `ERROR: define SONAR_TOKEN antes de escanear.`
- `scripts\prepare-sonar.bat` ejecutó PHPUnit 11.5.0 (122 tests, 659 assertions) y avisó `No code coverage driver available`.

El 58.30% de la sección 4.1 no se copia aquí como cobertura de Sonar ni como cobertura de este equipo.

## 8. Conclusiones

- Los 32 casos del archivo histórico de PCOV terminaron OK en otra instalación, con 58.30% de líneas (811/1391) el 2026-10-28.
- En este equipo, el JUnit guardado tiene 130 pruebas y 0 fallos, sin porcentaje de cobertura.
- La corrida del 2026-11-03 (134 pruebas) tiene 1 fallo y llegó a escribir en `mass_online`. No se puede decir que la suite actual esté en verde.
- INC-01 e INC-02 siguen documentados en la ejecución histórica como defectos del armado de pruebas, no como cambios forzados al código de producción de aquella corrida.
- SonarQube sigue sin métricas. El informe no se da por cerrado.

## 9. Evidencias y anexos

Rutas relativas a este archivo. «Ausente» significa que el enlace se mantiene y el destino no está en el disco.

| Recurso | Ruta | Estado el 2026-11-03 |
| --- | --- | --- |
| Plan | [01-plan-de-pruebas.md](01-plan-de-pruebas.md) | Presente |
| Ejecución | [02-ejecucion-pruebas.md](02-ejecucion-pruebas.md) | Presente |
| Automatización | [03-pruebas-automatizadas.md](03-pruebas-automatizadas.md) | Presente |
| Comparación | [04-comparacion-manual-vs-automatizada.md](04-comparacion-manual-vs-automatizada.md) | Presente |
| Guía Sonar | [05-guia-sonarqube.md](05-guia-sonarqube.md) | Presente |
| Accesibilidad | [accesibilidad.md](accesibilidad.md) | Presente. No es una de las capturas N.º 1 a 6 |
| Consola histórica con PCOV | [evidencias/ejecucion-automatizada.txt](evidencias/ejecucion-automatizada.txt) | Presente. Otra máquina |
| JUnit XML | [evidencias/junit.xml](evidencias/junit.xml) | Presente. 130 pruebas, sin cobertura |
| TestDox HTML | [evidencias/testdox.html](evidencias/testdox.html) | Presente |
| Clover XML | [evidencias/coverage/clover.xml](evidencias/coverage/clover.xml) | Ausente |
| Cobertura HTML | [evidencias/coverage/html/index.html](evidencias/coverage/html/index.html) | Presente, de `D:\xampp\htdocs\paginaweb\includes`, 58.44% (817/1398). No es captura ni medición de este equipo |
| Reescaneo Sonar | [../../PROMTS/evidencias/20-sonarqube-reescaneo.txt](../../PROMTS/evidencias/20-sonarqube-reescaneo.txt) | Presente |
| Cobertura de este equipo | [../../PROMTS/evidencias/21-cobertura-phpunit-backend/resumen.txt](../../PROMTS/evidencias/21-cobertura-phpunit-backend/resumen.txt) | Presente. Porcentaje no disponible |
| Suite del 2026-11-03 | [../../PROMTS/evidencias/24-ci-github-actions-composer-audit/comandos.txt](../../PROMTS/evidencias/24-ci-github-actions-composer-audit/comandos.txt) | Presente. 1 fallo |

### Capturas N.º 1 a N.º 6

| N.º | Qué pide el informe | Estado |
| --- | --- | --- |
| 1 | Terminal de `composer test:coverage` con 32 pruebas OK | Pendiente. No hay imagen. El texto histórico está en `evidencias/ejecucion-automatizada.txt` y no es una captura |
| 2 | `coverage/html/index.html` abierto en el navegador | Pendiente como captura. El HTML existe y muestra 58.44% de `D:\xampp\htdocs\paginaweb\includes`. No hay imagen del navegador y no se usa como resultado de este equipo |
| 3 | Quality Gate de SonarQube | Pendiente. No hubo análisis |
| 4 | Panel de Bugs, Vulnerabilities y Code Smells | Pendiente. No hubo análisis |
| 5 | Coverage de Sonar comparado con Clover local | Pendiente. No hay métrica de Sonar ni Clover de este equipo |
| 6 | Login admin y alta de producto con errores, en manual | Pendiente. No hay esa imagen en el repositorio |

Existe [../../PROMTS/evidencias/20-sonarqube-login-requerido.png](../../PROMTS/evidencias/20-sonarqube-login-requerido.png). Es la pantalla de login de SonarQube del 2026-11-02. No es la captura N.º 3 y no se inserta en su lugar. Las imágenes de `PROMTS/evidencias/22-accesibilidad/` son de accesibilidad y tampoco cubren estas seis capturas.
