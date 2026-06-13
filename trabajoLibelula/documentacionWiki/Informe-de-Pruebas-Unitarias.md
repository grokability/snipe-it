# Informe de Pruebas Unitarias

> Conforme a ISO/IEC/IEEE 29119-3 (Test Completion Report). Consolida la **ejecución** del [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias).

| Campo | Detalle |
|-------|---------|
| **Documento** | Informe de Pruebas Unitarias — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Plan asociado** | [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias) v3.0 |
| **Fecha de elaboración** | 2026-06-12 |
| **Estado** | Inventario verificado; **resultados de ejecución pendientes de artefacto de CI** |

---

## 1. Nota metodológica sobre la veracidad de los datos

Este informe distingue dos tipos de datos:

1. **Inventario verificado:** número de archivos y métodos de prueba, medido directamente sobre el repositorio. **Es factual.**
2. **Resultados de ejecución** (pruebas en verde/rojo, tiempo, cobertura): requieren ejecutar PHPUnit con un driver de cobertura. Se marcan como `⟦PENDIENTE-CI⟧` y **deben completarse a partir del artefacto `clover.xml`/`junit.xml`** que produce el workflow `tests-unit-coverage.yml`. **No se consignan valores inventados.**

> Procedimiento para completar este informe: ejecutar el workflow *Unit Tests + Coverage* en GitHub Actions (push o `workflow_dispatch`), descargar el artefacto `coverage-php-8.x` y transcribir los totales de `junit.xml` (resultados) y `clover.xml` (cobertura) a las tablas de §3 y §4.

---

## 2. Resumen de la ejecución (inventario verificado)

| Métrica | Valor |
|---------|-------|
| Archivos de prueba unitaria | **45** |
| Métodos de prueba unitaria | **279** |
| Suite ejecutada | `Unit` (`./tests/Unit`) |
| Entorno | SQLite en memoria (`sqlite_testing`), PCOV |
| Pruebas en verde (PASS) | `⟦PENDIENTE-CI⟧` |
| Pruebas en rojo (FAIL) | `⟦PENDIENTE-CI⟧` |
| Pruebas omitidas (SKIP) | `⟦PENDIENTE-CI⟧` |
| Tiempo total de ejecución | `⟦PENDIENTE-CI⟧` |

---

## 3. Resultados por módulo (inventario + resultado de CI)

| Módulo | Archivo(s) | # Tests | PASS | FAIL | Cobertura líneas (modelo) |
|--------|-----------|---------|------|------|---------------------------|
| Asset | `AssetTest.php` | 20 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| AssetModel | `AssetModelTest.php` | 4 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| User | `UserTest.php` | 25 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| License | `Models/LicenseTest.php` | 7 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Accessory | `AccessoryTest.php` | 7 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Component | `ComponentTest.php` | 8 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Consumable | `ConsumableTest.php` | 3 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Category | `CategoryTest.php` + `Category_AddedTest.php` | 17 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Company | `CompanyScopingTest.php` + `Models/Company/*` | 8 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Statuslabel | `StatuslabelTest.php` | 6 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Depreciable | `DepreciableTest.php` | 30 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| Checkout | `Models/CheckoutRequestTest.php` | 6 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| CustomField | `CustomFieldTest.php` | 9 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |
| SnipeModel | `SnipeModelTest.php` | 9 | `⟦CI⟧` | `⟦CI⟧` | `⟦CI⟧` |

---

## 4. Cobertura

| Indicador | Valor objetivo | Valor real |
|-----------|----------------|------------|
| Cobertura de líneas — modelos en alcance | ≥ 80 % | `⟦PENDIENTE-CI⟧` |
| Cobertura global `app/` (informativo) | sin objetivo | `⟦PENDIENTE-CI⟧` |
| Artefacto de evidencia | — | `clover.xml` / `html` (CI) |

El detalle y la interpretación se amplían en [Cobertura y Estado Real del Proyecto](Cobertura-y-Estado-del-Proyecto).

---

## 5. Casos destacados verificados por inspección

Estos casos **ya existen** en el repositorio y reflejan buenas prácticas de diseño (evidencia: nombres reales de los métodos de prueba):

- **Depreciable** (`DepreciableTest.php`, 30 métodos): cubre depreciación lineal a 0 %, 50 % y 100 %, método de medio año (`half_year`), año fiscal, *clamp* a 100 %, y casos sin `purchase_date`. Cobertura matemática exhaustiva.
- **Category** (`Category_AddedTest.php`, 15 métodos): valida tipos de categoría, unicidad de nombre por tipo, `getEula()` (propia, fallback global, nula), `itemCount()` por tipo y `isDeletable()` con/sin permisos.
- **Consumable** (`ConsumableTest.php`): incluye el caso límite `percent_remaining_can_go_negative_when_checked_out_exceeds_quantity`, evidencia de prueba de valores fuera de rango.

---

## 6. Defectos detectados

Los defectos se registran en **GitHub Issues** con etiqueta `bug` y se enlazan aquí.

| ID Issue | Módulo | Descripción | Severidad | Estado |
|----------|--------|-------------|-----------|--------|
| `⟦PENDIENTE⟧` | Accessory/Consumable | Inconsistencia documentada en `percentRemaining()` ante `qty=0, checkouts=0` (deuda técnica) | Baja | Por registrar |

> Deuda técnica conocida: `Accessory::percentRemaining()` evalúa primero `qty==0` (retorna 0); `Consumable::percentRemaining()` evalúa primero los checkouts (retorna 100). Comportamientos divergentes ante el mismo borde. Debe registrarse como Issue y cubrirse con prueba explícita.

---

## 7. Conclusión del informe

El repositorio cuenta con una base sólida de **279 pruebas unitarias** sobre la capa de modelos, con módulos especialmente bien cubiertos (Depreciable, Category, Asset, User). Las brechas reales priorizadas son **AssetModel, License/LicenseSeat, Consumable** y los métodos `getStatuslabelType()`/`getStatuslabelTypesForDB()` de Statuslabel.

El cierre formal de este informe requiere transcribir los resultados de ejecución y cobertura del artefacto de CI (§1). Una vez completados los campos `⟦PENDIENTE-CI⟧`, el informe satisface los criterios de salida del [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias).

---

*Fin del documento — Informe de Pruebas Unitarias.*
