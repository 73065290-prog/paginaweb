# PROMPT 26: Completar INFORME-FINAL y actualizar la documentación

**Intent:** INT-006  
**Bolts:** INT-006-BOLT-010; INT-001-BOLT-004  
**HU:** INT-006-HU-26  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en documentación técnica, aseguramiento de calidad, métricas, SonarQube y cobertura.

Completa la documentación solo con datos comprobables. No inventes datos personales, resultados, métricas ni capturas.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/26-completar-informe-final-documentacion.md`

Guarda la evidencia en:

`PROMTS/evidencias/26-completar-informe-final-documentacion/`

Reglas:

- UTF-8 y la misma estructura que los prompts anteriores.
- No presentes el informe como cerrado si faltan datos o evidencias.
- No marques un bolt como terminado si la verificación no lo sostiene.
- No crees un commit que presente como terminado un bolt incompleto.

## 3. Contexto

`INFORME-FINAL.md` tenía pendientes el nombre, el código, el docente, la fecha y el bloque de SonarQube. Parte de la documentación decía que la aplicación no usa MySQL, aunque el código persiste con MySQL/MariaDB mediante PDO y distingue el modo demo.

## 4. Objetivo

Dejar el informe y la documentación de pruebas coherentes con la arquitectura, las funciones, las métricas y las evidencias reales.

## 5. Alcance

Modifica únicamente:

- `docs/pruebas/*.md`
- `README.md`

Y este prompt, más la evidencia en `PROMTS/evidencias/26-completar-informe-final-documentacion/`.

No modifiques imágenes, reportes originales, PHP ni configuración. Si hace falta otro archivo, detente y espera autorización.

## 6. Pasos

1. Revisa `INFORME-FINAL.md`, `docs/pruebas/*.md`, `README.md`, los placeholders y las evidencias reales de SonarQube y cobertura.
2. Sustituye nombre, código, docente y fecha solo si están confirmados. Si no, déjalos identificados como pendientes. No inventes datos personales.
3. Corrige las frases que niegan MySQL. Documenta PDO, el modo demo y el modo error según `Database.php`. No digas que se guarda en MySQL cuando el modo es demo.
4. Copia solo métricas que estén en un reporte real, con su fecha. Si no hubo análisis, dilo. No uses cifras viejas como si fueran el estado actual.
5. Incorpora las capturas N.º 1 a N.º 6 si existen y corresponden. Si no, deja el faltante. No fabriques imágenes.
6. Revisa cada enlace relativo desde el documento que lo contiene. Conserva la referencia aunque el destino falte.
7. Comprueba que README e informe no se contradigan. No declares el informe finalizado si falta algo esencial.
8. Guarda el prompt y la evidencia de la revisión.

## 7. Restricciones

- No inventes métricas, porcentajes, resultados de SonarQube, capturas ni datos personales.
- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push.
- Un commit local por bolt completado, con el mensaje `tipo(INT-XXX-BOLT-NNN): descripción`. Si el bolt no se completa, no lo des por terminado en el commit.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 8. Criterios de aceptación

1. El informe y los documentos del alcance reflejan el estado real.
2. No quedan placeholders mudos, salvo datos pendientes de confirmación bien identificados.
3. La persistencia describe MySQL/MariaDB con PDO y el fallback demo.
4. Las métricas coinciden con los reportes reales.
5. Las seis capturas están incorporadas o su falta está documentada.
6. Los enlaces fueron revisados y el resultado quedó registrado.
7. README y los documentos de pruebas no se contradicen.
8. El prompt y la evidencia están en `PROMTS/`.

## 9. Entrega

Resume archivos, el estado real de INT-006-BOLT-010 e INT-001-BOLT-004, los datos personales resueltos o pendientes, las métricas y sus fuentes, las seis capturas, la revisión de enlaces, la ruta de evidencias, los commits si existen, los pendientes y el modelo de IA.
