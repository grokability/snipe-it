# Informe de Casos de Pruebas Funcionales

> Conforme a ISO/IEC/IEEE 29119-3 (Test Execution / Completion). Registra la **ejecución manual** del [Diseño de Casos de Pruebas Funcionales](Diseno-de-Casos-de-Pruebas-Funcionales).

| Campo | Detalle |
|-------|---------|
| **Documento** | Informe de Casos de Pruebas Funcionales — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Tipo** | Funcional / Caja negra / Manual |
| **Ambiente** | QA (despliegue compartido) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estado** | Plantilla de registro lista; **ejecución manual a cargo del equipo en QA** |

---

## 1. Nota metodológica

La ejecución de estos casos es **manual** y debe realizarse en un **ambiente de QA único** desplegado para el equipo. Los resultados (Conforme/No conforme) y las evidencias (capturas) se registran en §3 durante la sesión de pruebas. Mientras la ejecución no se realice, las celdas de resultado figuran como `⟦PENDIENTE-QA⟧`. **No se consignan resultados no ejecutados como si hubieran pasado.**

> Procedimiento: desplegar Snipe-IT en QA (Docker Compose disponible en el repositorio), poblar datos base, ejecutar cada caso CPF-XX del diseño, adjuntar evidencia y marcar el veredicto.

---

## 2. Resumen de ejecución

| Métrica | Valor |
|---------|-------|
| Casos diseñados | 11 (CPF-01 a CPF-11) |
| Casos ejecutados | `⟦PENDIENTE-QA⟧` |
| Conformes | `⟦PENDIENTE-QA⟧` |
| No conformes | `⟦PENDIENTE-QA⟧` |
| Bloqueados | `⟦PENDIENTE-QA⟧` |
| Defectos registrados (Issues) | `⟦PENDIENTE-QA⟧` |

---

## 3. Registro de ejecución por caso

| Caso | Requisito | Técnica | Resultado esperado (resumen) | Veredicto | Evidencia | Issue si falla |
|------|-----------|---------|------------------------------|-----------|-----------|----------------|
| CPF-01 | RF-01 | PE/AVL | Activo creado con tag único | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-02 | RF-01 | PE inválida | Rechazo de tag duplicado | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-03 | RF-02 | TE | Activo asignado a usuario | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-04 | RF-03 | TE | Activo devuelto y disponible | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-05 | RF-08 | TD | Checkout impedido si no deployable | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-06 | RF-04 | AVL | Licencia con 5 asientos | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-07 | RF-05 | TE | Asiento asignado, disponibles −1 | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-08 | RF-05 | AVL | Rechazo al exceder asientos | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-09 | RF-06 | AVL | Stock de consumible decrementa | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-10 | RF-07 | TD | Categoría con items no se elimina | `⟦QA⟧` | `⟦captura⟧` | — |
| CPF-11 | RF-07 | TD | Categoría vacía se elimina | `⟦QA⟧` | `⟦captura⟧` | — |

---

## 4. Defectos funcionales encontrados

| ID Issue | Caso origen | Descripción | Severidad | Estado |
|----------|-------------|-------------|-----------|--------|
| `⟦PENDIENTE-QA⟧` | — | — | — | — |

Los defectos se registran en GitHub Issues con etiqueta `bug` y se enlazan en esta tabla.

---

## 5. Conclusión

Una vez ejecutados los 11 casos en el ambiente de QA y completadas las columnas de veredicto y evidencia, este informe cierra la actividad de pruebas funcionales del Hito 2 y alimenta la [Matriz de Trazabilidad](Matriz-de-Trazabilidad). El diseño cubre los requisitos RF-01 a RF-08 mediante partición de equivalencia, valores límite, tablas de decisión y transición de estados.

---

*Fin del documento — Informe de Casos de Pruebas Funcionales.*
