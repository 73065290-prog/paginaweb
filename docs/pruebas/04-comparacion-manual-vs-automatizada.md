# Comparación: pruebas manuales vs automatizadas

## 1. Alcance verificado

| Modalidad | Funcionalidades cubiertas |
| --- | --- |
| **Manual (exploratoria / UI)** | Navegación storefront, modal Agregar/Editar producto, toast de errores vacíos, login visual admin/cliente, dashboard Figma, logout navbar. |
| **Automatizada (PHPUnit)** | Reglas de negocio, validaciones, auth, carrito/sesión, confirmación de pedido, persistencia admin, filtro catálogo, SQLite lab (P01–P32). |

## 2. Coincidencias y diferencias

| Aspecto | Manual | Automatizada |
| --- | --- | --- |
| Alta producto vacía muestra errores | Observado en UI (toast + campos rojos) | P16–P19, P29 |
| Express cobra S/ 4.90 | Comprobable en checkout | P04, P09, P26 |
| Retiro gratis | UI resumen | P04, P27 |
| Login rol incorrecto | UI mensaje | P13 |
| Layout Figma / CSS | Solo manual | No automatizado |
| Pixel / UX responsive | Solo manual | No automatizado |
| Persistencia sesión | Indirecta al recargar | P21–P30 explícita |

## 3. Defectos detectados por modalidad

| Defecto | Detectado por | Notas |
| --- | --- | --- |
| Botón Guardar `.btn-mini` bloqueaba submit (no mostraba errores de alta) | Manual (sesión previa del proyecto) | Corregido en `assets/js/admin.js` antes de esta práctica |
| Assert P10 usaba clave `id` inexistente | Automatizada | Corregido en test (INC-01) |

## 4. Ventajas y limitaciones

| | Manual | Automatizada |
| --- | --- | --- |
| Ventajas | Valida UX real, Figma, navegador | Rápida, repetible, métricas, cobertura |
| Limitaciones | Costosa, no repetible al 100% | No ve CSS/layout; no sustituye QA visual |

## 5. Por qué no se automatizó todo

| Área no automatizada | Motivo |
| --- | --- |
| Playwright E2E completo | Fuera del stack mínimo de la práctica; requiere browser driver y tiempo de CI local |
| Comparación visual Figma | Subjetiva / herramientas de visual regression no pedidas |
| SonarQube ejecución | Lo ejecuta el estudiante (restricción de la consignia) |

## 6. Posibilidades de ampliación

- E2E Playwright sobre `cliente_interfaces/pedido.php` y `admin/productos.php?nuevo=1`.
- Mutation testing / Infection.php.
- Contract tests HTTP con `HttpClient` CLI contra Apache.
