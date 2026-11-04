# PROMPT 23: Pruebas de cabeceras HTTP y de CSRF en el admin

**Intent:** INT-002  
**Bolts:** INT-002-BOLT-003, INT-002-BOLT-004 y INT-002-BOLT-007  
**HU:** INT-002-HU-23  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en PHP nativo 8.1+, seguridad web, protección CSRF, cabeceras HTTP, Apache, PHPUnit y pruebas de integración.

Trabaja sobre el proyecto existente. Inspecciona primero el CSRF, las rutas de administración y las cabeceras de Apache. Hay que demostrar con pruebas que las protecciones funcionan, sin debilitarlas ni quitar funcionalidades.

## 2. Registro obligatorio del prompt y evidencias

Antes de implementar, guarda este prompt en:

`PROMTS/23-Pruebas-cabeceras-http-csrf-admin.md`

Guarda salidas y resultados en:

`PROMTS/evidencias/23-cabeceras-http-csrf.txt`

Reglas:

- Si `PROMTS/` o `PROMTS/evidencias/` no existe, créala.
- Conserva la estructura de los prompts anteriores y guarda el archivo en UTF-8.
- No incluyas tokens CSRF, credenciales ni información sensible.
- No declares una protección funcional solo porque el código contiene una validación.
- No presentes una comprobación HTTP como aprobada si Apache no respondió.
- La creación de estos archivos no autoriza cambios fuera del alcance.

## 3. Contexto

Las cabeceras HTTP de seguridad y la protección CSRF del alta y la edición de productos en el panel de administración no tienen pruebas automatizadas específicas.

Hay que comprobar que:

- Un POST de alta o edición sin token CSRF válido sea rechazado.
- El servidor envíe las cabeceras configuradas en `.htaccess`.
- Las protecciones funcionen en el entorno real de ejecución.

## 4. Objetivo

Crear pruebas automatizadas del CSRF administrativo de productos y comprobar por HTTP las cabeceras de seguridad de `.htaccess`.

## 5. Alcance

Crea o modifica únicamente:

- `tests/Feature/CsrfAdminProductosTest.php`
- `scripts/verificar_cabeceras.php`
- `.htaccess`

Y este prompt, más la evidencia en `PROMTS/evidencias/23-cabeceras-http-csrf.txt`.

Si hace falta otro archivo, detente, explica el cambio y espera autorización.

## 6. Pasos

1. Inspecciona `.htaccess`, la generación y validación del token, las rutas reales de alta y edición, los controladores y validadores, y la estructura de PHPUnit. No supongas nombres de rutas, parámetros o métodos.
2. Crea `tests/Feature/CsrfAdminProductosTest.php`. Para alta y edición, envía un POST con estructura válida al endpoint real, sin token, y comprueba el rechazo que implementa el proyecto. Comprueba que no se cree ni se modifique ningún registro. Incluye un token inválido cuando la implementación lo permita. No desactives el CSRF ni simules el rechazo. Usa base de pruebas aislada si hay persistencia. No ejecutes solicitudes destructivas contra desarrollo o producción. No uses productos reales para modificar o eliminar.
3. Crea `scripts/verificar_cabeceras.php`. Debe usar `curl -I`, comprobar `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` y el resto de cabeceras de seguridad de `.htaccess`. Para cada una registra presencia, valor recibido y si coincide. Distingue presente, ausente e incorrecta. URL configurable, coherente con XAMPP. Maneja fallos de conexión y respuestas inesperadas. Resumen aprobado o fallido. Código de salida distinto de cero si algo falla. No envíes credenciales ni modifiques datos.
4. Comprueba que Apache envíe las cabeceras. Si falta alguna, distingue `.htaccess`, Apache y el entorno. Corrige solo dentro del alcance y no debilites protecciones. Si el arreglo está fuera de `.htaccess`, documéntalo y pide autorización.
5. Ejecuta la prueba de alta, la de edición, la de token inválido y el script de cabeceras. Confirma que los rechazos no cambian registros. Repite tras cualquier corrección.

## 7. Restricciones

- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No debilites cabeceras ni desactives reglas de SonarQube.
- No uses comentarios para ocultar incidencias.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push. Un commit local por bolt, con el formato `tipo(INT-XXX-BOLT-NNN): descripción`, solo si Git está disponible.
- No marques un bolt como resuelto sin haberlo ejecutado y guardado la evidencia.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 8. Criterios de aceptación

1. Hay una prueba automatizada de alta sin token CSRF.
2. Hay una prueba automatizada de edición sin token CSRF.
3. Ambas las rechaza la protección CSRF real.
4. Las solicitudes rechazadas no crean ni modifican registros.
5. La comprobación con token inválido funciona cuando corresponde.
6. El script verifica `X-Content-Type-Options`, `X-Frame-Options` y `Referrer-Policy`.
7. También verifica las demás cabeceras de seguridad de `.htaccess`.
8. El script señala cabeceras ausentes o incorrectas y sale con error.
9. No se debilita ninguna protección.
10. Los resultados quedan en evidencia verificable.

## 9. Pruebas requeridas

1. `composer test` (PHPUnit).
2. `php tests/run_all.php`.
3. `php scripts/smoke_views.php`.
4. `php scripts/smoke_auth_logout.php`.
5. `tests/Feature/CsrfAdminProductosTest.php`.
6. `php scripts/verificar_cabeceras.php` con Apache/XAMPP activo.

## 10. Entrega

Resume archivos, el estado real de cada bolt, comandos y resultados, CSRF de alta, edición y token inválido, cabeceras esperadas y recibidas, la ruta de la evidencia, pendientes y el modelo de IA. No declares los bolts completados solo porque existan los archivos de prueba.
