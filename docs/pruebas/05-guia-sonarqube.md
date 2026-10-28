# Guía SonarQube (ejecución a cargo del estudiante)

> Esta guía **no** instala ni levanta SonarQube. Solo indica cómo analizar este repo cuando ya tengas SonarQube Server/Community y `sonar-scanner`.

## 1. Prerrequisitos

1. SonarQube en ejecución (p. ej. `http://localhost:9000`).
2. Token de usuario → variable de entorno `SONAR_TOKEN`.
3. Reportes generados en este proyecto:

```bat
cd d:\xampp\htdocs\paginaweb
composer install
composer test:coverage
```

Verificar existencia de:
- `docs/pruebas/evidencias/coverage/clover.xml`
- `docs/pruebas/evidencias/junit.xml`

## 2. Comando sonar-scanner (Windows)

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

## 3. Plantilla de métricas — [COMPLETAR TRAS SONARQUBE]

| Métrica | Valor | Interpretación |
| --- | --- | --- |
| Quality Gate | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Bugs | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Vulnerabilities | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Code Smells | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Coverage | [COMPLETAR TRAS SONARQUBE] (referencia local PHPUnit: 58.30% líneas) | [COMPLETAR TRAS SONARQUBE] |
| Duplicated Lines (%) | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Maintainability Rating | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Reliability Rating | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |
| Security Rating | [COMPLETAR TRAS SONARQUBE] | [COMPLETAR TRAS SONARQUBE] |

## 4. Ocho preguntas de la guía — [COMPLETAR TRAS SONARQUBE]

1. ¿El Quality Gate pasó o falló? ¿Por qué? — [COMPLETAR TRAS SONARQUBE]
2. ¿Qué problemas se identificaron? — [COMPLETAR TRAS SONARQUBE]
3. ¿Cuántos bugs / vulnerabilidades / code smells hay? — [COMPLETAR TRAS SONARQUBE]
4. ¿Cuál es la cobertura reportada? — [COMPLETAR TRAS SONARQUBE]
5. ¿Qué partes del código requieren atención? — [COMPLETAR TRAS SONARQUBE]
6. ¿Qué corregir primero y por qué? — [COMPLETAR TRAS SONARQUBE]
7. ¿Relación entre las pruebas PHPUnit y el análisis estático? — [COMPLETAR TRAS SONARQUBE]
8. ¿Qué mejoras concretas propondrías? — [COMPLETAR TRAS SONARQUBE]

## 5. Lista priorizada de mejoras — [COMPLETAR TRAS SONARQUBE]

1. [COMPLETAR TRAS SONARQUBE]
2. [COMPLETAR TRAS SONARQUBE]
3. [COMPLETAR TRAS SONARQUBE]

## 6. Notas de higiene previas (revisión estática ligera sin Sonar)

| Hallazgo | Acción |
| --- | --- |
| Credenciales demo `123456` en `mass_usuarios_auth()` | Intencional para la demo académica; no son secretos de producción. Documentado; no se eliminan para no romper el login de la asignatura. |
| Sin `var_dump`/`dd()` en fuentes revisadas | OK |
| Cobertura Clover alineada a `sonar.php.coverage.reportPaths` | OK |
