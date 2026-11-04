# PROMPT 24: CI con GitHub Actions, composer audit y composer.lock

**Intent:** INT-006  
**Bolts:** INT-006-BOLT-008  
**HU:** INT-006-HU-24  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en PHP 8.1+, automatización CI/CD, GitHub Actions, Composer, seguridad de dependencias y pruebas automatizadas.

Trabaja sobre el proyecto existente. Antes de cambiar nada, inspecciona Composer, `.gitignore` y los comandos de prueba para que el flujo sea compatible con ellos.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/24-ci-github-actions-composer-audit.md`

Guarda la evidencia en:

`PROMTS/evidencias/24-ci-github-actions-composer-audit/`

Reglas:

- UTF-8 y la misma estructura que los prompts anteriores.
- Si la carpeta no existe, créala.
- No agregues secretos, tokens ni credenciales.
- No presentes como exitosa una prueba que no se ejecutó.
- Si el workflow no se ejecutó en GitHub, indícalo y deja pendiente la captura en verde.
- La creación de estos archivos no autoriza cambios fuera del alcance.

## 3. Contexto

El proyecto no tiene integración continua. `composer.lock` está en `.gitignore` y no hay una auditoría de dependencias en cada Pull Request.

## 4. Objetivo

Añadir un workflow de GitHub Actions que, en cada Pull Request, instale desde `composer.lock`, audite dependencias y ejecute las pruebas, con una configuración reproducible.

## 5. Alcance

Modifica o crea únicamente:

- `.github/workflows/ci.yml`
- `.gitignore`
- `composer.json`
- `composer.lock`

Y este prompt, más la evidencia en `PROMTS/evidencias/24-ci-github-actions-composer-audit/`.

Si hace falta otro archivo, detente, explica el cambio y espera autorización.

## 6. Pasos

1. Revisa `composer.json`, `.gitignore`, la versión de PHP, las extensiones y que los comandos de prueba existan.
2. Quita `composer.lock` de `.gitignore`. Genera o actualiza el lock con las dependencias ya declaradas. No actualices de más y no agregues dependencias.
3. Crea `.github/workflows/ci.yml` para cada Pull Request, con PHP 8.2 y las extensiones necesarias. Los pasos obligatorios son: obtener el código, configurar PHP, configurar Composer, `composer install` con el lock, `composer audit`, `composer test` y `php tests/run_all.php`. El workflow debe fallar si uno de ellos falla. No ocultes errores de pruebas ni de auditoría.
4. Añade SonarQube solo si hay servidor y secretos de GitHub (`SONAR_TOKEN`, `SONAR_HOST_URL`). Si no existen, deja constancia y no hagas fallar el flujo principal. No inventes tokens ni credenciales.
5. Explica dentro del workflow, con nombres de paso y mensajes, cómo ver una ejecución, una prueba fallida, una vulnerabilidad de Composer y un error de configuración. No crees archivos de documentación fuera del alcance.
6. Valida el YAML y ejecuta en local los comandos disponibles. No afirmes que GitHub Actions ya corrió si no se lanzó allí. No hagas push.
7. Guarda el prompt y la evidencia. La captura en verde solo si sale de una ejecución real.

## 7. Restricciones

- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No agregues dependencias ni secretos al repositorio.
- No desactives pruebas ni reglas de SonarQube para ocultar errores.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push.
- Un commit local en la rama de INT-006-BOLT-008, con el mensaje `tipo(INT-006-BOLT-008): descripción`, solo si Git está disponible.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 8. Criterios de aceptación

1. `.github/workflows/ci.yml` existe y el YAML es válido.
2. Se ejecuta en cada Pull Request.
3. Usa PHP 8.2 y las extensiones necesarias.
4. Ejecuta `composer install`, `composer audit`, `composer test` y `php tests/run_all.php`.
5. Falla si esas pruebas fallan.
6. `composer.lock` ya no está ignorado y queda listo para versionarse.
7. `composer audit` no reporta vulnerabilidades altas o críticas.
8. SonarQube solo corre con configuración y secretos válidos.
9. No se agregan dependencias ni secretos.
10. El workflow explica cómo consultar cada resultado.
11. El prompt y las evidencias quedan en `PROMTS/`.

## 9. Entrega

Resume archivos, el estado real de INT-006-BOLT-008, cada comando y su resultado, la auditoría, la ruta de evidencias, si hubo ejecución en GitHub y captura en verde, el estado de SonarQube, el commit local si existe, los pendientes y el modelo de IA.
