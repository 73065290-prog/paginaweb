# Guía SonarQube (ejecución a cargo del estudiante)

> Esta guía **no** instala ni levanta SonarQube. Solo indica cómo analizar este repo cuando ya tengas SonarQube Server/Community y `sonar-scanner`.

## 0. Estructura del proyecto lista para Sonar

```
paginaweb/
├── sonar-project.properties          ← configuración del análisis
├── scripts/
│   ├── prepare-sonar.bat             ← genera Clover + JUnit
│   └── scan-sonar.bat                ← lanza sonar-scanner
├── includes/                         ← lógica PHP (cobertura principal)
├── admin/ · cliente_interfaces/      ← UI PHP
├── assets/js/                        ← JS analizado (sin cobertura PHP)
├── tests/                            ← PHPUnit (Unit + Feature)
└── docs/pruebas/evidencias/
    ├── junit.xml
    └── coverage/clover.xml
```

| Pieza | Rol en Sonar |
| --- | --- |
| `sonar.sources` | Código a analizar (bugs, smells, vulnerabilidades) |
| `sonar.tests` | Suite PHPUnit |
| `sonar.php.coverage.reportPaths` | Cobertura Clover |
| `sonar.php.tests.reportPath` | Resultado de ejecución JUnit |

## 1. Prerrequisitos

1. SonarQube en ejecución (p. ej. `http://localhost:9000`).
2. Token de usuario → variable de entorno `SONAR_TOKEN`.
3. `sonar-scanner` en el PATH.
4. Reportes generados en este proyecto.

## 2. Preparar reportes (obligatorio antes del scan)

```bat
cd d:\xampp\htdocs\paginaweb
scripts\prepare-sonar.bat
```

Equivalente manual:

```bat
cd d:\xampp\htdocs\paginaweb
composer install
composer test:coverage
```

Verificar existencia de:
- `docs/pruebas/evidencias/coverage/clover.xml`
- `docs/pruebas/evidencias/junit.xml`

## 3. Comando sonar-scanner (Windows)

Opción recomendada:

```bat
cd d:\xampp\htdocs\paginaweb
set SONAR_TOKEN=TU_TOKEN_AQUI
scripts\scan-sonar.bat
```

Opción manual:

```bat
cd d:\xampp\htdocs\paginaweb
set SONAR_TOKEN=TU_TOKEN_AQUI
sonar-scanner -Dsonar.host.url=http://localhost:9000 -Dsonar.token=%SONAR_TOKEN%
```

Si usas SonarCloud:

```bat
sonar-scanner -Dsonar.organization=TU_ORG -Dsonar.host.url=https://sonarcloud.io -Dsonar.token=%SONAR_TOKEN%
```

La configuración de sources/tests/coverage está en `sonar-project.properties` (raíz del repo).

## 4. Higiene de código aplicada antes del análisis

| Hallazgo típico Sonar | Acción realizada | Archivo |
| --- | --- | --- |
| Credenciales en texto plano (S2068) | Passwords demo → `password_hash` + `password_verify` (login sigue siendo `123456`) | `includes/functions.php` |
| Bucles duplicados EAN/nombre | Un solo recorrido del catálogo en validación | `includes/data.php` |
| `console.log` / `var_dump` / `dd()` | No encontrados en fuentes | — |
| SQL concatenado | La aplicación sí usa MySQL/MariaDB con PDO. Los repositorios revisados preparan las consultas (`backend/repositories/ProductoRepository.php`). No hay un informe Sonar de esta fecha que cuente hallazgos | — |
| Vendor / evidencias / CSS | Excluidos en `sonar-project.properties` | raíz |

## 5. Métricas — pendientes tras el intento del 2026-11-02

Fuente: `PROMTS/evidencias/20-sonarqube-reescaneo.txt`. El análisis no se envió. No se estiman cifras. El 58.30% de `ejecucion-automatizada.txt` es de otra máquina (2026-10-28) y no se anota como cobertura de Sonar.

| Métrica | Valor | Interpretación |
| --- | --- | --- |
| Quality Gate | Pendiente. No disponible | No hubo análisis que el servidor pudiera calificar |
| Bugs | Pendiente. No disponible | Sin informe de issues de esta ejecución |
| Vulnerabilities | Pendiente. No disponible | Sin informe de issues de esta ejecución |
| Code Smells | Pendiente. No disponible | Sin informe de issues de esta ejecución |
| Coverage | Pendiente. No disponible | No se importó Clover de este equipo. Aquí no hay `php_pcov.dll` ni `php_xdebug.dll` |
| Duplicated Lines (%) | Pendiente. No disponible | Sin análisis |
| Maintainability Rating | Pendiente. No disponible | Sin análisis |
| Reliability Rating | Pendiente. No disponible | Sin análisis |
| Security Rating | Pendiente. No disponible | Sin análisis |

## 6. Ocho preguntas de la guía

1. ¿El Quality Gate pasó o falló? ¿Por qué? — Pendiente. No se puede responder: el escaneo no llegó al servidor el 2026-11-02 porque no había `SONAR_TOKEN`.
2. ¿Qué problemas se identificaron? — Ninguno atribuible a este análisis. No hay listado de issues.
3. ¿Cuántos bugs / vulnerabilidades / code smells hay? — No disponible.
4. ¿Cuál es la cobertura reportada? — Sonar no reportó cobertura. PHPUnit en este equipo tampoco produjo un porcentaje nuevo.
5. ¿Qué partes del código requieren atención? — Pendiente. Sin issues no se señala un archivo por una métrica que no existe.
6. ¿Qué corregir primero y por qué? — Pendiente del informe de Sonar. El bloqueo comprobado para poder medir es la falta de token y de driver de cobertura, no un smell ya clasificado.
7. ¿Relación entre las pruebas PHPUnit y el análisis estático? — `phpunit.xml` puede generar Clover y `sonar-project.properties` declara esa ruta. En este equipo el Clover no se generó y el escaneo no se envió, así que las dos herramientas todavía no se cruzaron con números.
8. ¿Qué mejoras concretas propondrías? — No se propone una lista de correcciones de código a partir de un Quality Gate inexistente.

## 7. Lista priorizada de pasos que faltan para tener métricas

1. Definir `SONAR_TOKEN` fuera del repositorio y volver a ejecutar `scripts\scan-sonar.bat`.
2. Disponer de PCOV o Xdebug en el PHP 8.2.12 de este XAMPP si se quiere un Clover de este árbol. No se instaló en esta revisión.
3. Copiar al informe solo las cifras que muestre el panel después de ese análisis.

## 8. Capturas sugeridas (para el informe)

1. Dashboard del proyecto en SonarQube (Quality Gate + ratings).
2. Pestaña **Issues** (bugs / vulnerabilities / smells).
3. Pestaña **Coverage** con % y archivos de `includes/`.
4. Pestaña **Measures** (duplicación, maintainability).
5. Consola tras `scripts\scan-sonar.bat` (EXECUTION SUCCESS).

Ninguna de estas cinco imágenes está en el repositorio. La pantalla de login de `PROMTS/evidencias/20-sonarqube-login-requerido.png` no las reemplaza. Las capturas N.º 1 a N.º 6 del informe están en [INFORME-FINAL.md](INFORME-FINAL.md) y también siguen pendientes.
