# Mass Online / SGC-Mass — paginaweb

Demo PHP de **Tiendas Mass**: tienda online (catálogo, carrito, checkout) y panel admin WMS (inventario, pedidos, productos). Datos en sesión, sin MySQL.

Repositorio: [github.com/73065290-prog/paginaweb](https://github.com/73065290-prog/paginaweb)

## Requisitos

- PHP 8.1+ (recomendado con XAMPP / MAMP)
- Servidor web con `mod_rewrite` opcional (`.htaccess` solo fija `DirectoryIndex`)

## Cómo ejecutar

1. Coloca el proyecto en la carpeta del servidor, por ejemplo:
   - XAMPP: `htdocs/paginaweb`
2. Abre en el navegador:
   - Tienda: `http://localhost/paginaweb/` → redirige a `cliente_interfaces/`
   - Admin: `http://localhost/paginaweb/admin/login.php`

## Credenciales demo

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| `admin` / `admin.operaciones@mass.pe` | `123456` | Administrador WMS |
| `cliente` / `proveedor@mass.pe` | `123456` | Cliente / proveedor |

## Estructura

```
paginaweb/
├── admin/                 # Panel SGC-Mass (login, dashboard, productos, pedidos)
├── cliente_interfaces/    # Tienda online (catálogo, PDP, carrito, checkout)
├── includes/              # init, data demo, functions, layouts, components
├── assets/                # CSS / JS / img
├── tests/                 # Pruebas de reglas de negocio (CLI)
├── index.php              # Entrada → cliente
└── MEJORAS.md             # Registro de ramas y correcciones
```

## Pruebas

Desde la raíz del proyecto:

```bash
php tests/auth_test.php
php tests/negocio_test.php
php tests/productos_admin_test.php
```

En las ramas de mejora también existen:

```bash
php tests/carrito_test.php   # rama fix/carrito-cantidad-maxima
php tests/csrf_test.php      # rama mejora/seguridad-csrf
```

## Cupones demo

| Código | Descuento |
|--------|-----------|
| `CLUBMASS10` | S/ 5.00 |
| `CLUBMASS60` | S/ 6.00 |

## Ramas de mejora

Ver detalle en [`MEJORAS.md`](MEJORAS.md). Resumen:

| Rama | Tipo | Qué corrige / mejora |
|------|------|----------------------|
| `fix/cupones-checkout` | Bug | Aplica `CLUBMASS60` (antes solo `CLUBMASS10` en el POST) |
| `fix/carrito-cantidad-maxima` | Bug | Tope 99 unidades en agregar/cambiar |
| `mejora/seguridad-csrf` | Seguridad | CSRF + cookies HttpOnly/SameSite |
| `docs/readme-proyecto` | Docs | README y registro de mejoras |

## Flujo demo rápido

1. Catálogo → agregar productos
2. `pedido.php?demo=1` carga canasta de ejemplo
3. Checkout en 3 pasos (carrito → entrega/retiro → pago)
4. Confirmación con tracking según modalidad (retiro vs express)
