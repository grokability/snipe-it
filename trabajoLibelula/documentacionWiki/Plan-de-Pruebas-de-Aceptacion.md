# Plan de Pruebas de Aceptación

> Conforme a ISO/IEC/IEEE 29119-3. Corresponde al **Hito 3 / Sprint 3-4**.

| Campo | Detalle |
|-------|---------|
| **Documento** | Plan de Pruebas de Aceptación — Snipe-IT |
| **Versión** | 1.0 (planificación) |
| **Hito / Sprint** | Hito 3 / Sprint 3-4 |
| **Nivel de prueba** | Aceptación (validación) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estándar** | ISO/IEC/IEEE 29119-3 |

---

## 1. Introducción y objetivos

Las pruebas de aceptación determinan si el sistema **satisface las necesidades del usuario** y los criterios definidos para considerar el producto "aceptado". Adoptan la perspectiva del **usuario/stakeholder** y se expresan mediante **criterios de aceptación** verificables, no mediante detalles técnicos.

**Objetivos:**
1. Validar que los flujos de negocio aportan el valor esperado al usuario final.
2. Confirmar el cumplimiento de los criterios de aceptación de las historias de usuario del backlog.
3. Emitir el veredicto de aceptación del producto en el contexto del curso.

---

## 2. Enfoque

- **Criterios de aceptación** redactados en formato verificable (Dado–Cuando–Entonces).
- **Ejecución manual** por un rol que actúa como usuario/stakeholder, sobre el entorno de staging.
- Trazabilidad directa con las **historias de usuario** registradas en GitHub Issues/Projects.

---

## 3. Criterios de aceptación por historia de usuario

| ID | Historia de usuario | Criterio de aceptación (Dado–Cuando–Entonces) |
|----|---------------------|------------------------------------------------|
| ACC-01 | Como administrador, quiero registrar activos | **Dado** un modelo y un estado, **cuando** registro un activo con tag único, **entonces** aparece en el inventario |
| ACC-02 | Como administrador, quiero asignar activos a empleados | **Dado** un activo disponible, **cuando** lo asigno a un usuario, **entonces** queda registrado como entregado |
| ACC-03 | Como administrador, quiero recuperar activos | **Dado** un activo asignado, **cuando** registro su devolución, **entonces** vuelve a estar disponible |
| ACC-04 | Como gestor de licencias, quiero controlar asientos | **Dado** una licencia con N asientos, **cuando** asigno asientos, **entonces** la disponibilidad se actualiza y no permite exceder N |
| ACC-05 | Como almacenero, quiero controlar consumibles | **Dado** un consumible con stock, **cuando** lo entrego, **entonces** el stock disminuye y se bloquea al agotarse |
| ACC-06 | Como responsable de seguridad, quiero control de acceso | **Dado** un usuario sin permisos, **cuando** intenta una acción restringida, **entonces** el sistema la deniega |
| ACC-07 | Como administrador multiempresa, quiero aislamiento de datos | **Dado** FMCS activo, **cuando** un usuario navega, **entonces** solo ve entidades de su empresa |

---

## 4. Registro de aceptación

| ID | Criterio | Veredicto | Evidencia | Observación |
|----|----------|-----------|-----------|-------------|
| ACC-01 | Activo registrado | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |
| ACC-02 | Activo asignado | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |
| ACC-03 | Activo recuperado | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |
| ACC-04 | Control de asientos | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |
| ACC-05 | Control de stock | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |
| ACC-06 | Control de acceso | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |
| ACC-07 | Aislamiento multiempresa | `⟦PENDIENTE-UAT⟧` | `⟦captura⟧` | — |

> UAT = *User Acceptance Testing*. Los veredictos se completan durante la sesión de aceptación del Hito 3.

---

## 5. Criterios de entrada y salida

### Entrada
- [ ] Pruebas de sistema del Hito 3 superadas.
- [ ] Entorno de staging estable con datos de demostración.
- [ ] Historias de usuario y criterios de aceptación acordados.

### Salida (criterio de aceptación del producto)
- [ ] 100 % de los criterios ACC-01 a ACC-07 evaluados.
- [ ] Todos los criterios de severidad alta en estado "Aceptado".
- [ ] Defectos de aceptación registrados y priorizados en GitHub Issues.
- [ ] Acta de aceptación documentada en la Wiki.

---

## 6. Trazabilidad

Cada criterio ACC-XX se vincula con su requisito (RF-XX) y con los niveles inferiores en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad).

---

*Fin del documento — Plan de Pruebas de Aceptación (Hito 3).*
