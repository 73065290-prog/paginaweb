# PROMPT 28: Gestión de credenciales demo y endurecimiento de autenticación

**Intent:** INT-001  
**Bolts:** INT-001-BOLT-006; G22  
**HU:** INT-001-HU-28  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como desarrollador de software senior especializado en PHP 8.1+, seguridad de autenticación, gestión de credenciales, configuración de entornos y pruebas automatizadas.

Antes de editar, revisa el inicio de sesión actual. Aísla las credenciales de demostración sin quitarlas de los entornos de prueba autorizados, y evita que puedan usarse por accidente en producción.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/28-gestion-credenciales-demo-autenticacion.md`

Guarda la evidencia en:

`PROMTS/evidencias/28-gestion-credenciales-demo-autenticacion/`

Reglas:

- UTF-8 sin BOM, con la misma estructura que los prompts anteriores.
- No guardes contraseñas reales, tokens ni secretos en la evidencia.
- No presentes una prueba como exitosa si no se ejecutó.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 3. Contexto

Las credenciales demo de administrador y de cliente están en pruebas y documentación. Hay que separarlas de la configuración de producción, controlar de forma explícita cuándo pueden usarse y documentar ese funcionamiento.

## 4. Objetivo

Aislar las credenciales demo del código y de la configuración de producción, permitirlas solo en entornos autorizados y reforzar el inicio de sesión con un control de intentos cuando la arquitectura existente lo permita.

## 5. Alcance

Modifica únicamente:

- `includes/functions.php`
- `backend/config/*`
- `tests/**`
- `README.md`

Y este prompt, más la evidencia en `PROMTS/evidencias/28-gestion-credenciales-demo-autenticacion/`.

Si hace falta otro archivo, detente y espera autorización. Indica el archivo, por qué es indispensable, el cambio propuesto y el impacto.

## 6. Pasos

1. Revisa el inicio de sesión, dónde se definen y validan los usuarios demo, la configuración de `backend/config/` y cómo se distingue el entorno local del de producción.
2. Traslada la definición demo a una configuración local o a un seed marcado como exclusivo de demostración. No las dejes habilitadas por defecto en producción. Conserva el acceso demo de las pruebas. No guardes contraseñas en texto plano; usa el hash y la verificación que ya tiene el proyecto.
3. Agrega `APP_DEMO_USERS_ENABLED`. En producción los usuarios demo quedan deshabilitados aunque la variable pida habilitarlos. Si la variable falta, está vacía o es inválida, permanecen deshabilitados fuera del entorno local.
4. Adapta las pruebas: demo permitido en local o prueba autorizada; demo rechazado en producción; variable ausente o inválida no lo habilita; las cuentas reales siguen autenticándose; las respuestas de credenciales incorrectas no revelan si el usuario existe.
5. Revisa si hay límite de intentos o retardo. Si no existe y cabe en la arquitectura actual, añádelo sin dependencias nuevas y sin bloqueo permanente. Si exige archivos fuera del alcance, detente y pide autorización.
6. Documenta en `README.md` qué son los usuarios demo, cómo habilitarlos, los valores de la variable, que no deben usarse en producción y cómo comprobar el entorno. No publiques secretos reales.
7. Comprueba producción y local solo mediante pruebas, sin cambiar el servidor real.
8. Guarda el prompt y la evidencia.

## 7. Restricciones

- No elimines el acceso demo de las pruebas.
- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No cambies el diseño ni elimines funcionalidades.
- No expongas credenciales reales, tokens ni secretos.
- No hagas push.
- Un commit local por bolt completado: `tipo(INT-001-BOLT-006): descripción`. Registra G22 solo si está identificado.
- Si un bolt no puede completarse, documéntalo y déjalo pendiente. No lo marques como hecho sin evidencia.

## 8. Criterios de aceptación

1. Las credenciales demo quedan aisladas de la configuración de producción.
2. Existe una variable para habilitar o deshabilitar los usuarios demo.
3. Con producción activa, los usuarios demo no inician sesión aunque la variable esté en un valor de habilitación.
4. En local y en prueba autorizada, el acceso demo sigue funcionando cuando está habilitado.
5. Las pruebas cubren ambos escenarios y los valores ausentes o inválidos.
6. Las cuentas reales siguen funcionando.
7. Los errores de autenticación no revelan si un usuario existe.
8. El límite de intentos o el retardo quedó verificado, o quedó documentado por qué no es viable dentro del alcance.
9. `README.md` describe la configuración y el uso de los usuarios demo.
10. El prompt y las evidencias están en `PROMTS/`.

## 9. Entrega

Resume archivos, el estado real de INT-001-BOLT-006 y G22, la ubicación de la configuración demo, el comportamiento de `APP_DEMO_USERS_ENABLED`, las pruebas de autenticación, el límite de intentos, cada comando con su resultado, la ruta de evidencias, el commit si existe, los pendientes y el modelo de IA.
