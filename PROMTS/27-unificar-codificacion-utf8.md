# PROMPT 27: Unificar la codificación UTF-8

**Intent:** INT-001  
**Bolts:** INT-001-BOLT-005; G21  
**HU:** INT-001-HU-27  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en PHP, codificación de caracteres, UTF-8, compatibilidad entre sistemas y control de calidad.

Antes de editar, revisa el estado real de los archivos. Corrige la codificación sin cambiar el significado, la funcionalidad ni la estructura.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/27-unificar-codificacion-utf8.md`

Guarda la evidencia en:

`PROMTS/evidencias/27-unificar-codificacion-utf8/`

Reglas:

- UTF-8 sin BOM, con la misma estructura que los prompts anteriores.
- No guardes esta evidencia en `docs/pruebas/evidencias/`.
- No reescribas el historial de Git.
- No presentes una prueba como exitosa si no se ejecutó.

## 3. Contexto

Hay mojibake en `sonar-project.properties` y `tests/bootstrap.php`, un carácter corrupto en el título del PROMPT 03 y BOM en mensajes de commit. Los textos del alcance deben quedar en UTF-8 sin BOM.

## 4. Objetivo

Corregir la codificación identificada, dejar UTF-8 sin BOM como convención de los archivos de texto del alcance y comprobar que los acentos se leen bien.

## 5. Alcance

Modifica únicamente:

- `sonar-project.properties`
- `tests/bootstrap.php`
- `PROMTS/*.md`
- `docs/**`
- `.editorconfig` (nuevo)

Y este prompt, más la evidencia en `PROMTS/evidencias/27-unificar-codificacion-utf8/`.

No conviertas binarios ni imágenes que no sean texto. Si hace falta otro archivo, detente y espera autorización.

## 6. Pasos

1. Detecta BOM, mojibake y UTF-8 inválido en el alcance. Revisa en especial `sonar-project.properties`, `tests/bootstrap.php` y el título del PROMPT 03. No corrijas secuencias que ya sean válidas.
2. Recupera el carácter original cuando se pueda determinar. No sustituyas tildes por letras sin acento. Si el original no es seguro, no lo cambies y pídelo.
3. Guarda lo modificado en UTF-8 sin BOM. Conserva saltos de línea y formato.
4. Crea `.editorconfig` con `root = true` y `charset = utf-8`, sin reglas de formato que reescriban el repositorio.
5. Vuelve a comprobar UTF-8 sin BOM, mojibake y los acentos de los archivos corregidos.
6. Revisa los mensajes de commit con BOM. No hagas rebase, amend ni reescritura. Los mensajes nuevos van en UTF-8 sin BOM.
7. Guarda el prompt y la evidencia.

## 7. Restricciones

- No cambies el contenido semántico.
- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push.
- No reescribas commits existentes.
- Un commit local del bolt completado, con el mensaje `tipo(INT-001-BOLT-005): descripción`, en UTF-8 sin BOM. Registra G21 solo si corresponde y está identificado.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 8. Criterios de aceptación

1. Los archivos afectados quedan en UTF-8 sin BOM.
2. No quedan secuencias de mojibake en lo revisado.
3. El título del PROMPT 03 muestra bien sus caracteres especiales.
4. `sonar-project.properties` y `tests/bootstrap.php` conservan su significado y sus acentos.
5. `.editorconfig` existe y fija `charset = utf-8`.
6. Los Markdown y propiedades revisados muestran bien los acentos.
7. El historial de Git no se reescribió.
8. El prompt y las evidencias están en `PROMTS/`.

## 9. Entrega

Resume archivos, el estado real de INT-001-BOLT-005 y G21, los problemas de codificación, las correcciones, `.editorconfig`, cada prueba con su resultado, la ruta de evidencias, el commit si existe, los pendientes y el modelo de IA.
