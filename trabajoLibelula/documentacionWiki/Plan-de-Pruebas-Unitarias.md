# Plan de Pruebas Unitarias

> Conforme a ISO/IEC/IEEE 29119-3 (Documentación de Pruebas). Documento **focalizado en pruebas unitarias** de la capa de modelos de dominio.

| Campo | Detalle |
|-------|---------|
| **Documento** | Plan de Pruebas Unitarias — Snipe-IT |
| **Versión** | **3.0 (reescritura verificada contra el repositorio)** |
| **Reemplaza a** | `trabajoLibelula/plan/planDePruebasUnitarias.md` v2.0 (2026-06-08) |
| **Repositorio** | `jhuamaniCond/snipe-it` |
| **Lenguaje / Framework** | PHP 8.2+ / Laravel 12 |
| **Herramienta de pruebas** | PHPUnit `^11.0` |
| **Driver de cobertura** | PCOV (CI) / Xdebug (local opcional) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estado** | Vigente |

---

## 1. Aviso de reescritura y correcciones respecto a la versión 2.0

Este plan **sustituye desde cero** al borrador v2.0. Durante la auditoría del repositorio real se detectaron afirmaciones del v2.0 que **no coinciden con el código** y que aquí quedan corregidas:

| Afirmación del Plan v2.0 | Realidad verificada en el repositorio | Corrección aplicada |
|--------------------------|----------------------------------------|---------------------|
| "PHPUnit 10.5" | `composer.json` declara **PHPUnit `^11.0`** | Se documenta 11.0 |
| Consumable: "0 tests — crear" | `tests/Unit/ConsumableTest.php` existe con **3 métodos** | Se documenta como iniciado |
| Depreciable: "0 tests — opcional" | `tests/Unit/DepreciableTest.php` con **30 métodos** | Se documenta como cubierto |
| Category: "2 tests, ampliar a 13" | `CategoryTest.php` (2) + `Category_AddedTest.php` (15) = **17** | Objetivo ya superado |
| Company: "6 tests" | `CompanyScopingTest.php` (3) + `Models/Company/*` (5) | Conteo corregido |
| Statuslabel: casos de `getStatuslabelType()` | El test real cubre **alta de status labels** (`test_*_statuslabel_add`), no `getStatuslabelType()` | Se redefine la brecha real |
| License: `isExpired/isTerminated/scopes` | `Models/LicenseTest.php` cubre **logging de seats, `percentRemaining`, depreciación** | Se redefine la brecha real |
| Cobertura objetivo "81–85 %" sobre el plan | `phpunit.xml` mide cobertura sobre **todo `app/`** → meta inalcanzable con solo unitarias | Se redefine la estrategia de medición (§6) |

**Regla de oro:** ante cualquier divergencia entre el plan anterior y el código, prevalece el código.

---

## 2. Introducción y objetivos

Este documento define la estrategia, el alcance y la planificación de las **pruebas unitarias** del backend de Snipe-IT, enfocadas en la **capa de modelos de dominio** (`app/Models/`), donde reside la lógica de negocio aislable: cálculos, reglas de eliminación (`isDeletable`), disponibilidad de stock, depreciación, scopes de consulta y mutadores.

**Objetivos:**
1. Documentar la cobertura unitaria **existente y verificada** del repositorio.
2. Identificar las **brechas reales** de cobertura por módulo en alcance.
3. Establecer criterios de entrada/salida y una métrica de cobertura **alcanzable y honesta**.
4. Servir de base trazable para el [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias).

**Fuera del alcance unitario:** controladores, transformers, vistas Blade, integraciones externas (LDAP, SAML/SCIM) y flujos multimodelo de checkout — estos corresponden a pruebas de integración, sistema y aceptación.

---

## 3. Alcance: inventario unitario verificado

Conteo de métodos de prueba medido directamente sobre `tests/Unit/`. **Total verificado: 45 archivos / 279 métodos.**

### 3.1 Módulos en alcance y su estado real

| # | Módulo | Modelo (LOC) | Archivo(s) de prueba unitaria | # Tests reales | Estado |
|---|--------|--------------|-------------------------------|----------------|--------|
| 1 | **Asset** | `Asset.php` (2 183) | `AssetTest.php` | 20 | Cubierto — ampliar brechas |
| 2 | **AssetModel** | `AssetModel.php` (387) | `AssetModelTest.php` | 4 | **Brecha — ampliar** |
| 3 | **User** | `User.php` (1 503) | `UserTest.php` | 25 | Cubierto — ampliar brechas |
| 4 | **License + LicenseSeat** | `License.php` (958) + `LicenseSeat.php` | `Models/LicenseTest.php` | 7 | **Brecha — ampliar** |
| 5 | **Accessory** | `Accessory.php` (527) | `AccessoryTest.php` | 7 | Cubierto — casos borde |
| 6 | **Component** | `Component.php` (483) | `ComponentTest.php` | 8 | Cubierto — casos borde |
| 7 | **Consumable** | `Consumable.php` (498) | `ConsumableTest.php` | 3 | **Brecha — ampliar** |
| 8 | **Category** | `Category.php` (346) | `CategoryTest.php` + `Category_AddedTest.php` | 2 + 15 = 17 | ✅ Objetivo superado |
| 9 | **Company** | `Company.php` (372) | `CompanyScopingTest.php` + `Models/Company/*` | 3 + 5 = 8 | Cubierto |
| 10 | **Statuslabel** | `Statuslabel.php` (196) | `StatuslabelTest.php` | 6 | Cubierto (altas) — ver §5 |
| 11 | **Depreciable** | `Depreciable.php` (244) | `DepreciableTest.php` | 30 | ✅ Objetivo superado |
| 12 | **Checkout** | `CheckoutRequest.php` | `Models/CheckoutRequestTest.php` | 6 | Cubierto |
| 13 | **CustomField** | `CustomField.php` | `CustomFieldTest.php` | 9 | Cubierto |
| 14 | **SnipeModel** | `SnipeModel.php` | `SnipeModelTest.php` | 9 | Cubierto |

### 3.2 Módulos fuera del alcance unitario — justificación

| Módulo | Razón de exclusión |
|--------|--------------------|
| `Department`, `Location`, `Manufacturer`, `Supplier` | Relaciones simples; cubiertos por Feature Tests |
| `Ldap`, `SAML/SCIM` | Integraciones externas; requieren entorno específico (Hito 3) |
| `Setting`, `ActionLog` | Infraestructura; `Setting` es singleton difícil de aislar |
| Flujos checkout/checkin completos | Multimodelo → pruebas de integración (Hito 2/3) |
| Controladores, Transformers, Notifications | No son lógica unitaria de modelo |

---

## 4. Configuración del entorno de pruebas (verificada)

### 4.1 Herramientas
```json
"require-dev": {
  "phpunit/phpunit": "^11.0",
  "php-mock/php-mock-phpunit": "^2.10"
}
```

### 4.2 phpunit.xml (real, resumen)
- Suites: `Unit` (`./tests/Unit`) y `Feature` (`./tests/Feature`).
- Entorno: `APP_ENV=testing`, `CACHE_DRIVER=array`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`, `QUEUE_DRIVER=sync`.
- Cobertura: `<source><include><directory>app/</directory>` → **mide toda la aplicación** (relevante para §6).

### 4.3 Base de datos de pruebas
Conexión `sqlite_testing` (SQLite en memoria, `:memory:`), definida en `config/database.php`. Requiere `.env.testing` (copia de `.env.testing.example`).

### 4.4 Soporte de pruebas disponible (`tests/Support/`)
`InitializesSettings.php`, `Settings.php`, `CanSkipTests.php`, `InteractsWithAuthentication.php`, `ProvidesDataForFullMultipleCompanySupportTesting.php`, `CustomTestMacros.php`. Estos *traits* deben reutilizarse en los tests nuevos para resolver dependencias de `Setting::getSettings()` y FMCS.

### 4.5 Ejecución
```bash
# Suite unitaria completa
php artisan test --testsuite=Unit
# Un módulo
php artisan test tests/Unit/ConsumableTest.php
# Con cobertura (Herd / PCOV)
herd coverage vendor/bin/phpunit --testsuite Unit --coverage-clover tests/coverage/clover.xml
```

---

## 5. Brechas reales identificadas y casos a diseñar

> Esta sección **no contiene código de pruebas** (se diseñará e implementará en la fase de ejecución del Hito 2). Define las brechas verificadas por inspección del código de los modelos y de los tests existentes.

### 5.1 AssetModel — `AssetModel.php` (4 tests actuales)
Cubierto: relación `assets`, `percentRemaining()` (varios ratios). Brechas: `isDeletable()` con/sin assets; scopes `scopeInCategory()` y `scopeRequestableModels()`; hooks `booted()` de borrado en cascada de requests.

### 5.2 License + LicenseSeat — `Models/LicenseTest.php` (7 tests actuales)
Cubierto: logging de alta/baja de seats, `percentRemaining()`, progreso de depreciación. Brechas: `isExpired()`, `isTerminated()`, `isInactive()`, `remaincount()`, `adjustSeatCount()` (alta y baja), `scopeActiveLicenses()`, `scopeExpiringLicenses()`; `LicenseSeat::location()` (vía usuario, vía asset, sin asignación).

### 5.3 Consumable — `ConsumableTest.php` (3 tests actuales)
Cubierto: `percentRemaining()` (100 % sin checkouts, parcial, negativo). Brechas: `numCheckedOut()` (con y sin eager loading), `numRemaining()`, `isDeletable()`, `totalCostSum()` (null y normal), `setQtyAttribute()`, `getImageUrl()` (propia, fallback de categoría, sin imagen).

### 5.4 Statuslabel — `StatuslabelTest.php` (6 tests actuales)
Cubierto: alta de cada tipo de status label (rtd, pending, archived, out_for_repair, broken, lost). **Brecha real (corrige al v2.0):** `getStatuslabelType()` y `getStatuslabelTypesForDB()` no están cubiertos por pruebas unitarias y deben diseñarse mediante tabla de decisión.

### 5.5 Asset — `AssetTest.php` (20 tests actuales)
Cubierto: auto-incremento de asset tag, garantía/EOL, depreciación, URL de imagen, estado deployable, costos. Brechas candidatas: `availableForCheckout()`, `assignedType()`, hooks `booted()` de cascada en soft/force delete.

### 5.6 User — `UserTest.php` (25 tests actuales)
Cubierto: generación de username/email en múltiples formatos. Brechas: `getFullNameAttribute()` (formatos), `isSuperUser()`, `hasAccess()`, `isManagerOf()` (directo/indirecto/sí mismo), `getAllSubordinates()`, `isDeletable()`.

> **Dependencia conocida:** `getFullNameAttribute()` y `preferredLocale()` dependen de `Setting::getSettings()`; usar el trait `InitializesSettings`.

---

## 6. Estrategia de medición de cobertura (corrección clave)

El `phpunit.xml` mide cobertura sobre **todo `app/`** (91 controladores, transformers, helpers, etc.). Ejecutar **solo** la suite unitaria contra todo `app/` produce un porcentaje global bajo que **no representa** la calidad de las pruebas unitarias de modelos. Por ello se define:

**Métrica oficial del proceso unitario:** cobertura de líneas **acotada a los modelos en alcance** (`app/Models/` de los subsistemas núcleo), obtenida del artefacto `clover.xml` del workflow `tests-unit-coverage.yml`.

- La cobertura **global de `app/`** se reporta por separado como dato informativo, no como objetivo.
- Los valores numéricos reales se consignan en [Cobertura y Estado Real del Proyecto](Cobertura-y-Estado-del-Proyecto) y en el [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias), tomados del artefacto de CI. **No se publican estimaciones como reales.**

**Meta de cobertura por módulo en alcance:** ≥ 80 % de líneas en los modelos de los subsistemas núcleo.

---

## 7. Estrategia y técnicas de diseño

### 7.1 Patrón AAA (Arrange–Act–Assert)
Todo caso se estructura en preparación de datos, ejecución del método bajo prueba y verificación del resultado.

### 7.2 Técnicas de caja negra aplicadas

| Técnica | Aplicación representativa |
|---------|----------------------------|
| Partición de equivalencia | `percentRemaining()`: sin stock / parcial / completo |
| Análisis de valores límite | 0 %, 50 %, 100 % exactos; `qty = 0` |
| Tabla de decisión y transicion de estados| `getStatuslabelType()` (combinaciones pending/archived/deployable) |
| Manejo de nulos | `totalCostSum()` con `purchase_cost = null` |

### 7.3 Aislamiento de base de datos
- Lógica pura (matemática, mutadores): instanciación directa `new Model()` **sin** trait de BD.
- Lógica con persistencia: `RefreshDatabase` o `DatabaseTransactions` + factories.

---

## 8. Criterios de entrada y salida (ISO/IEC/IEEE 29119)

### 8.1 Criterios de entrada
- [ ] `.env.testing` configurado y conexión `sqlite_testing` operativa.
- [ ] `composer install` ejecutado (dependencias de prueba disponibles).
- [ ] Factories de los módulos en alcance verificadas (29 disponibles).
- [ ] Driver de cobertura (PCOV/Xdebug) disponible en el entorno de ejecución.

### 8.2 Criterios de salida
- [ ] 100 % de los casos definidos ejecutados.
- [ ] Cobertura ≥ 80 % en los modelos de los subsistemas núcleo (medida sobre `clover.xml`).
- [ ] Cero pruebas en estado FAIL al cierre del Sprint.
- [ ] Reporte de cobertura (Clover + HTML) archivado como artefacto de CI.
- [ ] Defectos registrados en GitHub Issues con etiqueta `bug`.
- [ ] Resultados documentados en el [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias).

### 8.3 Suspensión y reanudación
**Suspender** si > 20 % de pruebas fallan por causas de entorno (no por lógica), si el pipeline de CI no ejecuta, o si una factory crítica bloquea varios módulos. **Reanudar** cuando el bloqueo esté corregido y verificado en GitHub Issues.

---

## 9. Riesgos

| ID | Riesgo | Prob. | Impacto | Mitigación |
|----|--------|-------|---------|------------|
| R-01 | Métrica de cobertura mal interpretada (todo `app/`) | Alta | Alto | Métrica acotada a modelos (§6) |
| R-02 | Dependencia de `Setting::getSettings()` en métodos de `User` | Alta | Medio | Trait `InitializesSettings` |
| R-03 | Hooks `booted()` difíciles de aislar | Alta | Medio | `RefreshDatabase` + factories |
| R-04 | Entorno local sin PHP/cobertura (caso actual) | Media | Alto | Ejecutar en CI (`tests-unit-coverage.yml`) |
| R-05 | Conflictos de merge en paralelo | Media | Bajo | Un archivo de test por responsable; ramas feature |
| R-06 | Inconsistencia `percentRemaining()` Accessory vs Consumable | Baja | Bajo | Documentar como deuda técnica en Issues |

---

## 10. Responsabilidades

| Rol | Responsabilidades |
|-----|-------------------|
| QA Lead | Mantenimiento del plan, revisión final, módulos Asset/AssetModel |
| Tester 1 | User, Checkout |
| Tester 2 | License + LicenseSeat |
| Tester 3 | Accessory, Component |
| Tester / CI | Consumable, Category, GitHub Actions |
| Revisor / Docs | Company, Statuslabel, Wiki e informe |

> Los nombres concretos se asignan en GitHub Projects; este plan no fija identidades para evitar datos no verificados.

---

## 11. Trazabilidad

Cada brecha y caso definido en §5 se vincula a su requisito y evidencia en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad). Los resultados de ejecución se consolidan en el [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias).

---

*Fin del documento — Plan de Pruebas Unitarias v3.0.*
