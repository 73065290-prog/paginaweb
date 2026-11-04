# PROMPT 25: Resolver ramas sin fusionar y flujo de Pull Request

**Intent:** INT-006  
**Bolts:** INT-006-BOLT-009  
**HU:** INT-006-HU-25  
**Modelo IA:** Grok 4.7  
**Fecha:** 2026-11-03  
**Responsable:** cursor12310-sys

---

## 1. Rol

Actúa como ingeniero de software senior especializado en Git, GitHub, ramas, revisión de código, Pull Requests e integración continua.

Antes de cambiar nada, analiza el estado real del repositorio. Conserva el historial, la trazabilidad y una integración segura con `main`.

## 2. Registro obligatorio del prompt y evidencias

Guarda este prompt en:

`PROMTS/25-resolver-ramas-pull-request.md`

Guarda la evidencia en:

`PROMTS/evidencias/25-resolver-ramas-pull-request/`

Reglas:

- UTF-8 y la misma estructura que los prompts anteriores.
- Si la carpeta no existe, créala.
- No inventes enlaces de Pull Request ni capturas.
- Si una operación exige publicar en GitHub, déjala pendiente.
- No presentes como hecha una fusión, una protección de rama o un commit que no se haya verificado.

## 3. Contexto

Hay tres ramas remotas pendientes:

- `mejora/carrito-limite-stock`
- `feature/mejoras-admin-qa-cliente`
- `fix/sonar-a11y-parse-labels`

También hubo merges locales sin Pull Request. Los commits `55ce88d` y `eb68b44` parecen duplicar el refactor de frontend, backend y MySQL. Hay que comprobarlo con los árboles, no solo con el mensaje.

## 4. Objetivo

Decidir el destino de cada rama y dejar documentado un flujo de Pull Request que proteja `main`: revisión y validación de CI antes de integrar.

## 5. Alcance

- Repositorio Git.
- `MEJORAS.md`.
- Configuración del repositorio en GitHub, solo si se puede aplicar sin violar la prohibición de publicar.

Y este prompt, más la evidencia en `PROMTS/evidencias/25-resolver-ramas-pull-request/`.

Si hace falta otro archivo, detente, explica el cambio y espera autorización.

## 6. Pasos

1. Revisa la rama actual, el árbol de trabajo, las ramas locales y remotas, los merges locales sin Pull Request y los commits `55ce88d` y `eb68b44`.
2. Para cada rama pendiente ejecuta la comparación `git diff main...rama`, con su historial. Decide fusionar por Pull Request, rehacer o cerrar, y justifica cambios, conflictos y riesgos.
3. Relaciona `mejora/carrito-limite-stock` con el PROMPT 09 y con los servicios de carrito y cupones. No dupliques lógica ya integrada.
4. Compara archivos y árboles de `55ce88d` y `eb68b44`. Indica qué cambio se conserva. No reescribas `main` ni borres commits.
5. Prepara un Pull Request independiente solo para las ramas cuya decisión sea fusionar. No abras PR de ramas a rehacer o cerrar. No digas que un PR existe si no se creó.
6. Protege `main` para exigir Pull Request, revisión y CI en verde cuando el workflow exista y los permisos lo permitan. Si el plan, los permisos o la prohibición de publicar lo impiden, documéntalo y no afirmes que la regla quedó activa.
7. Actualiza `MEJORAS.md` con la decisión real. No marques como fusionado lo que siga pendiente.
8. Guarda el prompt y la evidencia. Los enlaces de PR quedan pendientes si no se autoriza publicar.

## 7. Restricciones

- No reescribas el historial de `main`.
- PHP nativo 8.1+, MySQL/MariaDB con PDO, HTML, CSS y JavaScript nativos, XAMPP.
- Sin Laravel, React, Vue, Node.js ni otros frameworks.
- No instales dependencias nuevas.
- No desactives pruebas ni reglas de SonarQube.
- No cambies el diseño ni elimines funcionalidades.
- No hagas push ni publiques en GitHub sin autorización expresa.
- No borres ramas ni referencias remotas sin autorización expresa.
- Un commit local en la rama de INT-006-BOLT-009, con el mensaje `tipo(INT-006-BOLT-009): descripción`, solo con archivos de este alcance.
- No actualices `Matriz_de_registro_paginaweb.xlsx`.

## 8. Criterios de aceptación

1. Las tres ramas tienen una decisión justificada.
2. La comparación de cada rama con `main` está registrada.
3. La relación con el PROMPT 09 está evaluada.
4. `55ce88d` y `eb68b44` tienen un análisis de duplicidad.
5. Cada rama que deba integrarse tiene un PR preparado o creado, sin duplicar cambios.
6. `main` exige PR y CI cuando los permisos lo permitan.
7. `MEJORAS.md` refleja el estado real.
8. El prompt y las evidencias están en `PROMTS/`.
9. No se reescribió `main` ni se hicieron operaciones destructivas.

## 9. Entrega

Resume archivos, el estado real de INT-006-BOLT-009, la decisión de cada rama, el análisis de los dos commits, los PR reales o su ausencia, la protección de `main`, las verificaciones, la ruta de evidencias, el commit local si existe, los pendientes y el modelo de IA.
