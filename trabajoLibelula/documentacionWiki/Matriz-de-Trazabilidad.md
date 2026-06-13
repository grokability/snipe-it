# Matriz de Trazabilidad

> Conforme a ISO/IEC/IEEE 29119. Vincula **requisito → módulo → caso de prueba → nivel → evidencia → resultado**, garantizando cobertura bidireccional.

| Campo | Detalle |
|-------|---------|
| **Documento** | Matriz de Trazabilidad General — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Fecha de elaboración** | 2026-06-12 |

---

## 1. Propósito

Asegurar que **cada requisito funcional** está cubierto por al menos un caso de prueba (trazabilidad directa) y que **cada caso de prueba** responde a un requisito (trazabilidad inversa), enlazando el diseño con su evidencia de ejecución.

---

## 2. Catálogo de requisitos funcionales

| ID Req. | Requisito | Subsistema |
|---------|-----------|------------|
| RF-01 | Registrar activo con asset tag único | Activos |
| RF-02 | Checkout de activo a usuario | Activos/Checkout |
| RF-03 | Checkin de activo | Activos/Checkout |
| RF-04 | Crear licencia con N asientos | Licencias |
| RF-05 | Asignar asiento de licencia | Licencias |
| RF-06 | Descontar stock de consumible | Inventario |
| RF-07 | Impedir eliminación de categoría con items | Categorías |
| RF-08 | Reflejar disponibilidad según status label | Activos |

---

## 3. Matriz requisito → caso de prueba → nivel

| Req. | Módulo | Caso unitario (evidencia real) | Caso funcional | Caso integración | Resultado |
|------|--------|-------------------------------|----------------|------------------|-----------|
| RF-01 | Asset | `AssetTest` (auto asset tag) | CPF-01, CPF-02 (+ subcasos) | INT-01 | `⟦CI/QA⟧` |
| RF-02 | Asset/User | `AssetTest` (deployable) | CPF-03 (+ subcasos .1–.7) | INT-01 | `⟦CI/QA⟧` |
| RF-03 | Asset | — | CPF-04 (+ subcasos .1–.3) | INT-02 | `⟦QA⟧` |
| RF-04 | License | `Models/LicenseTest` (seats) | CPF-06 (+ subcasos .1–.5) | INT-04 | `⟦CI/QA⟧` |
| RF-05 | LicenseSeat | `Models/LicenseTest` (percentRemaining) | CPF-07 (+ .1), **CPF-08 (manual)** | INT-04 | `⟦CI/QA⟧` ⁽¹⁾ |
| RF-06 | Consumable | `ConsumableTest` (percentRemaining) | CPF-09 (+ subcasos .1–.5) | INT-05 | `⟦CI/QA⟧` |
| RF-07 | Category | `Category_AddedTest` (`is_deletable`, `item_count`) | CPF-10 (+ .1), CPF-11 | — | `⟦CI/QA⟧` |
| RF-08 | Statuslabel/Asset | `StatuslabelTest` (altas) · brecha `getStatuslabelType()` | **CPF-05 (manual, + .1–.3)** | INT-01.2 | `⟦CI/QA⟧` ⁽¹⁾ |

> Leyenda de resultado: `⟦CI⟧` se completa con el artefacto de CI (unitarias/integración); `⟦QA⟧` con la ejecución manual funcional.
>
> ⁽¹⁾ **CPF-05** (disponibilidad por *status label* no desplegable) y **CPF-08** (agotamiento de asientos de licencia) **no cuentan con prueba funcional automatizada** en el repositorio: su verificación es **manual exclusiva** y queda pendiente de validación en QA. Esto se documenta en el [Informe de Casos de Pruebas Funcionales](Informe-de-Casos-de-Pruebas-Funcionales) §4 (RF-05, RF-08). El indicador `⟦CI⟧` de esas filas corresponde únicamente a la cobertura **unitaria** del módulo, no al comportamiento funcional específico.
>
> **Nota sobre subcasos:** el [Diseño de Casos de Pruebas Funcionales](Diseno-de-Casos-de-Pruebas-Funcionales) v2.0 desglosa los 11 casos principales en **30 subcasos** `CPF-XX.n` (positivos, negativos y de valores límite). Esta matriz traza el caso principal; el detalle de subcasos y su evidencia se mantiene en el informe funcional.

---

## 4. Matriz módulo → cobertura unitaria existente (verificada)

| Módulo | Archivo de prueba | # Tests | Brecha priorizada |
|--------|-------------------|---------|-------------------|
| Asset | `AssetTest.php` | 20 | `availableForCheckout`, `assignedType` |
| AssetModel | `AssetModelTest.php` | 4 | `isDeletable`, scopes, hooks |
| User | `UserTest.php` | 25 | `isManagerOf`, `hasAccess`, `isDeletable` |
| License+Seat | `Models/LicenseTest.php` | 7 | `isExpired`, `adjustSeatCount`, scopes |
| Accessory | `AccessoryTest.php` | 7 | `isDeletable`, `totalCostSum` |
| Component | `ComponentTest.php` | 8 | `calculatedPurchaseCost`, hooks |
| Consumable | `ConsumableTest.php` | 3 | `numCheckedOut`, `isDeletable`, `getImageUrl` |
| Category | `CategoryTest.php` + `Category_AddedTest.php` | 17 | — (cubierto) |
| Company | `CompanyScopingTest.php` + `Models/Company/*` | 8 | `isDeletable` |
| Statuslabel | `StatuslabelTest.php` | 6 | `getStatuslabelType`, `getStatuslabelTypesForDB` |
| Depreciable | `DepreciableTest.php` | 30 | — (cubierto) |
| Checkout | `Models/CheckoutRequestTest.php` | 6 | `CheckoutAcceptance::isPending` |
| CustomField | `CustomFieldTest.php` | 9 | — |
| SnipeModel | `SnipeModelTest.php` | 9 | — |

---

## 5. Cobertura de requisitos (resumen)

| Indicador | Valor |
|-----------|-------|
| Requisitos definidos (alcance académico) | 8 (RF-01 a RF-08), sobre los **5 subsistemas núcleo** delimitados en [Hito 1](Hito-1-Presentacion-del-Producto) §4 |
| Requisitos con al menos un caso diseñado | 8 (100 % del alcance definido) |
| Requisitos con caso unitario asociado | 7 de 8 (RF-03 solo funcional/integración) |
| Requisitos con caso funcional | 8 (100 % del alcance definido) |
| Casos funcionales principales / subcasos | 11 / 30 (ver diseño funcional v2.0) |
| Casos funcionales con cobertura automatizada de referencia | 9 de 11 (CPF-05 y CPF-08 son verificación manual exclusiva) |

> **Aclaración de alcance:** "100 %" se refiere a la cobertura de los **8 requisitos delimitados** para el proceso de pruebas académico, **no** a la totalidad funcional de Snipe-IT (que comprende ~20 subsistemas). La delimitación es deliberada y está justificada en [Hito 1](Hito-1-Presentacion-del-Producto) §2 y §4.

---

## 6. Trazabilidad documental

| Nivel | Diseño | Resultado |
|-------|--------|-----------|
| Unitario | [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias) | [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias) |
| Funcional | [Diseño de Casos Funcionales](Diseno-de-Casos-de-Pruebas-Funcionales) | [Informe de Casos Funcionales](Informe-de-Casos-de-Pruebas-Funcionales) |
| Integración | [Plan de Integración](Plan-de-Pruebas-de-Integracion) | (Hito 3) |
| Sistema/Aceptación | [Sistema](Plan-de-Pruebas-de-Sistema) / [Aceptación](Plan-de-Pruebas-de-Aceptacion) | (Hito 3) |

---

*Fin del documento — Matriz de Trazabilidad.*
