# PROMPT 29: Despliegue de prueba y evidencia (opcional)

**Intent:** INT-006  
**Bolts:** columnas W y AZ de la matriz (sin identificador de bolt)  
**HU:** INT-006-HU-29  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como ingeniero de software senior especializado en despliegues de PHP nativo, hosting PHP/MySQL, administración de bases de datos, seguridad y validación funcional.

Evalúa si conviene publicar una versión de prueba sin cambiar la arquitectura ni exponer información sensible. El despliegue es opcional y solo se hace si el curso exige una URL pública como evidencia.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/29-despliegue-prueba-evidencia.md`

Guarda la evidencia en:

`PROMTS/evidencias/29-despliegue-prueba-evidencia/`

Reglas:

- UTF-8 sin BOM, con la misma estructura que los prompts anteriores.
- No guardes esta evidencia en `docs/pruebas/evidencias/`.
- No incluyas credenciales, contraseñas ni secretos.
- No presentes una URL ni un flujo público como verificados si no se publicaron.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 3. Contexto

Las filas de despliegue de la matriz están en «No aplica» porque el proyecto corre en XAMPP. Hay que decidir si un despliegue de prueba corresponde y, solo si el curso lo exige, publicar una versión que muestre el flujo de compra.

## 4. Objetivo

Si el curso exige evidencia de despliegue, publicar una versión de prueba segura y documentar una URL comprobada y capturas reales.

Si no es obligatorio, conservar la ejecución local y documentar por qué sigue siendo «No aplica».

## 5. Alcance

Modifica únicamente:

- `README.md`
- Documentación de despliegue existente o nueva, en la ubicación documental que ya usa el proyecto

Y este prompt, más la evidencia en `PROMTS/evidencias/29-despliegue-prueba-evidencia/`.

No modifiques PHP, configuración de aplicación, scripts ni esquemas. Si el despliegue exigiera otro archivo, detente y espera autorización.

## 6. Pasos

1. Revisa los requisitos del curso y la documentación. No des por obligatorio el despliegue solo porque la matriz tenga una pregunta. Si no es obligatorio, documenta la justificación y detén la publicación.
2. Si es obligatorio, evalúa un hosting compatible con PHP 8.1+, PDO y MySQL/MariaDB. No contrates planes de pago sin autorización.
3. Prepara la configuración de prueba sin tocar las credenciales locales, sin publicar secretos y con los usuarios demo deshabilitados en el entorno público. Usa datos ficticios.
4. Importa esquema y datos iniciales según la guía existente y comprueba PDO.
5. Publica solo los archivos necesarios, sin cambiar funcionalidad ni diseño, y sin mostrar errores internos.
6. Verifica catálogo, carrito, checkout, confirmación, registro en la base y persistencia al recargar. No simules un éxito si un servicio externo no está disponible.
7. Actualiza `README.md` y la documentación de despliegue con el estado real. Incluye una URL solo si fue verificada.
8. Guarda el prompt y la evidencia. Si no hubo despliegue, guarda la justificación.

## 7. Restricciones

- No subas credenciales reales.
- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No cambies el diseño ni elimines funcionalidades.
- No publiques datos personales ni de clientes reales.
- No contrates servicios de pago.
- No hagas push.
- No marques el despliegue como completado sin URL y flujo comprobados.
- No inventes un identificador de bolt. El commit `tipo(INT-XXX-BOLT-NNN): descripción` solo corresponde si existe un bolt asociado y queda completado.

## 8. Criterios de aceptación

1. Queda determinado si el despliegue es obligatorio.
2. Si no lo es, la justificación de «No aplica» está documentada.
3. Si lo es, existe una URL de prueba accesible y verificada.
4. La aplicación sigue en PHP y MySQL/MariaDB con PDO.
5. En un entorno público, los usuarios demo permanecen deshabilitados.
6. El flujo catálogo, carrito, pedido y confirmación funciona cuando hay despliegue.
7. El pedido de prueba queda en la base cuando hay despliegue.
8. Las capturas, si el despliegue se hizo, son reales.
9. `README.md` y la documentación de despliegue reflejan el estado real.
10. El prompt y las evidencias están en `PROMTS/`.

## 9. Entrega

Resume archivos, si el despliegue se hizo o queda en «No aplica», el hosting si existe, la URL si fue verificada, el resultado del flujo, las capturas y su ruta, los cambios de documentación, el commit si existe, los pendientes y el modelo de IA.
