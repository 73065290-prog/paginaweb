# PROMPT 22: Validación de accesibilidad de los fixes (labels, ARIA y toast)

**Intent:** INT-002  
**Bolts:** INT-002-BOLT-008, INT-002-BOLT-012, INT-003-BOLT-003, INT-003-BOLT-004 y INT-003-BOLT-005  
**HU:** INT-002-HU-22  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en accesibilidad web (WCAG), HTML semántico, ARIA, JavaScript nativo, PHP 8.1+ y pruebas de accesibilidad.

Trabaja sobre el proyecto existente. Inspecciona primero los cambios de accesibilidad y verifica su comportamiento real con análisis automatizado y pruebas manuales de teclado y lector de pantalla.

El objetivo es validar y corregir problemas de accesibilidad sin alterar el diseño visual ni eliminar funcionalidades.

## 2. Registro obligatorio del prompt y evidencias

Antes de implementar, guarda este prompt en:

`PROMTS/22-Validacion-accesibilidad-labels-aria-toast.md`

Guarda reportes, resultados y capturas en:

`PROMTS/evidencias/22-accesibilidad/`

Reglas:

- Si `PROMTS/` o la carpeta de evidencias no existe, créala.
- Conserva la estructura de los prompts anteriores y guarda el archivo en UTF-8.
- No incluyas información sensible.
- No inventes capturas ni presentes estimaciones como resultados reales.
- No declares una prueba automatizada como ejecutada si no se pudo correr.
- La creación de estos archivos no autoriza cambios fuera del alcance.

## 3. Contexto

Los cambios de accesibilidad todavía no tienen pruebas ni un reporte de verificación. Hay que validar:

- Etiquetas `label` de los campos del catálogo.
- Encabezado del panel de administración.
- Identificación accesible de los campos de cada línea del carrito (`cart-line`).
- Atributos ARIA del carrito.
- Anuncios del toast mediante `aria-live`.

## 4. Objetivo

Verificar catálogo, carrito, pedido y administración con Lighthouse, axe y pruebas manuales. Corregir lo encontrado dentro del alcance, repetir las verificaciones y dejar el reporte con resultados y limitaciones reales.

## 5. Alcance

Modifica o crea únicamente:

- `frontend/views/**`
- `frontend/assets/js/ui.js`
- `docs/pruebas/accesibilidad.md`

Y este prompt, más la evidencia en `PROMTS/evidencias/22-accesibilidad/`.

Si hace falta otro archivo, detente, explica el cambio y espera autorización.

## 6. Pasos

1. Inspecciona vistas y `ui.js`. Identifica campos del catálogo, encabezado admin, líneas repetidas del carrito, diálogo del carrito y toasts. No edites antes de localizar problemas concretos: ids duplicados, labels sin asociación o ARIA incorrecto.
2. Evalúa catálogo, carrito, pedido y administración con Lighthouse y axe si ya están disponibles. No instales dependencias nuevas. Si una herramienta falta, registra el impedimento y usa las alternativas que existan.
3. Comprueba que cada campo tenga nombre accesible asociado (`for`/`id` o envoltura semántica), que las instrucciones usen `aria-describedby` cuando corresponda, que no haya ids duplicados y que cada línea del carrito genere ids únicos. No uses el placeholder como única etiqueta.
4. Comprueba el diálogo del carrito: nombre, rol, estado, apertura y cierre con teclado, foco al abrir, foco contenido mientras está abierto, retorno del foco al cerrar y controles internos operables.
5. Comprueba que el toast se anuncie con `aria-live` acorde al tipo de mensaje, sin duplicar anuncios ni depender solo del color, y que el anuncio ocurra cuando el contenido cambia, no solo al cargar.
6. Prueba Tab, Shift+Tab, Enter y Espacio, visibilidad del foco, orden de tabulación, diálogo y lectura de etiquetas. Si no hay lector de pantalla, márcalo pendiente. No afirmes una prueba que no se ejecutó.
7. Corrige los problemas dentro del alcance. Prioriza lo que impida el uso con teclado o lector. Conserva diseño y funciones. Prefiere HTML semántico antes que ARIA extra. Repite las pruebas que correspondan.
8. Escribe `docs/pruebas/accesibilidad.md` con fecha, vistas, herramientas y versiones, resultados iniciales y finales, correcciones, severidades reales, pruebas manuales, pendientes y conclusión. No declares cumplimiento total de WCAG solo por Lighthouse o axe.

## 7. Restricciones

- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No cambies el diseño visual ni elimines funcionalidades.
- No desactives pruebas ni reglas de SonarQube.
- No uses comentarios para esconder incidencias.
- No hagas push.
- Un commit local por bolt, formato `tipo(INT-XXX-BOLT-NNN): descripción`, solo si git está disponible. No instales git.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.
- No declares los bolts completados solo porque exista el reporte.

## 8. Criterios de aceptación

1. Se evaluaron catálogo, carrito, pedido y administración.
2. Cada campo de formulario tiene etiqueta accesible asociada.
3. No hay identificadores duplicados en las vistas evaluadas, incluidas las líneas del carrito.
4. El diálogo del carrito se usa con teclado.
5. La gestión del foco del diálogo funciona.
6. Los anuncios `aria-live` y el toast se comunican a tecnologías de asistencia.
7. Los problemas detectados dentro del alcance se corrigieron.
8. La nueva ejecución de axe presenta cero errores críticos.
9. Los resultados de Lighthouse y axe están documentados.
10. Existe `docs/pruebas/accesibilidad.md` con resultados y limitaciones reales.

## 9. Pruebas obligatorias

Lighthouse y axe sobre catálogo, carrito, pedido y administración, más teclado y lector de pantalla.

La evidencia debe incluir reportes, capturas reales, teclado, diálogo, toast, resultados iniciales y finales, y fecha y hora. Si una prueba no puede ejecutarse, queda pendiente con el motivo.
