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
| RF-01 | Asset | `AssetTest` (auto asset tag) | CPF-01, CPF-02 | INT-01 | `⟦CI/QA⟧` |
| RF-02 | Asset/User | `AssetTest` (deployable) | CPF-03 | INT-01 | `⟦CI/QA⟧` |
| RF-03 | Asset | — | CPF-04 | INT-02 | `⟦QA⟧` |
| RF-04 | License | `Models/LicenseTest` (seats) | CPF-06 | INT-04 | `⟦CI/QA⟧` |
| RF-05 | LicenseSeat | `Models/LicenseTest` (percentRemaining) | CPF-07, CPF-08 | INT-04 | `⟦CI/QA⟧` |
| RF-06 | Consumable | `ConsumableTest` (percentRemaining) | CPF-09 | INT-05 | `⟦CI/QA⟧` |
| RF-07 | Category | `Category_AddedTest` (`is_deletable`, `item_count`) | CPF-10, CPF-11 | — | `⟦CI/QA⟧` |
| RF-08 | Statuslabel/Asset | `StatuslabelTest` (altas) · brecha `getStatuslabelType()` | CPF-05 | INT-01.2 | `⟦CI/QA⟧` |

> Leyenda de resultado: `⟦CI⟧` se completa con el artefacto de CI (unitarias/integración); `⟦QA⟧` con la ejecución manual funcional.

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
| Requisitos definidos | 8 (RF-01 a RF-08) |
| Requisitos con al menos un caso diseñado | 8 (100 %) |
| Requisitos con caso unitario asociado | 7 de 8 (RF-03 solo funcional/integración) |
| Requisitos con caso funcional | 8 (100 %) |

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
