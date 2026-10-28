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
| SQL concatenado | No aplica (sin MySQL de app) | — |
| Vendor / evidencias / CSS | Excluidos en `sonar-project.properties` | raíz |

## 5. Plantilla de métricas — [COMPLETAR TRAS SONARQUBE]

| Métrica | Valor | Interpretación |
| --- | --- | --- |
| Quality Gate | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Bugs | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Vulnerabilities | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Code Smells | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Coverage | [COMPLETAR TRAS SONARQUBE] (referencia local PHPUnit: ~58% líneas `includes/`) | [COMPLETAR TRAS SONARQUBE] |
| Duplicated Lines (%) | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Maintainability Rating | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Reliability Rating | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Security Rating | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |

## 6. Ocho preguntas de la guía — [COMPLETAR TRAS SONARQUBE]

1. ¿El Quality Gate pasó o falló? ¿Por qué? — [COMPLETAR TRAS SONARQUBE]
2. ¿Qué problemas se identificaron? — [COMPLETAR TRAS SONARQUBE]
3. ¿Cuántos bugs / vulnerabilidades / code smells hay? — [COMPLETAR TRAS SONARQUBE]
4. ¿Cuál es la cobertura reportada? — [COMPLETAR TRAS SONARQUBE]
5. ¿Qué partes del código requieren atención? — [COMPLETAR TRAS SONARQUBE]
6. ¿Qué corregir primero y por qué? — [COMPLETAR TRAS SONARQUBE]
7. ¿Relación entre las pruebas PHPUnit y el análisis estático? — [COMPLETAR TRAS SONARQUBE]
8. ¿Qué mejoras concretas propondrías? — [COMPLETAR TRAS SONARQUBE]

## 7. Lista priorizada de mejoras — [COMPLETAR TRAS SONARQUBE]

1. [COMPLETAR TRAS SONARQUBE]
2. [COMPLETAR TRAS SONARQUBE]
3. [COMPLETAR TRAS SONARQUBE]

## 8. Capturas sugeridas (para el informe)

1. Dashboard del proyecto en SonarQube (Quality Gate + ratings).
2. Pestaña **Issues** (bugs / vulnerabilities / smells).
3. Pestaña **Coverage** con % y archivos de `includes/`.
4. Pestaña **Measures** (duplicación, maintainability).
5. Consola tras `scripts\scan-sonar.bat` (EXECUTION SUCCESS).
