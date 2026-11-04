# Despliegue

**Estado:** No aplica  
**Fecha de la evaluación:** 2026-11-03  
**HU:** INT-006-HU-29  
**Entorno vigente:** XAMPP local (PHP 8.1+ y MySQL/MariaDB con PDO)

No hay URL pública. No se eligió hosting y no se publicó una versión de prueba.

## Qué pide el registro del curso

En `docs/Matriz_de_registro_paginaweb.xlsx`, hoja Bolts:

| Columna | Pregunta |
| --- | --- |
| W | ¿Existe evidencia de despliegue en producción? |
| AZ | ¿Evidencia de despliegue en producción o N.A justificado? |

La pregunta de la columna AZ admite dejar el punto como no aplicable cuando la justificación existe. Las 55 filas de datos tienen «No aplica» en W y en AZ. La evidencia de W dice: «Proyecto académico en XAMPP local; sin despliegue a producción». La evidencia de AZ dice: «Proyecto académico local sin despliegue; N.A. justificado».

En la hoja Brechas y pendientes, la fila Despliegue indica que no hay despliegue a producción, que el proyecto corre en XAMPP local y que un despliegue de prueba corresponde solo si el curso lo exige.

## Por qué no se publica

En el repositorio no aparece un sílabo, una rúbrica ni una consigna que exija una URL pública. El README, el plan de pruebas y el informe describen la ejecución en XAMPP. La existencia de la pregunta en la matriz no convierte el despliegue en obligatorio: la propia columna AZ deja abierta la respuesta «N.A. justificado».

Por eso se conserva el funcionamiento local y no se inicia la publicación.

## Cómo se ejecuta en local

1. PHP 8.1+ con Apache, por ejemplo XAMPP, y la carpeta del proyecto bajo el document root.
2. Tienda en `http://localhost/<carpeta>/` y acceso admin en `http://localhost/<carpeta>/admin/login.php`.
3. MySQL/MariaDB opcional: importar `database/schema.sql` y después `database/seed.sql`, y copiar `backend/config/database.example.php` a `backend/config/database.local.php`.
4. La conexión de la aplicación usa PDO. `database.local.php` no se versiona.

El procedimiento completo, los errores de importación y la verificación con `php scripts/verificar_bd.php` están en el README, sección «Cómo ejecutar».

## Qué no se hizo

- No hay hosting de prueba ni plan gratuito o de pago activado.
- No hay URL pública verificada.
- No hay capturas de un flujo publicado. No se simula un despliegue exitoso.
- No se modificó PHP, la configuración de la aplicación, los scripts ni el esquema de la base.
- No se subieron credenciales ni datos personales.
- Los usuarios demo siguen como están en el entorno local. No existe un entorno público en el que hubiera que apagarlos.

## Limitaciones

La evidencia de las columnas W y AZ sigue siendo el entorno local. Una URL pública solo tendría sentido si el curso la pide de forma explícita y se autoriza el hosting. Hasta entonces el estado correcto es «No aplica».
