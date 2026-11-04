# PROMPT 21: Ampliar la cobertura PHPUnit a backend/ y reproducir la suite

**Intent:** INT-001  
**Bolts:** INT-001-BOLT-002, G10 y G11  
**HU:** INT-001-HU-21  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-02  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en PHP nativo 8.1+, PHPUnit, pruebas unitarias, cobertura de código, backend, servicios, repositorios y calidad de software.

Trabaja sobre el proyecto existente. Antes de cambiar nada, inspecciona Composer, PHPUnit y las pruebas actuales. Respeta la arquitectura, las dependencias y las convenciones del proyecto.

## 2. Registro obligatorio del prompt y evidencias

Antes de implementar, guarda este prompt en:

`PROMTS/21-Ampliar-cobertura-phpunit-backend.md`

Guarda salidas y reportes en:

`PROMTS/evidencias/21-cobertura-phpunit-backend/`

Reglas:

- Si `PROMTS/` o la carpeta de evidencias no existe, créala.
- Conserva la estructura de los prompts anteriores y guarda el archivo en UTF-8.
- No incluyas credenciales ni datos sensibles.
- No inventes resultados ni marques como aprobada una prueba que no se ejecutó.
- La creación de estos archivos no autoriza cambios fuera del alcance.

## 3. Contexto

El enunciado describe que `phpunit.xml` medía solo `includes/` y que la cobertura reportada era 58.44%. También consta el error `Cannot open bootstrap script tests/bootstrap.php` en un entorno restringido.

Hay que reproducir la suite en este equipo, corregir la inicialización de PHPUnit si vuelve a aparecer, medir `backend/` junto con `includes/` y añadir pruebas de servicios y repositorios que todavía no tengan cobertura suficiente.

## 4. Objetivo

Reproducir la suite completa de PHPUnit, incorporar `backend/` a la medición de cobertura, añadir pruebas de los servicios y repositorios pendientes y fijar un umbral mínimo que no se baje para hacer pasar la ejecución.

## 5. Alcance

Modifica o crea únicamente:

- `phpunit.xml`
- `tests/bootstrap.php`
- `tests/**`
- `composer.json`
- `docs/pruebas/evidencias/`

Y este prompt, más la evidencia en `PROMTS/evidencias/21-cobertura-phpunit-backend/`.

Si hace falta otro archivo, detente, explica el cambio y espera autorización.

## 6. Pasos

1. Inspecciona `composer.json`, `phpunit.xml`, `tests/bootstrap.php`, `tests/`, los servicios y repositorios de `backend/` y los casos P01–P32. Anota versión de PHP y PHPUnit, autoload, ruta real del bootstrap y la cobertura actual.
2. Ejecuta `composer install` y `composer test`. Si aparece `Cannot open bootstrap script tests/bootstrap.php`, corrige existencia, ruta, permisos, codificación, sintaxis y autoload dentro del alcance. No crees un bootstrap alternativo ni quites la inicialización que las pruebas ya usan.
3. Incluye `backend/` y mantén `includes/` en el código fuente medido. Excluye solo vistas u otros archivos que no sean lógica ejecutable, con justificación. No excluyas servicios, repositorios ni lógica de negocio por tener poca cobertura. No midas los tests como código productivo.
4. Añade pruebas de comportamiento real para servicios y repositorios pendientes: CRUD, validación, errores, límites, relaciones, consultas preparadas, comillas y caracteres especiales, y caminos alternativos. No dupliques pruebas ni debilites P01–P32. Si un repositorio necesita MySQL, usa solo la base de pruebas aislada.
5. Deja `composer test:coverage` generando los reportes y aplicando un umbral mínimo que falle si no se alcanza. Fija el umbral después de medir. No lo bajes para conseguir un verde. Si la medición inicial no permite un umbral viable, informa y pide autorización antes de debilitar el criterio.
6. Genera `junit.xml`, `clover.xml` y la salida de consola en `docs/pruebas/evidencias/`, y copia la evidencia de esta ejecución a `PROMTS/evidencias/21-cobertura-phpunit-backend/`.

## 7. Restricciones

- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- `composer install` solo instala lo ya declarado. No agregues paquetes nuevos.
- No desactives pruebas ni reglas de SonarQube.
- No uses comentarios para esconder incidencias.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push.
- Un commit local por bolt, formato `tipo(INT-XXX-BOLT-NNN): descripción`, solo si git está disponible. No instales git.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.
- No declares el trabajo terminado solo porque PHPUnit arranca. Hay que verificar P01–P32, las pruebas nuevas, la cobertura de `backend/` y el umbral.

## 8. Criterios de aceptación

1. `composer install` termina bien o queda documentado el impedimento exacto.
2. `composer test` ejecuta PHPUnit sin error de bootstrap.
3. P01–P32 siguen pasando.
4. Las pruebas nuevas de servicios y repositorios se ejecutan.
5. `phpunit.xml` mide `backend/` e `includes/`.
6. Las exclusiones se limitan a lo que no es lógica de producción medible.
7. La cobertura de `backend/` queda en los reportes.
8. Existe `composer test:coverage`.
9. El umbral mínimo está configurado y no se redujo para forzar el resultado.
10. Se generan `junit.xml`, `clover.xml` y la salida de consola de la ejecución reciente.

## 9. Pruebas obligatorias

1. `composer install`
2. `composer test`
3. `php tests/run_all.php`
4. `php scripts/smoke_views.php`
5. `php scripts/smoke_auth_logout.php`
6. `composer test:coverage`

La evidencia debe incluir junit, clover, consola, P01–P32, casos nuevos, cobertura global, cobertura de `backend/`, umbral, aprobadas/fallidas/omitidas, y fecha y hora.
