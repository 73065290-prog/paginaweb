# PROMPT 30: Plantilla estándar de prompts, registro de modelo IA y tickets

**Intent:** INT-006  
**Bolts:** Transversal (sin identificador de bolt)  
**HU:** INT-006-HU-30  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior y especialista en documentación técnica, trazabilidad, control de versiones y gestión de proyectos.

Establece una plantilla uniforme y mejora la trazabilidad entre prompts, tickets, bolts y commits. No alteres el contenido funcional de los prompts existentes.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/30-plantilla-estandar-prompts-modelo-ia-tickets.md`

Guarda la revisión en:

`PROMTS/evidencias/30-revision-plantilla-trazabilidad.txt`

Reglas:

- UTF-8 sin BOM.
- No inventes IDs, enlaces, métricas ni tickets.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.
- No presentes una comprobación pendiente como exitosa.

## 3. Contexto

Los prompts no tenían un registro uniforme de modelo, fecha, HU y resultado, ni una convención única entre prompt, ticket, bolt, rama y commit. El prompt 01 tiene, en el árbol de `main`, una copia en `docs/pruebas/prompt-correccion-sonar-a11y.md`.

## 4. Objetivo

Dejar una plantilla estándar y convenciones de identificación entre Intent, HU, prompt, ticket, bolt, rama y commit, de modo que se distinga qué se pidió, qué se ejecutó y cuál fue el resultado, sin inventar datos ni reescribir la historia.

## 5. Alcance

Modifica o crea únicamente:

- `PROMTS/PLANTILLA.md`
- `PROMTS/README.md`
- `PROMTS/01` a `PROMTS/03`, solo el encabezado
- `docs/pruebas/prompt-correccion-sonar-a11y.md`, eliminarlo solo si es un duplicado sin información única

Y este prompt, más `PROMTS/evidencias/30-revision-plantilla-trazabilidad.txt`.

## 6. Pasos

1. Revisa `PROMTS/` y la numeración existente. Compara el duplicado con el prompt 01 antes de borrarlo.
2. Crea la plantilla con número y título, Intent, HU, bolts, modelo, fecha, responsable, rol, contexto, objetivo, alcance, pasos, restricciones, criterios, pruebas y entrega.
3. Agrega el encabezado a los prompts 01, 02 y 03. Conserva el resto del texto.
4. Documenta en `PROMTS/README.md` el uso de la plantilla y la convención de trazabilidad. No dupliques identificadores.
5. Elimina el duplicado solo si la comparación confirma que no aporta texto único.
6. Define la relación Intent, HU, prompt, ticket, bolt, rama y commit. El commit sigue siendo `tipo(INT-XXX-BOLT-NNN): descripción`. No inventes IDs.
7. Revisa si hay Trello o Jira. Crea tickets solo con acceso autorizado. Si no hay conexión, deja los tickets pendientes y di qué dato falta.
8. Verifica plantilla, encabezados, contenido original, README, enlaces y la comparación del duplicado.
9. Guarda este prompt en UTF-8 sin BOM.

## 7. Restricciones

- No modifiques el cuerpo de los prompts 01 a 03.
- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push.
- Crea un commit local solo cuando exista un bolt asignado, con el formato `tipo(INT-XXX-BOLT-NNN): descripción`.
- No marques tareas como resueltas sin verificarlas.

## 8. Criterios de aceptación

1. Existe `PROMTS/PLANTILLA.md` con todos los apartados pedidos.
2. Los prompts 01, 02 y 03 tienen el encabezado estándar.
3. Su contenido original permanece, salvo el encabezado.
4. El duplicado se elimina solo si no contiene información única.
5. `PROMTS/README.md` explica la plantilla y la trazabilidad.
6. La convención de ramas y commits está definida.
7. Los tickets creados tienen ID verificable; los demás quedan pendientes.
8. Este prompt está guardado en `PROMTS/`.
9. No se modificaron archivos fuera del alcance.

## 9. Entrega

Resume archivos, bolts si tienen identificador, pruebas y resultados, la ruta de la evidencia, tickets creados y pendientes, las convenciones, los pendientes y el modelo de IA.
