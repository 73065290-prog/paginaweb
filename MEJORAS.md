# Registro de mejoras y correcciones

Documento de calidad de software (S11) con las ramas subidas a  
[https://github.com/73065290-prog/paginaweb](https://github.com/73065290-prog/paginaweb) y su trazabilidad hacia el Intent **`INT-002`** (`HU: INT-002-HU-05`, documentado en [`PROMTS/05.Registro-mejoras-seguridad-accesibilidad.md`](./PROMTS/05.Registro-mejoras-seguridad-accesibilidad.md)).

Cada rama parte de `main` y se puede revisar / fusionar de forma independiente.

---

## 1. `fix/cupones-checkout` (`INT-002-BOLT-001` · Commit `b59774f` · Merge `e9fc1cb` · **Fusionada en `main`**)

### Problema
`pedido_descuento_cupon()` reconocía `CLUBMASS10` y `CLUBMASS60`, pero el endpoint
`cliente_interfaces/carrito.php` (acción `cupon`) **solo aceptaba** `CLUBMASS10`.
La UI del resumen tampoco mostraba cupones distintos de `CLUBMASS10`.

### Corrección
- Fuente única: `pedido_cupones_validos()` + `pedido_es_cupon_valido()`.
- El checkout aplica cualquier cupón del mapa.
- El badge de cupón aplicado usa el código real y el descuento calculado.
- Pruebas ampliadas en `tests/negocio_test.php`.

### Archivos
- `includes/functions.php`
- `cliente_interfaces/carrito.php`
- `cliente_interfaces/pedido.php`
- `tests/negocio_test.php`

---

## 2. `fix/carrito-cantidad-maxima` (`INT-002-BOLT-002` · Commit `22a619d` · Merge `beaa9f1` · **Fusionada en `main`**)

### Problema
`carrito_set_cantidad()` limitaba a 99 unidades, pero `carrito_agregar()` y
`carrito_cambiar()` podían superar ese tope (inconsistencia con la UI `max="99"`).

### Corrección
- Constante lógica `carrito_cantidad_maxima()` (= 99).
- Agregar / cambiar / set respetan el mismo tope.
- Nueva suite `tests/carrito_test.php`.

### Archivos
- `includes/functions.php`
- `tests/carrito_test.php`

---

## 3. `mejora/seguridad-csrf` (`INT-002-BOLT-003` · Commit `6c1195a` · Merge `7f2b531` · **Fusionada en `main`**)

### Problema
Los formularios POST del carrito/checkout y el login admin no validaban origen
del request (riesgo CSRF). Las cookies de sesión no declaraban `HttpOnly` /
`SameSite`. El parámetro `volver` admitía rutas con `..`.

### Mejora
- Cookie de sesión: `httponly` + `samesite=Lax`.
- Helpers `csrf_token()`, `csrf_field()`, `csrf_validar()`.
- Validación CSRF en `carrito.php` y `admin/login.php`.
- Token oculto en formularios de tienda (cards, drawer, PDP, checkout).
- Bloqueo de open redirect con `..` en `volver`.
- Suite `tests/csrf_test.php`.

### Archivos
- `includes/init.php`, `includes/functions.php`
- `cliente_interfaces/carrito.php`, `pedido.php`, `producto.php`
- `includes/components/{product-card,cart-line,cart-drawer}.php`
- `admin/login.php`
- `tests/csrf_test.php`

---

## 4. `docs/readme-proyecto` (`INT-002-BOLT-013` y `INT-002-BOLT-014` · Commit `5a804ee` y `f54b4db` · Merge `e9a5813` · **Fusionada en `main`**)

### Mejora
Documentación de arranque, credenciales, estructura, pruebas y este registro.

### Archivos
- `README.md` (ampliado en `f54b4db` junto con `docs/img/{banner-mass-online,preview-admin,preview-tienda}.svg` → `INT-002-BOLT-013`)
- `MEJORAS.md` (actualizado luego en `docs/registro-nuevas-ramas` commit `f0bffcd` → `INT-002-BOLT-014`)

---

## Cómo revisar una rama

```bash
git fetch origin
git checkout fix/cupones-checkout
# o: fix/carrito-cantidad-maxima | mejora/seguridad-csrf | docs/readme-proyecto
php tests/negocio_test.php   # según la rama
```

## Cómo fusionar a `main` (cuando se apruebe)

```bash
git checkout main
git merge fix/cupones-checkout
git merge fix/carrito-cantidad-maxima
git merge mejora/seguridad-csrf
git merge docs/readme-proyecto
git push origin main
```

> Nota: al fusionar las tres ramas de código habrá solapamiento en
> `includes/functions.php`; resolver priorizando ambas correcciones
> (cupones + tope 99) más los helpers CSRF.

---

## Rondas adicionales (ramas individuales) y trazabilidad con `INT-002`

| Rama | Bolt asignado (`INT-002`) | Commit(s) | Merge en `main` | Tipo | Resumen | Prueba asociada | Estado en Git (`HEAD`) |
|------|---------------------------|-----------|-----------------|------|---------|-----------------|------------------------|
| `fix/cupones-checkout` | `INT-002-BOLT-001` | `b59774f` | `e9fc1cb` | Bug/Negocio | Cupones Club Mass consistentes en carrito y checkout | `tests/negocio_test.php` (`38 OK`); `PedidoReglasTest` (`P05`) | **Fusionada** |
| `fix/carrito-cantidad-maxima` | `INT-002-BOLT-002` | `22a619d` | `beaa9f1` | Bug/Carrito | Tope de 99 unidades en todas las operaciones del carrito | `tests/carrito_test.php` (`12 OK`) | **Fusionada** |
| `mejora/seguridad-csrf` | `INT-002-BOLT-003` | `6c1195a` | `7f2b531` | Seguridad | CSRF en carrito/login y cookies `HttpOnly`/`SameSite=Lax` | `tests/csrf_test.php` (`9 OK`) | **Fusionada** |
| `mejora/csrf-admin-productos` | `INT-002-BOLT-003` | `1e96aa7` | `ce2f41b` | Seguridad | CSRF en alta/edición WMS productos (`admin/productos.php`) | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `mejora/session-regenerate-login` | `INT-002-BOLT-004` | `bcb814b` | `9e9307a` | Seguridad | `session_regenerate_id(true)` al autenticar | `tests/auth_test.php` (`16 OK`) | **Fusionada** |
| `fix/sanitizar-redirect-volver` | `INT-002-BOLT-005` | `bc3cf1d`, `aac822e` | `6c76d88` | Bug/Seguridad | `mass_safe_return_url()` anti open-redirect y queries válidas | `tests/redirect_test.php` (`6 OK`) | **Fusionada** |
| `mejora/headers-seguridad` | `INT-002-BOLT-006` | `4f3e2d3` | `58830e9` | Seguridad | Cabeceras HTTP en `.htaccess` | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `mejora/accesibilidad-carrito-aria` | `INT-002-BOLT-007` | `9bf838a` | `cf285b5` | UX/a11y | Dialog/ARIA del carrito (`role="dialog"`, `aria-live`) | Sin prueba: ver prompt 22 o 23 | **Fusionada** (divergencia en `frontend/` pendiente en prompt 15) |
| `mejora/runner-tests-cli` | `INT-002-BOLT-008` | `4e700ba` | `0b6d625` | Calidad | `tests/run_all.php` | `tests/run_all.php` (`103 OK` en suites CLI) | **Fusionada** |
| `mejora/catalogo-empty-state` | `INT-002-BOLT-009` | `1785dcb` | `6a3a7fc` | UX | Empty state con CTA limpiar filtros | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `mejora/meta-theme-color` | `INT-002-BOLT-010` | `8a111dc` | `6f52acb` | UX | `theme-color` marca Mass (`#FFD100`) | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `mejora/toast-aria-live` | `INT-002-BOLT-011` | `395fa71` | `e59763d` | a11y | Toast flash con `role="status"` y `aria-live="polite"` | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `fix/admin-filtro-pedidos` | `INT-002-BOLT-012` | `b9413c4` | `c0c2739` | Bug | Validación filtros pedidos admin (modo y código) | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `docs/readme-proyecto` (`README.md` + `f54b4db`) | `INT-002-BOLT-013` | `5a804ee`, `f54b4db` | `e9a5813` | Documentación | `README.md` de arranque y presentación Mass Online / SGC-Mass | Sin prueba: ver prompt 22 o 23 | **Fusionada** |
| `docs/readme-proyecto` (`MEJORAS.md`) + `docs/registro-nuevas-ramas` | `INT-002-BOLT-014` | `5a804ee`, `f0bffcd` | `e9a5813`, `8f3e5e2` | Documentación | Creación y actualización de `MEJORAS.md` | Sin prueba: ver prompt 22 o 23 | **Fusionada** |

---

## Ramas remotas pendientes (aún NO fusionadas en `main` local `HEAD`)

Al verificar el repositorio con `git branch -r --no-merged HEAD`, se identifican las siguientes ramas remotas que **continúan sin fusionar**:

| # | Rama remota | Último commit | Fecha | Autor | Descripción del commit y estado |
|---|-------------|---------------|-------|-------|---------------------------------|
| **1** | `origin/mejora/carrito-limite-stock` | `8a69a47` | `2026-10-27 20:08:24 -0500` | `Kervin-Mansilla` | *"mejora: limitar cantidad del carrito al stock disponible"* (7 archivos). **Decisión Prompt 25: rehacer.** No fusionar esta rama. |
| **2** | `origin/feature/mejoras-admin-qa-cliente` | `128a41d` | `2026-10-27 20:28:52 -0500` | `cursor12310-sys` | Mismo árbol que `41a4bde`, ya ancestro de `main`. **Decisión Prompt 25: cerrar.** Sin Pull Request. |
| **3** | `origin/fix/sonar-a11y-parse-labels` | `128a41d` | `2026-10-27 20:28:52 -0500` | `cursor12310-sys` | Apunta al mismo commit `128a41d`. **Decisión Prompt 25: cerrar.** Sin Pull Request. |

*(Nota adicional: en el remoto existe también `origin/mejora/roles-cliente-proveedor` / `origin/main` en el commit `1bd67ba` —"mejora: separar roles cliente y proveedor con portales propios"— que se encuentra 1 commit por delante de `main` local `3a58798` y pendiente de sincronización/revisión).*

---

## Prompt 25 — ramas pendientes y flujo de Pull Request (`INT-006-BOLT-009`)

Fecha del análisis: 2026-11-03. `HEAD` local sigue en `main` (`3a587981005d544cb18118650de64d0f5bca466e`). `origin/main` es `1bd67ba`, cuyo padre es ese `main` local. No se reescribió el historial de `main`, no se borraron ramas y no se publicó nada en GitHub.

El detalle y los diff están en `PROMTS/evidencias/25-resolver-ramas-pull-request/`.

### Decisiones

| Rama | Punta | Merge-base con `main` | Decisión | Motivo |
|------|-------|------------------------|----------|--------|
| `mejora/carrito-limite-stock` | `8a69a47` | `19ddfec866f4` | **Rehacer** | Un commit propio, 7 archivos. Limita la línea a `min(99, stock)`. El PROMPT 09 dejó el tope comercial en 99 dentro de `CarritoService` y no fusionó esta rama. El parche toca `includes/functions.php` y componentes ya sustituidos por el servicio. Fusionarla duplicaría lógica y chocaría con `eb68b44`. |
| `feature/mejoras-admin-qa-cliente` | `128a41d` | `cd06ef0b7533` | **Cerrar** | Dos commits no son ancestros de `main`, pero el árbol de `128a41d` es idéntico al de `41a4bde`, y `41a4bde` sí es ancestro de `main`. No aporta contenido nuevo. |
| `fix/sonar-a11y-parse-labels` | `128a41d` | `cd06ef0b7533` | **Cerrar** | Es el mismo commit que la rama anterior. El fast-forward local del 2026-10-27 las dejó en el mismo puntero. |

Ninguna de las tres queda fusionada por este prompt. No hay Pull Request creado ni enlace. Crearlos exigiría publicar en GitHub, y las tres decisiones son rehacer o cerrar, así que no corresponde abrir un PR de integración.

La rama remota no se elimina: hace falta autorización expresa para borrar referencias.

### Commits `55ce88d` y `eb68b44`

No son el mismo commit ni el mismo árbol.

- `55ce88d` (*Integrar frontend/backend, MySQL, correccion de logout y prompts*) es el segundo padre de `eb68b44` y ya es ancestro de `main`.
- `eb68b44` es el merge local, sin Pull Request, de `a94d2c6` (primer padre, `main` de entonces) y `55ce88d`. Su mensaje dice que resolvió conflictos con CSRF, carrito y cupones.
- Los árboles difieren en 35 rutas. El merge conservó lo que ya estaba en `main` (`MEJORAS.md`, imágenes, pruebas de carrito, CSRF y redirección, y la resolución de `includes/functions.php`).

Se conserva `eb68b44`. No se vuelve a integrar `55ce88d`: Git ya lo tiene como padre del merge. Volver a aplicarlo repetiría el refactor y podría deshacer esa resolución. No se reescribe `main`.

### Flujo que queda pendiente de activar en GitHub

1. Integrar a `main` solo mediante Pull Request, una rama por PR.
2. Exigir revisión antes de fusionar.
3. Exigir el workflow de CI en verde cuando ese workflow esté en el repositorio remoto. Hoy `.github/workflows/ci.yml` existe en el árbol de trabajo y no está en el commit `3a58798` ni publicado.
4. Impedir el push directo a `main`.

La protección de `main` no quedó activa. La consulta a la API de protección de la rama respondió **401 Unauthorized**. Este prompt no autoriza publicar ni cambiar la configuración de GitHub. No se afirma que la regla exista.

### Estado del bolt

`INT-006-BOLT-009` queda **pendiente**. El análisis y este registro están hechos. Siguen pendientes la protección de `main`, la publicación de los Pull Requests cuando una rama futura sí deba fusionarse, y el cierre remoto de las dos ramas de Sonar si se autoriza borrarlas.
