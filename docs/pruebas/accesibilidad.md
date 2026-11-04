# Validación de accesibilidad

Fecha: 2026-11-03, hora local aproximada 00:05–00:11.  
Responsable de la ejecución: cursor12310-sys.  
Modelo usado para preparar y registrar la prueba: Grok 4.7.

Esta página registra lo que se ejecutó. axe-core no demuestra el cumplimiento de WCAG. Lighthouse no se ejecutó. No se usó un lector de pantalla.

## Vistas evaluadas

| Vista | URL medida | Título |
| --- | --- | --- |
| Catálogo | `http://127.0.0.1/paginaweb/cliente_interfaces/catalogo.php` | Catálogo General — Mass Online |
| Carrito | `cliente_interfaces/carrito.php` redirige a `cliente_interfaces/pedido.php` | Carrito & Validación — Mass Online |
| Pedido, paso 1 | `cliente_interfaces/pedido.php` | Carrito & Validación — Mass Online |
| Pedido, paso 2 | `cliente_interfaces/pedido.php?paso=2` | Entrega o Retiro — Mass Online |
| Administración | `http://127.0.0.1/paginaweb/admin/index.php` | Dashboard — Mass Admin |

El carrito y el paso 1 del pedido son el mismo documento. El paso 2 es otra presentación de `pedido.php`.

## Herramientas

| Herramienta | Versión | Estado |
| --- | --- | --- |
| axe-core | 4.10.3, cargado desde cdnjs en la pestaña de prueba | Ejecutado. No se instaló en el proyecto. |
| Navegador de Cursor | Chromium integrado | Ejecutado para DOM, foco y capturas. |
| Lighthouse | No está en PATH (`node`, `npx` y `lighthouse` no se encontraron) | No ejecutado. No se instaló. |
| Lector de pantalla | Narrador de Windows no se inició | Pendiente. |

Los conteos de axe son nodos afectados por impacto: critical, serious, moderate y minor. En estas corridas no hubo hallazgos minor.

## Resultados iniciales

Catálogo, cajón cerrado, antes de las correcciones de esta sesión:

- critical 0, serious 11, moderate 2, minor 0
- serious: `color-contrast` (11 nodos)
- moderate: `heading-order` (h3 de Filtros) y `region` (`.topbar`)
- campos de formulario sin etiqueta: 0
- identificadores duplicados: 0

Administración, antes de corregir el `tablist` y el contenido principal:

- critical 1, serious 17, moderate 7, minor 0
- critical: `aria-required-children` en `.filters-inline` (`role="tablist"` sobre enlaces que no son pestañas)
- además: contraste, orden de encabezados, dos banners, ausencia de `main` y contenido fuera de landmarks

Pedido paso 1, antes de sacar el `aside` anidado:

- critical 0, serious 14, moderate 3
- moderate extra: `landmark-complementary-is-top-level` en `.cart-upsell` y `region` en `.topbar`

## Problemas detectados y correcciones

Dentro de `frontend/views/**` y `frontend/assets/js/ui.js`:

- Los controles repetidos del catálogo, de cada línea del carrito y del cajón incluyen el nombre del producto en su nombre accesible. Los identificadores de línea son `cart-sel-{id}` y `cart-qty-{id}`. Con dos productos no hubo identificadores duplicados.
- El cajón cerrado lleva `inert` y `aria-hidden="true"`. Abierto, tiene `aria-modal="true"`, nombre por el encabezado "Tu carrito" y el foco entra en "Cerrar carrito".
- Tab y Shift+Tab ciclan dentro del diálogo. Escape lo cierra y devuelve el foco al botón que lo abrió. La primera prueba falló porque el navegador usaba `ui.js` en caché y porque `inert` se aplicaba antes de mover el foco. El pie ahora carga `ui.js?v=20261103b` y el cierre enfoca primero el botón de apertura.
- `#toast` es una sola región. Un mensaje de estado usa `role="status"` y `aria-live="polite"`. Un error usa `role="alert"` y `aria-live="assertive"`. El texto se vacía y se vuelve a escribir para que el cambio sea anunciable. Se observó "Producto agregado al carrito" y el rechazo "El campo id debe ser un número entero positivo."
- La barra superior de la tienda es una región con nombre "Datos de la tienda". El bloque de sugerencias del pedido dejó de ser un `aside` dentro de otra sección; conserva la clase y el aspecto.
- En administración se quitó `role="tablist"`. El filtro activo usa `aria-current="page"`. `.admin-main` pasó a ser `main`, así que el encabezado dejó de crear un segundo banner y el contenido quedó en un landmark. La búsqueda y el botón "Menú de cuenta" ya tenían nombre accesible.

No se cambió el color ni el tamaño de los textos. El contraste y el salto de nivel de encabezado siguen abiertos porque corregirlos altera el diseño o requiere CSS, y el CSS no está en el alcance.

## Resultados posteriores

| Vista | Critical | Serious | Moderate | Minor |
| --- | ---: | ---: | ---: | ---: |
| Catálogo | 0 | 9 | 1 | 0 |
| Carrito / pedido paso 1 | 0 | 14 | 1 | 0 |
| Pedido paso 2 | 0 | 7 | 1 | 0 |
| Administración | 0 | 17 | 1 | 0 |

En las cuatro vistas el único serious restante es `color-contrast`. El único moderate restante es `heading-order` (h3 de Filtros en el catálogo, h3 de indicadores en administración y h4 del pie en el pedido). No quedaron campos sin etiqueta ni identificadores duplicados en las páginas medidas.

## Pruebas manuales realizadas

- Apertura y cierre del diálogo del carrito, ciclo de Tab y Shift+Tab, y regreso del foco. El detalle está en `PROMTS/evidencias/22-accesibilidad/teclado-dialogo-toast.txt`.
- Lectura de nombres accesibles en el árbol de accesibilidad: búsqueda, filtros, cantidades con nombre de producto, paginación, líneas del carrito, entrega y encabezado de administración.
- Lectura del DOM de `#toast` después de agregar un producto y después de un envío rechazado.

La tecla Tab de la herramienta de automatización no movió el foco. La comprobación usa los eventos `keydown` que escucha la página y el elemento que queda activo.

## Pendientes y limitaciones

- Lighthouse no está instalado y no se instaló. No hay puntuación de Lighthouse.
- No se ejecutó Narrador ni otro lector de pantalla. El comportamiento de `aria-live` se comprobó en el DOM, no en la salida hablada.
- El anillo de foco del buscador se ve por `:focus-within`. En un enlace del pedido no se observó un contorno adicional. El CSS está fuera del alcance.
- Contraste y orden de encabezados siguen en axe. No se declaran resueltos.
- Git no está disponible en este equipo. No hay commit local.

## Conclusión

Las cuatro vistas quedaron con cero errores critical de axe 4.10.3 en la ejecución posterior a las correcciones. Los campos medidos tienen etiqueta, las líneas del carrito no repiten identificadores, el diálogo responde al teclado de la página y el toast cambia `role` y `aria-live` según el tipo de mensaje.

Eso no equivale a conformidad WCAG. Siguen hallazgos serious de contraste y moderate de encabezados, no hay informe de Lighthouse y falta la prueba con lector de pantalla.
