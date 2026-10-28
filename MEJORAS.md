# Registro de mejoras y correcciones

Documento de calidad de software (S11) con las ramas subidas a  
[https://github.com/73065290-prog/paginaweb](https://github.com/73065290-prog/paginaweb).

Cada rama parte de `main` y se puede revisar / fusionar de forma independiente.

---

## 1. `fix/cupones-checkout`

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

## 2. `fix/carrito-cantidad-maxima`

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

## 3. `mejora/seguridad-csrf`

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

## 4. `docs/readme-proyecto`

### Mejora
Documentación de arranque, credenciales, estructura, pruebas y este registro.

### Archivos
- `README.md`
- `MEJORAS.md`

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
