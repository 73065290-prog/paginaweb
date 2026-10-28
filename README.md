# Mass Online · SGC-Mass

<p align="center">
  <img src="https://raw.githubusercontent.com/73065290-prog/paginaweb/main/docs/img/banner-mass-online.png" alt="Mass Online — Tienda online y panel WMS SGC-Mass" width="100%" />
</p>

<p align="center">
  <strong>Demo PHP</strong> de e-commerce y operaciones para <em>Tiendas Mass</em>:<br/>
  catálogo, carrito, checkout (retiro / delivery) y panel admin WMS (inventario, pedidos, productos).
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white" />
  <img alt="Sesión" src="https://img.shields.io/badge/Persistencia-Sesión%20PHP-00472F" />
  <img alt="Tests" src="https://img.shields.io/badge/Tests-CLI%20PHP-2F9E60" />
  <img alt="Licencia demo" src="https://img.shields.io/badge/Uso-Académico%20%2F%20Demo-FF8A3D" />
</p>

---

## ¿Qué es este proyecto?

**Mass Online** es el portal de compra del cliente (storefront).  
**SGC-Mass** es el panel de control de tienda (WMS / góndola / picking).

Todo corre en **PHP puro** con datos demo en sesión (sin MySQL). Ideal para prácticas de:

- reglas de negocio (retiro vs express, cupones, validación de pedidos)
- calidad de software (suites CLI en `/tests`)
- UX de checkout y panel operativo

| Módulo | Ruta | Para quién |
|--------|------|------------|
| Tienda online | `cliente_interfaces/` | Cliente / proveedor de pedidos |
| Panel WMS | `admin/` | Administrador de operaciones |
| Núcleo | `includes/` | Datos, auth, carrito, checkout |
| Pruebas | `tests/` | Validación de reglas |

---

## Vistas principales

<p align="center">
  <img src="https://raw.githubusercontent.com/73065290-prog/paginaweb/main/docs/img/preview-tienda.png" alt="Vista previa Tienda Mass Online" width="48%" />
  &nbsp;
  <img src="https://raw.githubusercontent.com/73065290-prog/paginaweb/main/docs/img/preview-admin.png" alt="Vista previa Admin SGC-Mass" width="48%" />
</p>

<p align="center"><em>Izquierda:</em> storefront (hero, catálogo, carrito) · <em>Derecha:</em> dashboard WMS (KPIs, alertas de góndola)</p>

### Flujo cliente
1. Catálogo y ficha de producto (PDP)
2. Carrito con cupones Club Mass
3. Checkout en 3 pasos: canasta → entrega/retiro → pago
4. Confirmación con tracking (retiro en tienda **o** delivery express)

### Flujo admin
1. Login SGC-Mass (admin WMS / cliente)
2. Dashboard de venta, pedidos y stock crítico
3. Catálogo admin (alta/edición con reglas EAN, margen, ABC)
4. Inventario y cola de picking / pedidos

---

## Requisitos

- **PHP 8.1+** (XAMPP / MAMP / servidor local)
- Extensiones habituales de PHP (`session`, `mbstring`)
- Navegador moderno

## Cómo ejecutar

1. Copia el proyecto a tu servidor local, por ejemplo:
   - XAMPP → `htdocs/sgc-mass-online` (o el nombre de carpeta que uses)
2. Abre:
   - **Tienda:** `http://localhost/<carpeta>/` → redirige a `cliente_interfaces/`
   - **Admin:** `http://localhost/<carpeta>/admin/login.php`
3. Atajo demo de checkout:  
   `cliente_interfaces/pedido.php?demo=1` (carga una canasta de ejemplo)

---

## Credenciales demo

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| `admin` · `admin.operaciones@mass.pe` | `123456` | Administrador WMS |
| `cliente` · `proveedor@mass.pe` | `123456` | Cliente / proveedor pedidos |

> Solo para demo académica. No uses estas claves en producción.

## Cupones demo

| Código | Descuento |
|--------|-----------|
| `CLUBMASS10` | S/ 5.00 |
| `CLUBMASS60` | S/ 6.00 |

## Reglas de negocio destacadas

- **Retiro en tienda:** envío gratis; valida stock de la tienda elegida
- **Delivery Express:** + S/ 4.90; exige dirección completa y horarios propios
- **Aprovecha+:** ahorro extra desde 3 unidades en canasta
- **CSRF** en formularios de carrito y login; cookies `HttpOnly` + `SameSite=Lax`
- **Tope** de 99 unidades por línea de carrito

---

## Estructura del repositorio

```text
sgc-mass-online/
├── admin/                  # Login, dashboard, productos, inventario, pedidos
├── cliente_interfaces/     # Home, catálogo, PDP, carrito, checkout, confirmación
├── includes/               # init, data demo, functions, layouts, components
├── assets/                 # CSS, JS, logo
├── docs/img/               # Banner y previews del README
├── tests/                  # Suites CLI de calidad
├── index.php               # Entrada → tienda
├── MEJORAS.md              # Historial de correcciones por rama
└── README.md
```

## Pruebas (calidad de software)

```bash
php tests/auth_test.php
php tests/negocio_test.php
php tests/productos_admin_test.php
php tests/carrito_test.php
php tests/csrf_test.php
```

Las suites validan autenticación, checkout (retiro/express/cupones), catálogo admin WMS, límites de carrito y tokens CSRF.

## Mejoras recientes

Detalle completo en [`MEJORAS.md`](MEJORAS.md):

| Rama | Tipo | Resumen |
|------|------|---------|
| `fix/cupones-checkout` | Bug | Cupones Club Mass consistentes (`CLUBMASS10` / `CLUBMASS60`) |
| `fix/carrito-cantidad-maxima` | Bug | Tope 99 unidades en agregar/cambiar |
| `mejora/seguridad-csrf` | Seguridad | CSRF + cookies de sesión endurecidas |
| `docs/readme-proyecto` | Docs | Documentación de arranque |
| `mejora/csrf-admin-productos` | Seguridad | CSRF en productos admin |
| `mejora/session-regenerate-login` | Seguridad | Regenerar sesión al login |
| `mejora/headers-seguridad` | Seguridad | Headers HTTP en Apache |
| `fix/sanitizar-redirect-volver` | Bug | Redirect `volver` seguro |
| `mejora/accesibilidad-carrito-aria` | a11y | ARIA del carrito |
| `mejora/runner-tests-cli` | Calidad | Runner `tests/run_all.php` |
| *(más)* | — | Ver [`MEJORAS.md`](MEJORAS.md) |

---

## Stack técnico

- **Backend:** PHP 8 (strict types, sesiones)
- **Frontend:** HTML semántico, CSS por módulos (`tokens`, `catalogo`, `pedido`, `admin`…)
- **JS:** UI ligera (drawer carrito, qty, countdown)
- **Datos:** catálogo y KPIs demo en `includes/data.php` (sin base de datos)

## Autor / contexto

Proyecto académico de **Pruebas y Calidad de Software** — demo funcional de tienda Mass Online y panel SGC-Mass.

---

<p align="center">
  <img src="https://raw.githubusercontent.com/73065290-prog/paginaweb/main/docs/img/logo-mass.png" alt="Mass Online" height="36" />
  <br/>
  <sub>Mass Online · SGC-Mass — demo PHP · uso académico</sub>
</p>
