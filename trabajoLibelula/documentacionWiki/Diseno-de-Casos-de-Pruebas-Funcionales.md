# Diseño de Casos de Pruebas Funcionales

> Conforme a ISO/IEC/IEEE 29119-3 (Test Design Specification). Pruebas de **caja negra, ejecución manual**, sobre los requisitos funcionales del producto.

| Campo | Detalle |
|-------|---------|
| **Documento** | Diseño de Casos de Pruebas Funcionales — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Tipo de prueba** | Funcional / Caja negra / Manual |
| **Ambiente de ejecución** | QA (despliegue único compartido por el equipo) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estándar** | ISO/IEC/IEEE 29119-3 |

---

## 1. Propósito y enfoque

Este documento especifica el **diseño** de los casos de prueba funcionales de caja negra para los requisitos funcionales núcleo de Snipe-IT. Las pruebas se ejecutan **manualmente** en un ambiente de **QA** único, sin acceso al código fuente, validando que el comportamiento observable cumple la especificación funcional.

La **ejecución y los resultados** se consignan en el [Informe de Casos de Pruebas Funcionales](Informe-de-Casos-de-Pruebas-Funcionales).

### 1.1 Distinción frente a las pruebas unitarias
| Aspecto | Pruebas unitarias | Pruebas funcionales (este documento) |
|---------|-------------------|----------------------------------------|
| Caja | Blanca (acceso al código) | Negra (sin acceso al código) |
| Ambiente | DEV (desarrollador) | QA (equipo) |
| Ejecución | Automatizada (PHPUnit) | Manual |
| Objetivo | Lógica interna de métodos | Cumplimiento de requisitos del usuario |

---

## 2. Requisitos funcionales bajo prueba

Requisitos derivados de la funcionalidad verificable del producto:

| ID Req. | Requisito funcional | Subsistema |
|---------|---------------------|------------|
| RF-01 | Registrar un activo con etiqueta (asset tag) única | Activos |
| RF-02 | Asignar un activo a un usuario (checkout) | Activos / Checkout |
| RF-03 | Devolver un activo asignado (checkin) | Activos / Checkout |
| RF-04 | Crear una licencia con un número definido de asientos | Licencias |
| RF-05 | Asignar un asiento de licencia a un usuario o activo | Licencias |
| RF-06 | Descontar stock de un consumible al asignarlo | Inventario |
| RF-07 | Impedir la eliminación de una categoría con elementos asociados | Checkout / Categorías |
| RF-08 | Reflejar la disponibilidad del activo según su status label | Activos |

---

## 3. Técnicas de caja negra aplicadas

| Técnica | Abreviatura | Uso |
|---------|-------------|-----|
| Partición de equivalencia | PE | Entradas válidas/inválidas de formularios |
| Análisis de valores límite | AVL | Cantidades de stock y asientos (0, 1, máximo) |
| Tabla de decisión | TD | Reglas de disponibilidad y eliminación |
| Transición de estados | TE | Ciclo de vida del activo (disponible → asignado → devuelto) |

---

## 4. Especificación de casos de prueba

> Cada caso indica precondición, datos, pasos, resultado esperado y técnica. La columna de resultado real se completa en el informe.

### CPF-01 — Registrar activo con asset tag único (RF-01, PE/AVL)
- **Precondición:** existe al menos un modelo de activo y un status label.
- **Datos:** asset tag nuevo `A-1001`; modelo válido; status "Ready to Deploy".
- **Pasos:** Assets → Create New → completar formulario → Save.
- **Resultado esperado:** el activo se crea y aparece en el listado con el tag `A-1001`.

### CPF-02 — Rechazar asset tag duplicado (RF-01, PE inválida)
- **Precondición:** ya existe un activo con tag `A-1001`.
- **Datos:** asset tag `A-1001` (repetido).
- **Pasos:** crear otro activo con el mismo tag.
- **Resultado esperado:** el sistema rechaza el registro y muestra error de unicidad.

### CPF-03 — Checkout de activo a usuario (RF-02, TE)
- **Precondición:** activo en estado disponible (deployable) y usuario activo.
- **Pasos:** seleccionar activo → Checkout → tipo "User" → elegir usuario → Checkout.
- **Resultado esperado:** el activo pasa a estado "Deployed"; queda asignado al usuario; se registra en el historial.

### CPF-04 — Checkin de activo asignado (RF-03, TE)
- **Precondición:** activo previamente asignado (CPF-03).
- **Pasos:** seleccionar activo → Checkin → confirmar.
- **Resultado esperado:** el activo vuelve a estado disponible; se libera la asignación; se registra en el historial.

### CPF-05 — Checkout sobre activo no desplegable (RF-08, TD)
- **Precondición:** activo con status label NO deployable (p. ej. "Archived").
- **Pasos:** intentar checkout.
- **Resultado esperado:** el sistema impide la asignación o no ofrece el activo como disponible.

### CPF-06 — Crear licencia con N asientos (RF-04, AVL)
- **Datos:** licencia con `seats = 5`.
- **Pasos:** Licenses → Create → completar → Save.
- **Resultado esperado:** la licencia se crea con 5 asientos disponibles.

### CPF-07 — Asignar asiento de licencia (RF-05, TE)
- **Precondición:** licencia con asientos libres (CPF-06).
- **Pasos:** checkout de un asiento a un usuario.
- **Resultado esperado:** asientos disponibles disminuyen en 1; el usuario figura como asignado.

### CPF-08 — Agotar asientos de licencia (RF-05, AVL borde superior)
- **Precondición:** licencia con 1 asiento disponible.
- **Pasos:** asignar el último asiento; intentar asignar uno más.
- **Resultado esperado:** el primero se asigna; el segundo se rechaza por falta de asientos.

### CPF-09 — Descuento de stock de consumible (RF-06, AVL)
- **Precondición:** consumible con `qty = 2`.
- **Pasos:** realizar checkout del consumible a un usuario.
- **Resultado esperado:** la cantidad restante disminuye a 1; al llegar a 0 no se permite más checkout.

### CPF-10 — Impedir eliminación de categoría con items (RF-07, TD)
- **Precondición:** categoría con al menos un elemento asociado (activo/accesorio/etc.).
- **Pasos:** intentar eliminar la categoría.
- **Resultado esperado:** el sistema impide la eliminación e informa que la categoría no está vacía.

### CPF-11 — Eliminar categoría vacía (RF-07, TD complemento)
- **Precondición:** categoría sin elementos asociados y usuario con permiso.
- **Pasos:** eliminar la categoría.
- **Resultado esperado:** la categoría se elimina correctamente.

---

## 5. Tabla de decisión — Eliminación de categoría (RF-07)

| Condición | R1 | R2 | R3 |
|-----------|----|----|----|
| ¿Tiene elementos asociados? | Sí | No | No |
| ¿Usuario con permiso de borrado? | — | No | Sí |
| **Acción esperada** | Rechazar | Rechazar | Eliminar |

Cobertura: CPF-10 (R1), CPF-11 (R3). R2 se añade como caso negativo de permisos.

---

## 6. Diagrama de transición de estados — Activo (RF-02/03/08)

```
[Disponible] --checkout--> [Asignado] --checkin--> [Disponible]
     |                                                   ^
     +--(status no deployable)--> [No disponible] -------+
```
Cobertura: CPF-03, CPF-04, CPF-05.

---

## 7. Trazabilidad

Cada caso CPF-XX se vincula a su requisito (RF-XX) y a su evidencia de ejecución en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad) y en el [Informe de Casos de Pruebas Funcionales](Informe-de-Casos-de-Pruebas-Funcionales).

---

*Fin del documento — Diseño de Casos de Pruebas Funcionales.*
