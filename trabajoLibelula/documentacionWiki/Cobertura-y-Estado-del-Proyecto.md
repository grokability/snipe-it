# Cobertura y Estado Real del Proyecto

> Resumen factual del estado de las pruebas, con separación estricta entre **datos verificados** y **valores pendientes de ejecución en CI**.

| Campo | Detalle |
|-------|---------|
| **Documento** | Cobertura y Estado Real del Proyecto — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Fecha de elaboración** | 2026-06-12 |

---

## 1. Estado verificado del repositorio (factual)

Valores medidos directamente sobre el árbol de código (no estimados):

| Atributo | Valor |
|----------|-------|
| Licencia | AGPL-3.0-or-later |
| PHP / Framework | 8.2+ / Laravel 12 |
| PHPUnit | `^11.0` |
| Modelos Eloquent | 41 |
| Controladores | 91 (61 web + 30 API) |
| Policies | 22 |
| Factories | 29 |
| Migraciones | 444 |
| **Pruebas unitarias** | **45 archivos / 279 métodos** |
| **Pruebas de integración (Feature)** | **292 archivos / 1624 métodos** |
| Workflows CI/CD | 11 |

---

## 2. Situación de la medición de cobertura

### 2.1 Hecho técnico verificado
El archivo `phpunit.xml` define el ámbito de cobertura como **todo `app/`**:
```xml
<source><include><directory suffix=".php">app/</directory></include></source>
```
En consecuencia, ejecutar **solo** la suite `Unit` y medir contra **todo `app/`** (que incluye 91 controladores, transformers y helpers no cubiertos por unitarias) produce un **porcentaje global bajo** que **no representa** la calidad de las pruebas unitarias de la capa de modelos.

### 2.2 Limitación del entorno local actual
Al momento de redactar, el entorno local **no permite medir la cobertura**: no hay binario de PHP en el PATH, las dependencias (`vendor/`) no están instaladas y no existe un reporte de cobertura previo en el repositorio. **Por integridad, no se publica ningún porcentaje estimado.**

### 2.3 Fuente oficial de cobertura
La cobertura real se obtiene del workflow **`tests-unit-coverage.yml`**, que genera `clover.xml` y un reporte HTML como artefactos. Ver [Pipeline CI/CD](Pipeline-CI-CD) §3.

---

## 3. Métricas oficiales y su estado

| Métrica | Definición | Objetivo | Valor real |
|---------|------------|----------|------------|
| Cobertura de modelos en alcance | Líneas cubiertas en `app/Models/` de los subsistemas núcleo | ≥ 80 % | `⟦PENDIENTE-CI⟧` |
| Cobertura global `app/` | Informativa, no es objetivo | — | `⟦PENDIENTE-CI⟧` |
| Pruebas unitarias en verde | PASS / total | 100 % | `⟦PENDIENTE-CI⟧` |
| Tiempo de ejecución (Unit) | Duración de la suite | < 60 s | `⟦PENDIENTE-CI⟧` |

> Procedimiento para completar: ejecutar el workflow de cobertura → descargar artefacto → transcribir el porcentaje de `app/Models/` (modelos en alcance) y los totales de `junit.xml`.

---

## 4. Estado por módulo (cobertura de pruebas, inventario verificado)

| Módulo | # Tests unitarios | Estado de cobertura unitaria | Acción Hito 2 |
|--------|-------------------|------------------------------|----------------|
| Depreciable | 30 | ✅ Alta | Documentar |
| User | 25 | 🟢 Buena | Ampliar brechas |
| Asset | 20 | 🟢 Buena | Ampliar brechas |
| Category | 17 | ✅ Alta | Documentar |
| CustomField | 9 | 🟢 Buena | — |
| SnipeModel | 9 | 🟢 Buena | — |
| Company | 8 | 🟢 Buena | Ampliar `isDeletable` |
| Component | 8 | 🟢 Buena | Casos borde |
| Accessory | 7 | 🟢 Buena | Casos borde |
| License + Seat | 7 | 🟡 Media | **Ampliar (prioridad)** |
| Statuslabel | 6 | 🟡 Media | **Cubrir `getStatuslabelType()`** |
| Checkout | 6 | 🟡 Media | Ampliar aceptación |
| AssetModel | 4 | 🔴 Baja | **Ampliar (prioridad)** |
| Consumable | 3 | 🔴 Baja | **Ampliar (prioridad)** |

---

## 5. Diferencias corregidas respecto al plan v2.0

| Tema | Plan v2.0 | Estado real | Corregido en |
|------|-----------|-------------|--------------|
| Versión PHPUnit | 10.5 | ^11.0 | Plan Unitarias v3.0 |
| Consumable | 0 tests | 3 tests | Este documento / Plan v3.0 |
| Depreciable | 0 (opcional) | 30 tests | Este documento / Plan v3.0 |
| Category | 2 tests | 17 tests | Este documento / Plan v3.0 |
| Statuslabel | `getStatuslabelType` cubierto | Solo altas cubiertas | Plan v3.0 §5.4 |
| License | `isExpired`/scopes cubiertos | seats/percent/depreciación | Plan v3.0 §5.2 |
| Cobertura objetivo | 81–85 % global | Métrica acotada a modelos | Plan v3.0 §6 |

---

## 6. Conclusión del estado

El proyecto presenta una **base de pruebas robusta y verificable** (279 unitarias + 1624 de integración) y un **pipeline de CI/CD operativo con cobertura automatizada**. El trabajo pendiente del Hito 2 es: (1) cerrar las brechas unitarias prioritarias (AssetModel, Consumable, License, Statuslabel); (2) ejecutar el workflow de cobertura y transcribir los valores reales a los campos `⟦PENDIENTE-CI⟧`; (3) ejecutar las pruebas funcionales manuales en QA.

---

*Fin del documento — Cobertura y Estado Real del Proyecto.*
