# Mass Online / SGC-Mass (`paginaweb`)

Tienda en línea y panel admin WMS (PHP nativo + XAMPP), con separación **frontend / backend** y soporte opcional de **MySQL/MariaDB** vía PDO.

## Arquitectura

```text
paginaweb/
├── backend/           # Config, PDO, repositorios, servicios, middleware, rutas
├── frontend/          # Vistas PHP + assets (css/js/img)
├── database/          # schema.sql + seed.sql
├── admin/             # Shims de compatibilidad → frontend/views/admin
├── cliente_interfaces/# Shims de compatibilidad → frontend/views/cliente
├── includes/          # Puente (init + facades de negocio / demo fallback)
├── tests/             # PHPUnit
└── PROMTS/            # Prompts del proyecto (versionados)
```

- **Sin MySQL:** el sistema sigue usando datos demo (`includes/data.php`) y sesión.
- **Con MySQL:** catálogo, usuarios, cupones, pedidos y alta/edición de productos persisten en BD.

## Requisitos

- XAMPP (Apache + MySQL/MariaDB)
- PHP 8.1+ (probado en 8.2)
- Composer

## Base de datos (XAMPP)

1. Arranca **Apache** y **MySQL** en el panel de XAMPP.
2. Abre phpMyAdmin (`http://localhost/phpmyadmin`) o consola MySQL.
3. Importa en este orden:
   - `database/schema.sql`
   - `database/seed.sql`
4. Copia la configuración local:

```bat
copy backend\config\database.example.php backend\config\database.local.php
```

5. Edita `backend/config/database.local.php` si tu usuario/clave no son `root` / vacío.
6. Deja `"enabled" => true` en ese archivo.

Comprobar API de salud del backend:

`http://localhost/paginaweb/backend/public/index.php`  
(Debe responder JSON; si `.htaccess` bloquea `backend/`, usa un script CLI o descomenta temporalmente el deny solo en `public/`.)

> Nota: `backend/.htaccess` deniega el acceso HTTP a todo el backend. Para el endpoint JSON, usa CLI:

```bat
php backend\public\index.php
```

## Ejecutar el sistema

URLs (proyecto en `htdocs/paginaweb`):

| Área | URL |
| --- | --- |
| Tienda | http://localhost/paginaweb/ |
| Catálogo | http://localhost/paginaweb/cliente_interfaces/catalogo.php |
| Admin login | http://localhost/paginaweb/admin/login.php |

Credenciales demo: `admin` / `123456` o `cliente` / `123456`.

## Pruebas

```bat
cd d:\xampp\htdocs\paginaweb
composer install
composer test
php tests\run_all.php
composer test:coverage
```

La prueba `MysqlDisponibilidadTest` se **omite** si no hay conexión MySQL; con BD importada y `database.local.php` habilitado, valida persistencia de pedidos.

## Regenerar seed desde datos demo

```bat
php scripts\generate_seed.php
```

## SonarQube

Ver `docs/pruebas/05-guia-sonarqube.md` y `scripts\prepare-sonar.bat`.
