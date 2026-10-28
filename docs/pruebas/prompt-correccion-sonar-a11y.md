# PROMPT: Corrección de incidencias de SonarQube

Analiza y corrige las incidencias detectadas por SonarQube en mi proyecto de PHP nativo. El objetivo es reducir a **0 las incidencias identificadas en los archivos y líneas indicados**, especialmente las relacionadas con mantenibilidad, fiabilidad y accesibilidad.

## 1. Archivo `assets/js/admin.js`

Corrige las siguientes incidencias de consistencia de código:

- Reemplaza `parseFloat()` por `Number.parseFloat()` en las líneas reportadas 58, 59, 167, 168 y 169.
- Reemplaza `parseInt()` por `Number.parseInt()` en las líneas reportadas 98, 107, 108, 175 y 176.

Revisa el código alrededor de cada incidencia y todas las apariciones correspondientes a los problemas reportados, porque los números de línea pueden cambiar.

**Importante:** conserva los argumentos, las bases numéricas cuando correspondan, los valores devueltos y el comportamiento original de las operaciones. No cambies la lógica de los cálculos ni otras funcionalidades del archivo.

## 2. Archivo `cliente_interfaces/catalogo.php`

Corrige las incidencias de accesibilidad detectadas en las líneas 113 y 172:

- Asocia cada campo de entrada con una etiqueta `<label>` válida y con texto accesible que describa su propósito.
- Agrega un atributo `id` único a cada campo que lo necesite.
- Asocia la etiqueta con su campo utilizando el atributo `for`, cuyo valor debe coincidir exactamente con el `id` del campo.
- Evita etiquetas vacías, identificadores duplicados y asociaciones incorrectas.

Si el diseño actual no muestra etiquetas visualmente, utiliza una solución accesible que conserve la apariencia de la página y permita que los lectores de pantalla identifiquen el propósito del campo.

## 3. Archivo `includes/admin/header.php`

Corrige la incidencia de accesibilidad detectada en la línea 27:

- Añade un atributo `id` único al campo de entrada reportado.
- Crea o corrige su etiqueta `<label>` con texto descriptivo.
- Asocia la etiqueta y el campo mediante `for` e `id`.
- Conserva el funcionamiento original del encabezado administrativo y de sus controles.

## 4. Archivo `includes/components/cart-line.php`

Corrige la incidencia de accesibilidad detectada en la línea 18:

- Asegúrate de que la etiqueta `<label>` esté vinculada al control de formulario correspondiente.
- Añade un texto accesible y descriptivo a la etiqueta.
- Asigna un `id` único al control y utiliza el mismo valor en el atributo `for` de la etiqueta.
- Si el componente se genera varias veces dentro del carrito, garantiza que los identificadores sean únicos en el HTML final.
- Conserva el comportamiento actual del carrito, las cantidades, los productos y las operaciones existentes.

## 5. Restricciones obligatorias

1. Modifica únicamente los fragmentos necesarios para resolver las incidencias indicadas en estos cuatro archivos.
2. No cambies la arquitectura, la estructura de carpetas, los nombres de los archivos ni las tecnologías utilizadas.
3. No instales dependencias, frameworks o librerías adicionales.
4. No alteres la lógica de negocio, las consultas SQL, las sesiones, las rutas, el diseño visual ni las funcionalidades existentes.
5. No elimines validaciones ni desactives reglas de SonarQube para ocultar las incidencias.
6. No utilices comentarios para justificar que una incidencia puede ignorarse. Corrige el código cuando corresponda.
7. Evita refactorizaciones generales y cambios que no sean necesarios para solucionar los problemas reportados.

## 6. Verificación final

Después de aplicar las correcciones:

- Revisa cada una de las incidencias descritas para verificar que realmente se haya solucionado.
- Comprueba que los campos de formulario tengan etiquetas accesibles correctamente asociadas y que no existan identificadores duplicados en componentes repetidos.
- Verifica que los reemplazos de `parseFloat` y `parseInt` mantengan el comportamiento original.
- Comprueba la sintaxis de los archivos PHP modificados y ejecuta las verificaciones disponibles para JavaScript.
- Revisa el diff para asegurarte de que no se hayan modificado archivos ni funcionalidades ajenos al alcance.
- No marques como resuelta una incidencia sin haber comprobado la corrección correspondiente.

Al terminar, presenta un resumen con los archivos modificados, las incidencias corregidas y las verificaciones ejecutadas. Si alguna incidencia no puede resolverse sin información adicional, explica exactamente cuál es y por qué.

**Resultado esperado:** corregir todas las incidencias enumeradas para que dejen de aparecer en el siguiente análisis de SonarQube, sin introducir errores ni alterar las funcionalidades existentes del proyecto.
