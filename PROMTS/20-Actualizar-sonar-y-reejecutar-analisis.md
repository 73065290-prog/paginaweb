# PROMPT 20: Actualizar sonar-project.properties y re-escanear con SonarQube

**Intent:** INT-006  
**Bolt:** INT-006-BOLT-007  
**HU:** INT-006-HU-20  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-02  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en PHP nativo 8.1+, calidad de código, análisis estático, SonarQube, PHPUnit y cobertura de pruebas.

Trabaja sobre el proyecto existente. Inspecciona primero la configuración de SonarQube, los scripts de análisis y la estructura real del código. El objetivo es analizar el código vigente de forma completa, sin ocultar incidencias ni alterar las reglas de calidad para conseguir un resultado artificialmente favorable.

## 2. Registro obligatorio del prompt y evidencias

Antes de implementar, guarda este prompt en:

`PROMTS/20-Actualizar-sonar-y-reejecutar-analisis.md`

Guarda las capturas del panel en `PROMTS/evidencias/` y el registro de comandos en:

`PROMTS/evidencias/20-sonarqube-reescaneo.txt`

Reglas:

- Si `PROMTS/` o `PROMTS/evidencias/` no existe, créala.
- Conserva la estructura de los prompts anteriores y guarda el archivo en UTF-8.
- No incluyas tokens, contraseñas ni credenciales.
- No inventes métricas ni capturas.
- La creación de estos archivos no autoriza cambios fuera del alcance.

## 3. Contexto

`sonar-project.properties` estaba descrito con:

`sonar.sources = includes, admin, cliente_interfaces, assets/js, index.php, logout.php`

Esa selección deja fuera `backend/` y `frontend/`. `docs/pruebas/INFORME-FINAL.md` conserva el marcador `[COMPLETAR TRAS SONARQUBE]`.

## 4. Objetivo

Actualizar la configuración para analizar el código vigente de `backend/` y `frontend/`, junto con los puntos de entrada, generar la cobertura disponible, ejecutar un análisis nuevo y registrar en `INFORME-FINAL.md` las métricas reales del Quality Gate.

## 5. Alcance autorizado

Modifica o crea únicamente:

- `sonar-project.properties`
- `scripts/prepare-sonar.bat`
- `scripts/scan-sonar.bat`
- `docs/pruebas/INFORME-FINAL.md`
- `PROMTS/20-Actualizar-sonar-y-reejecutar-analisis.md`
- `PROMTS/evidencias/20-sonarqube-reescaneo.txt` y las capturas reales del panel en `PROMTS/evidencias/`

No modifiques otros archivos sin autorización previa.

## 6. Pasos obligatorios

### Paso 0. Guardar este prompt

Guárdalo antes de implementar.

### Paso 1. Inspeccionar

Revisa `sonar-project.properties`, los dos scripts `.bat`, la estructura real del código, PHPUnit, la posibilidad de generar cobertura y el apartado pendiente de `INFORME-FINAL.md`. Comprueba que las herramientas existan antes de ejecutarlas.

### Paso 2. Actualizar sonar-project.properties

Incluye como fuentes `backend/`, `frontend/`, `index.php` y `logout.php`. Excluye `vendor/`, `docs/` y `tests/` para que no se analicen como código de producción, según corresponda. No excluyas `backend/` ni `frontend/`. No uses `sonar.issue.ignore` para ocultar incidencias nuevas. Conserva las reglas de calidad. Configura la cobertura para que los archivos de prueba no cuenten como código productivo y para que SonarQube lea el reporte correcto.

### Paso 3. Codificación

Guarda `sonar-project.properties` en UTF-8. Corrige mojibake en los `.bat` sin romper comandos que ya funcionen. Mantén la compatibilidad con Windows/XAMPP.

### Paso 4. Cobertura

Ejecuta `composer test:coverage`. Si no puede generarse por falta de extensión o de configuración, documenta el motivo. No instales dependencias ni edites archivos fuera del alcance para resolverlo.

### Paso 5. Análisis

Comprueba que el servidor SonarQube esté activo. Ejecuta `scripts/scan-sonar.bat`. Verifica en el servidor que el análisis incluye `backend/` y `frontend/`. Si el servidor no está disponible, registra el bloqueo y no inventes métricas.

### Paso 6. Métricas reales

Registra en `INFORME-FINAL.md` el Quality Gate, Bugs, Vulnerabilities, Code Smells, Coverage y Duplicated Lines del análisis final, con fecha y la identificación del proyecto cuando exista. Si una métrica no está disponible, indícalo y explica el motivo.

### Paso 7. Incidencias bloqueantes

Corrige solo las bloqueantes que caigan en el alcance autorizado. No ocultes incidencias ni cambies reglas para forzar el Quality Gate. Si hace falta tocar otro archivo, detente y pide autorización. Tras corregir, repite cobertura y análisis, y actualiza el informe con el resultado final.

## 7. Restricciones

- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas salvo autorización expresa.
- No uses `sonar.issue.ignore` para ocultar incidencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No uses comentarios para esconder incidencias.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push ni publiques en GitHub.
- Crea la rama y un commit local `tipo(INT-006-BOLT-007): descripción`.
- No marques el bolt como resuelto sin verificación y evidencia.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 8. Criterios de aceptación

1. `sonar-project.properties` incluye `backend/`, `frontend/`, `index.php` y `logout.php`.
2. Las exclusiones dejan fuera `vendor/`, `docs/` y `tests/` como código de producción, según corresponda.
3. El análisis contempla `backend/` y `frontend/`.
4. La configuración y los scripts están en codificación correcta, sin mojibake.
5. La cobertura se genera y SonarQube puede leerla, o el impedimento queda documentado.
6. El análisis se ejecuta con el servidor activo y el resultado se comprueba en el panel.
7. `INFORME-FINAL.md` contiene las métricas reales del análisis final.
8. Las bloqueantes corregibles dentro del alcance se atienden y se reanalizan.
9. No se ocultan incidencias ni se alteran las reglas para favorecer el resultado.

## 9. Entrega

Al finalizar, informa archivos, estado real de `INT-006-BOLT-007`, comandos y resultados, métricas del análisis, rutas de evidencia, incidencias corregidas y pendientes, bloqueos y el modelo de IA. El bolt no se declara completado si solo se actualizó la configuración.
