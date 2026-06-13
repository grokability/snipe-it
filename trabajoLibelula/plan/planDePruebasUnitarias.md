## Conforme a ISO/IEC/IEEE 29119-3

| Campo | Detalle |
|-------|---------|
| **Documento** | Plan de Pruebas Unitarias — Snipe-IT |
| **Versión** | 2.0 |
| **Repositorio** | `jhuamaniCond/snipe-it` |
| **Lenguaje** | PHP 8.2+ / Framework: Laravel 12 |
| **Herramienta de Testing** | PHPUnit 10.5 |
| **Fecha de elaboración** | 2026-05-29 |
| **Última actualización** | 2026-06-08 |
| **Estado** | En revisión |

### Autores y responsabilidades
(Aquí colocan los roles xD)

| Integrante | Rol | Módulos asignados |
|------------|-----|-------------------|
| _Nombre 1_ | QA Lead / Redactor del plan | Asset, AssetModel |
| _Nombre 2_ | Tester | User, Checkout |
| _Nombre 3_ | Tester | License, LicenseSeat |
| _Nombre 4_ | Tester | Accessory, Component |
| _Nombre 5_ | Tester / CI-CD | Consumable, Category, GitHub Actions |
| _Nombre 6_ | Revisor / Documentación | Company, Statuslabel, Wiki |

### Historial de revisiones

| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2026-05-29 | _Nombre_ | Versión inicial — Sprint 1 |
| 2.0 | 2026-06-08 | _Nombre_ | Revisión completa: auditoría de los 41 modelos del repositorio, corrección de LOC reales, incorporación de brechas identificadas, conformidad con ISO/IEC/IEEE 29119-3 |

---

## 1. Introducción

Este documento define la estrategia y planificación de pruebas unitarias para los módulos del backend del sistema **Snipe-IT**, un sistema libre y de código abierto para la gestión de activos y licencias de TI.

El plan se basa en una **auditoría completa del repositorio** (`jhuamaniCond/snipe-it`), que cuenta con 41 modelos Eloquent y 43 archivos de pruebas unitarias existentes. El enfoque distingue entre módulos que ya cuentan con cobertura unitaria (documentar y ampliar brechas) y módulos que carecen de ella (crear tests nuevos).

---

## 2. Alcance del Plan

### 2.1 Contexto del repositorio

| Métrica | Valor |
|---------|-------|
| Modelos Eloquent | 41 archivos |
| Controladores (Web + API) | 91 archivos |
| Tests unitarios existentes | 43 archivos |
| Tests de integración (Feature) | ~296 archivos |
| Factories disponibles | 29 factories |
| Workflows de CI/CD | 10 GitHub Actions |

### 2.2 Módulos en alcance

La selección de módulos se basa en tres criterios:

1. **Criticidad de negocio**: módulos que implementan lógica de negocio central (cálculos, restricciones de integridad, permisos).
2. **Brecha de cobertura unitaria**: módulos que solo cuentan con Feature Tests o tienen cobertura insuficiente.
3. **Tamaño y complejidad**: los archivos de mayor tamaño concentran mayor riesgo.

| # | Módulo | Archivos principales | LOC total | Tests existentes | Estado |
|---|--------|---------------------|-----------|-----------------|--------|
| 1 | **Asset + AssetModel** | `Asset.php` + `AssetModel.php` | 2,184 + 387 | 20 + 4 | Documentar + ampliar |
| 2 | **User** | `User.php` | 1,504 | 25 | Documentar + ampliar brechas |
| 3 | **License + LicenseSeat** | `License.php` + `LicenseSeat.php` | 959 + 238 | 7 | Documentar + ampliar |
| 4 | **Accessory** | `Accessory.php` + `AccessoryCheckout.php` | 528 + 199 | 7 + 1 | Documentar + casos borde |
| 5 | **Component** | `Component.php` + `ComponentAssignment.php` | 484 + 80 | 8 | Documentar + ampliar |
| 6 | **Consumable** | `Consumable.php` + `ConsumableAssignment.php` | 499 + 35 | 0 de modelo | **Crear tests — prioridad alta** |
| 7 | **Category** | `Category.php` | 347 | 2 | Ampliar — brechas críticas |
| 8 | **Company** | `Company.php` | 373 | 6 | Documentar + ampliar |
| 9 | **Checkout** | `CheckoutAcceptance.php` + `CheckoutRequest.php` | 136 + 57 | 6 | Documentar cobertura existente |
| 10 | **Statuslabel** | `Statuslabel.php` | ~200 | existente | Documentar + ampliar `getStatuslabelType()` |
| 11 | **SnipeModel** | `SnipeModel.php` | ~150 | existente | Documentar cobertura existente |
| 12 | **Depreciable** | `Depreciable.php` | ~200 | 0 | Opcional — matemática pura, preparado para tests |
| 13 | **CustomField** | `CustomField.php` | ~300 | existente | Documentar cobertura existente |
| 14 | **ReportTemplate** | `ReportTemplate.php` | ~200 | existente | Documentar cobertura existente |

### 2.3 Módulos fuera del alcance — justificación

| Módulo | Razón de exclusión |
|--------|-------------------|
| `Department`, `Location` | Solo relaciones simples; Feature Tests suficientes |
| `Maintenance` | Mutadores básicos; cubierto por `MaintenanceTest` existente |
| `Manufacturer`, `Supplier` | Solo `isDeletable()` con relaciones; sin lógica de negocio aislable |
| `Ldap`, `SAML/SCIM` | Integraciones externas; requieren entorno específico (Hito 2/3) |
| `ActionLog`, `Setting` | Infraestructura; `Setting` tiene dependencia de singleton difícil de mockear |
| `Group`, `CustomFieldset` | Baja complejidad o dependencias de BD complejas |
| `MaintenanceType`, `PredefinedKit` | Funcionalidad secundaria sin lógica crítica aislable |
| `SamlNonce`, `SCIMUser`, `Import` | Infraestructura pura o wrappers sin lógica propia |
| `Checkout/Checkin flows` | Flujos multi-modelo; corresponden a pruebas de integración (Hito 2) |
| `Notifications`, `Reports`, `Search` | Dependencias de sistema completo (Hito 2/3) |

---

## 3. Configuración del Entorno de Testing

### 3.1 Herramientas requeridas

```json
{
  "require-dev": {
    "phpunit/phpunit": "^10.5"
  }
}
```

### 3.2 Configuración PHPUnit

**Archivo:** `phpunit.xml` (ya existe en el proyecto)

```xml
<phpunit>
  <testsuites>
    <testsuite name="Unit">
      <directory suffix="Test.php">./tests/Unit</directory>
    </testsuite>
  </testsuites>
  <source>
    <include>
      <directory suffix=".php">app/</directory>
    </include>
  </source>
</phpunit>
```

### 3.3 Estructura de directorios

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── LicenseTest.php          ← existente
│   │   └── CheckoutRequestTest.php  ← existente
│   ├── AssetTest.php                ← existente (20 tests)
│   ├── AssetModelTest.php           ← existente (4 tests)
│   ├── UserTest.php                 ← existente (25 tests)
│   ├── AccessoryTest.php            ← existente (7 tests)
│   ├── ComponentTest.php            ← existente (8 tests)
│   ├── ConsumableTest.php           ← CREAR (0 tests de modelo)
│   ├── CategoryTest.php             ← existente (2 tests — ampliar)
│   ├── CompanyScopingTest.php       ← existente (6 tests)
│   └── StatuslabelTest.php          ← existente
├── TestCase.php
└── CreatesApplication.php
```

### 3.4 Estrategia de aislamiento de base de datos

Todos los tests unitarios que requieran BD deben usar uno de estos traits:

```php
// Opción preferida para tests unitarios — rollback automático
use Illuminate\Foundation\Testing\DatabaseTransactions;

// Alternativa para tests que necesitan estado limpio
use Illuminate\Foundation\Testing\RefreshDatabase;
```

Para tests de lógica pura (cálculos matemáticos, mutadores, métodos sin BD), **no se debe usar ningún trait de BD** — se instancia el modelo directamente con `new Model()`.

### 3.5 Ejecución de tests

```bash
# Ejecutar todos los tests unitarios
php artisan test tests/Unit

# Ejecutar tests de un módulo específico
php artisan test tests/Unit/AssetTest.php

# Ejecutar con reporte de cobertura (requiere Xdebug o PCOV)
php artisan test --coverage tests/Unit

# Ejecutar y generar reporte HTML
php artisan test --coverage tests/Unit --coverage-html=coverage
```

---

## 4. Estrategia de Testing

### 4.1 Patrón AAA (Arrange-Act-Assert)

```php
public function test_percent_remaining_returns_zero_when_qty_is_zero()
{
    // Arrange
    $consumable = new Consumable(['qty' => 0]);
    $consumable->consumables_users_count = 0;

    // Act
    $result = $consumable->percentRemaining();

    // Assert
    $this->assertEquals(0, $result);
}
```

### 4.2 Técnicas de diseño de casos aplicadas

| Técnica | Descripción | Ejemplo de aplicación |
|---------|-------------|----------------------|
| **Partición de equivalencia** | Agrupar entradas con comportamiento equivalente | `percentRemaining()`: qty=0, qty>0 con checkouts, qty>0 sin checkouts |
| **Análisis de valores límite** | Probar en los bordes de los rangos | `percentRemaining()`: 0%, 50%, 100% exactos |
| **Tabla de decisión** | Cubrir combinaciones de condiciones | `getStatuslabelType()`: 4 combinaciones de pending/archived/deployable |
| **Cobertura de ramas** | Probar cada camino de un `if/else` | `isDeletable()`: con y sin items asignados |
| **Prueba de valores nulos** | Verificar manejo de null | `totalCostSum()`: `purchase_cost = null` |

### 4.3 Uso de factories

```php
// Crear un modelo con datos específicos
$license = License::factory()->create(['seats' => 10]);

// Crear con estado personalizado
$expiredLicense = License::factory()->expired()->create();

// Crear modelo sin persistir (para tests de lógica pura)
$consumable = Consumable::factory()->make(['qty' => 5]);
```

### 4.4 Convenciones de nomenclatura

```
test_{descripcion_en_snake_case}

Ejemplos:
- test_percent_remaining_returns_zero_when_qty_is_zero()
- test_is_deletable_returns_false_when_assets_are_assigned()
- test_get_statuslabel_type_returns_pending_for_correct_flags()
```

---

## 5. Módulos y Casos de Prueba

### 5.1 MÓDULO: Asset (`Asset.php` — 2,184 LOC)

**Tests existentes:** 20 tests en `AssetTest.php`

**Cobertura existente confirmada:**
- Auto-increment de asset tag (8 tests)
- Cálculo de garantía y EOL (3 tests)
- Depreciación (1 test)
- URL de imagen (1 test)
- Estado deployable (3 tests)
- Costos de accesorios y componentes (2 tests)

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **AST-021** | `booted()` — soft delete elimina checkout requests | Event hook | Al soft-delete un asset, sus `CheckoutRequest` se soft-delete en cascada |
| **AST-022** | `booted()` — force delete elimina checkout requests | Event hook | Al force-delete, se force-delete en cascada |
| **AST-023** | `availableForCheckout()` — status deployable | Business logic | Retorna true solo si el status label es deployable |
| **AST-024** | `assignedType()` con distintos tipos de asignación | Type safety | Retorna 'user', 'location' o 'asset' según tipo |

### 5.2 MÓDULO: AssetModel (`AssetModel.php` — 387 LOC)

**Tests existentes:** 4 tests en `AssetModelTest.php`

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **AM-005** | `booted()` — soft delete elimina requests | Event hook | Al soft-delete un AssetModel, sus requests se soft-delete |
| **AM-006** | `booted()` — force delete elimina requests | Event hook | Al force-delete, sus requests se force-delete |
| **AM-007** | `percentRemaining()` — sin assets disponibles | Business logic | Retorna 0 cuando `availableAssets()->count() == 0` |
| **AM-008** | `percentRemaining()` — cálculo normal | Business logic | `available / total * 100` |
| **AM-009** | `isDeletable()` — con assets asignados | Authorization | Retorna false si `assets_count > 0` |
| **AM-010** | `isDeletable()` — sin assets | Authorization | Retorna true si no hay assets |
| **AM-011** | `scopeInCategory()` — filtra por array de IDs | Query | Solo retorna modelos en las categorías indicadas |
| **AM-012** | `scopeRequestableModels()` — solo requestable=1 | Query | Filtra correctamente por campo requestable |

### 5.3 MÓDULO: User (`User.php` — 1,504 LOC)

**Tests existentes:** 25 tests en `UserTest.php` (principalmente generación de usernames/emails)

**Cobertura existente confirmada:** Generación de username con distintos formatos de nombre, variantes con email.

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **U-026** | `booted()` — soft delete revoca tokens Passport | Event hook | Al soft-delete, se revocan tokens OAuth |
| **U-027** | `booted()` — force delete purga tokens Passport | Event hook | Al force-delete, se eliminan tokens y refresh tokens |
| **U-028** | `getFullNameAttribute()` — formato first_last | Presentation | Retorna "Nombre Apellido" por defecto |
| **U-029** | `getFullNameAttribute()` — formato last_first | Presentation | Retorna "Apellido Nombre" según setting |
| **U-030** | `isSuperUser()` con permiso superuser | Authorization | Retorna true si tiene permiso superuser en JSON |
| **U-031** | `hasAccess()` — superuser tiene acceso a todo | Authorization | Retorna true para cualquier sección |
| **U-032** | `isManagerOf()` — manager directo | Business logic | Retorna true si es manager directo del usuario |
| **U-033** | `isManagerOf()` — manager indirecto (N niveles) | Business logic | Retorna true si es manager en cualquier nivel |
| **U-034** | `isManagerOf()` — mismo usuario | Business logic | Retorna false si se compara consigo mismo |
| **U-035** | `getAllSubordinates()` — sin subordinados | Business logic | Retorna colección vacía |
| **U-036** | `getAllSubordinates()` — jerarquía de 2 niveles | Business logic | Retorna todos los subordinados sin duplicados |
| **U-037** | `isDeletable()` — con assets asignados | Authorization | Retorna false si tiene items asignados |

### 5.4 MÓDULO: License + LicenseSeat (`License.php` 959 LOC + `LicenseSeat.php` 238 LOC)

**Tests existentes:** 7 tests en `tests/Unit/Models/LicenseTest.php`

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **L-008** | `isExpired()` — fecha pasada | Business logic | Retorna true si `expiration_date` < hoy |
| **L-009** | `isExpired()` — fecha futura | Business logic | Retorna false si `expiration_date` > hoy |
| **L-010** | `isTerminated()` — fecha pasada | Business logic | Retorna true si `termination_date` < hoy |
| **L-011** | `isInactive()` — expirada O terminada | Business logic | Retorna true si cualquiera de las dos condiciones |
| **L-012** | `remaincount()` — cálculo correcto | Business logic | `total - assigned - unreassignable` |
| **L-013** | `percentRemaining()` — cálculo normal | Business logic | `(available/total)*100` |
| **L-014** | `adjustSeatCount()` — aumentar seats | Data integrity | Al aumentar, se crean registros LicenseSeat nuevos |
| **L-015** | `adjustSeatCount()` — reducir seats | Data integrity | Al reducir, se eliminan seats no asignados |
| **L-016** | `scopeActiveLicenses()` — excluye expiradas | Query scope | No retorna licencias expiradas o terminadas |
| **L-017** | `scopeExpiringLicenses()` — N días | Query scope | Retorna licencias que expiran en el periodo indicado |
| **LS-001** | `LicenseSeat::location()` — via usuario | Relationship | Retorna location del usuario asignado |
| **LS-002** | `LicenseSeat::location()` — via asset | Relationship | Retorna location del asset asignado |
| **LS-003** | `LicenseSeat::location()` — sin asignación | Relationship | Retorna false |

### 5.5 MÓDULO: Accessory (`Accessory.php` 528 LOC + `AccessoryCheckout.php` 199 LOC)

**Tests existentes:** 7 tests en `AccessoryTest.php` + 1 en `AccessoryPresenterTest.php`

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **AC-008** | `percentRemaining()` — qty = 0 | Business logic | Retorna 0 cuando qty es 0 o vacío |
| **AC-009** | `percentRemaining()` — checkouts = 0 | Business logic | Retorna 100 cuando no hay checkouts |
| **AC-010** | `percentRemaining()` — stock parcial | Business logic | Retorna porcentaje correcto (ej. 50%) |
| **AC-011** | `isDeletable()` — con checkouts activos | Authorization | Retorna false si `checkouts_count > 0` |
| **AC-012** | `isDeletable()` — sin checkouts | Authorization | Retorna true si `checkouts_count === 0` |
| **AC-013** | `totalCostSum()` — purchase_cost null | Data | Retorna null si `purchase_cost` es null |
| **AC-014** | `totalCostSum()` — cálculo normal | Data | Retorna `qty * purchase_cost` |
| **AC-015** | `setQtyAttribute()` — valor vacío | Mutator | Establece 0 si el valor es falsy |

> **Nota importante:** `percentRemaining()` en `Accessory` tiene un orden de condiciones diferente al de `Consumable`:
> - `Accessory`: primero verifica `qty == 0`, luego `checkouts_count == 0`
> - `Consumable`: primero verifica `consumables_users_count == 0`, luego `qty == 0`
>
> Esto significa que si `qty=0` y `checkouts=0`, `Accessory` retorna **0** pero `Consumable` retorna **100**. Esta inconsistencia debe ser documentada y testeada explícitamente.

### 5.6 MÓDULO: Component (`Component.php` 484 LOC + `ComponentAssignment.php` 80 LOC)

**Tests existentes:** 8 tests en `ComponentTest.php`

**Cobertura existente confirmada:** `numCheckedOut()`, `numRemaining()`, `percentRemaining()`, company scoping.

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **CO-009** | `calculatedPurchaseCost()` — purchase_cost null con assignedQty | Accessor | Retorna 0.0 cuando cost es null pero hay qty asignada |
| **CO-010** | `calculatedPurchaseCost()` — purchase_cost null sin assignedQty | Accessor | Retorna null cuando cost es null y no hay qty |
| **CO-011** | `calculatedPurchaseCost()` — ambos presentes | Accessor | Retorna `purchase_cost * assigned_qty` |
| **CO-012** | `booted()` — unset sum_unconstrained_assets al guardar | Event hook | El atributo de caché se elimina antes de cada save |
| **CO-013** | `numCheckedOut()` — forzar recálculo | Business logic | Pasar `$recalculate=true` fuerza nueva query |
| **CO-014** | `isDeletable()` — con checkouts activos | Authorization | Retorna false si `numCheckedOut() > 0` |

### 5.7 MÓDULO: Consumable (`Consumable.php` 499 LOC + `ConsumableAssignment.php` 35 LOC)

**Tests existentes:** 0 tests de modelo (solo `ConsumablePresenterTest.php` que prueba el presentador, no el modelo)

**Este módulo requiere crear todos los tests desde cero — prioridad alta.**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **CN-001** | `percentRemaining()` — consumables_users_count = 0 | Business logic | Retorna 100 (comportamiento diferente a Accessory) |
| **CN-002** | `percentRemaining()` — qty = 0 | Business logic | Retorna 0 cuando qty es 0 |
| **CN-003** | `percentRemaining()` — stock parcial | Business logic | Retorna porcentaje correcto |
| **CN-004** | `numCheckedOut()` — con eager loading | Business logic | Usa `consumables_users_count` si está cargado |
| **CN-005** | `numCheckedOut()` — sin eager loading | Business logic | Ejecuta query `users()->count()` como fallback |
| **CN-006** | `numRemaining()` — cálculo normal | Business logic | `qty - numCheckedOut()` |
| **CN-007** | `isDeletable()` — con usuarios asignados | Authorization | Retorna false si `numCheckedOut() > 0` |
| **CN-008** | `isDeletable()` — sin asignaciones | Authorization | Retorna true si no hay asignaciones |
| **CN-009** | `totalCostSum()` — purchase_cost null | Data | Retorna null |
| **CN-010** | `totalCostSum()` — cálculo normal | Data | Retorna `qty * purchase_cost` |
| **CN-011** | `setQtyAttribute()` — valor vacío | Mutator | Establece 0 si el valor es falsy |
| **CN-012** | `getImageUrl()` — imagen propia | Presentation | Retorna URL de imagen del consumible |
| **CN-013** | `getImageUrl()` — imagen de categoría como fallback | Presentation | Retorna URL de imagen de la categoría |
| **CN-014** | `getImageUrl()` — sin imagen | Presentation | Retorna false |

### 5.8 MÓDULO: Category (`Category.php` — 347 LOC)

**Tests existentes:** 2 tests en `CategoryTest.php` — cobertura insuficiente.

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **CAT-003** | `isDeletable()` — tipo asset con models_count > 0 | Business logic | Retorna false si hay AssetModels (rama especial para assets) |
| **CAT-004** | `isDeletable()` — tipo asset sin modelos ni items | Business logic | Retorna true |
| **CAT-005** | `isDeletable()` — otros tipos con items | Business logic | Retorna false si `itemCount() > 0` |
| **CAT-006** | `itemCount()` — tipo asset | Business logic | Retorna count de assets via hasManyThrough |
| **CAT-007** | `itemCount()` — tipo accessory | Business logic | Retorna count de accessories |
| **CAT-008** | `itemCount()` — tipo component | Business logic | Retorna count de components |
| **CAT-009** | `itemCount()` — tipo consumable | Business logic | Retorna count de consumables |
| **CAT-010** | `itemCount()` — tipo license | Business logic | Retorna count de licenses |
| **CAT-011** | `getEula()` — EULA propia | Business logic | Retorna EULA específica de la categoría |
| **CAT-012** | `getEula()` — EULA global heredada | Business logic | Retorna EULA global si `use_default_eula = 1` |
| **CAT-013** | `getEula()` — sin EULA | Business logic | Retorna null |

### 5.9 MÓDULO: Company (`Company.php` — 373 LOC)

**Tests existentes:** 6 tests en `CompanyScopingTest.php`

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **CMP-007** | `isDeletable()` — con assets | Authorization | Retorna false si tiene assets asignados |
| **CMP-008** | `isDeletable()` — con users | Authorization | Retorna false si tiene usuarios asociados |
| **CMP-009** | `isDeletable()` — empresa vacía | Authorization | Retorna true si todas las 6 relaciones son cero |
| **CMP-010** | `isCurrentUserHasAccess()` — FMCS desactivado | Authorization | Retorna true para cualquier companyable |
| **CMP-011** | `isCurrentUserHasAccess()` — FMCS activo, misma empresa | Authorization | Retorna true si las empresas coinciden |
| **CMP-012** | `isCurrentUserHasAccess()` — FMCS activo, empresa diferente | Authorization | Retorna false |
| **CMP-013** | `getIdFromInput()` — input "0" | Data | Retorna null |
| **CMP-014** | `getIdFromInput()` — input válido | Data | Retorna el ID escapado |

### 5.10 MÓDULO: Checkout (`CheckoutAcceptance.php` 136 LOC + `CheckoutRequest.php` 57 LOC)

**Tests existentes:** 6 tests en `tests/Unit/Models/CheckoutRequestTest.php`

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **CKO-007** | `CheckoutAcceptance::isPending()` — sin fechas | Business logic | Retorna true si `accepted_at` y `declined_at` son null |
| **CKO-008** | `CheckoutAcceptance::isPending()` — aceptada | Business logic | Retorna false si `accepted_at` tiene valor |
| **CKO-009** | `CheckoutAcceptance::isCheckedOutTo()` — usuario correcto | Business logic | Retorna true si el usuario coincide |
| **CKO-010** | `getCheckoutableItemTypeAttribute()` — para Asset | Presentation | Retorna string traducido correcto |

### 5.11 MÓDULO: Statuslabel (`Statuslabel.php` — ~200 LOC)

**Tests existentes:** `StatuslabelTest.php` (número exacto a confirmar con los tests reales)

**Brechas identificadas:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **SL-001** | `getStatuslabelType()` — pending | Business logic | `pending=1, archived=0, deployable=0` → "pending" |
| **SL-002** | `getStatuslabelType()` — archived | Business logic | `pending=0, archived=1, deployable=0` → "archived" |
| **SL-003** | `getStatuslabelType()` — undeployable | Business logic | `pending=0, archived=0, deployable=0` → "undeployable" |
| **SL-004** | `getStatuslabelType()` — deployable (default) | Business logic | Cualquier otra combinación → "deployable" |
| **SL-005** | `getStatuslabelTypesForDB()` — tipo pending | Static method | Retorna array con flags correctos |
| **SL-006** | `getStatuslabelTypesForDB()` — tipo deployable | Static method | Retorna array con flags correctos |
| **SL-007** | `getStatuslabelTypesForDB()` — tipo archived | Static method | Retorna array con flags correctos |

### 5.12 MÓDULO: Depreciable (`Depreciable.php` — ~200 LOC) — Opcional

**Tests existentes:** 0 — sin embargo, el desarrollador original preparó `getDateTime()` expresamente para facilitar los tests (ver comentario en el código).

**Candidatos prioritarios si el equipo tiene capacidad:**

| ID | Caso de prueba | Tipo | Descripción |
|----|---------------|------|-------------|
| **DEP-001** | `getLinearDepreciatedValue()` — 0 meses pasados | Math | Retorna `purchase_cost` completo |
| **DEP-002** | `getLinearDepreciatedValue()` — 50% del periodo | Math | Retorna valor depreciated al 50% |
| **DEP-003** | `getLinearDepreciatedValue()` — periodo completo | Math | Retorna el valor mínimo (floor) |
| **DEP-004** | `depreciationProgressPercent()` — sin purchase_date | Math | Retorna 0.0 |
| **DEP-005** | `depreciationProgressPercent()` — clamp a 100 | Math | No supera 100% si ya depreció |

---

## 6. Métricas y criterios de evaluación

### 6.1 Cobertura esperada por módulo

| Módulo | LOC real | Tests actuales | Tests objetivo | Cobertura objetivo |
|--------|----------|---------------|---------------|-------------------|
| **Asset** | 2,184 | 20 | 24 | 85% |
| **AssetModel** | 387 | 4 | 12 | 85% |
| **User** | 1,504 | 25 | 37 | 80% |
| **License + LicenseSeat** | 1,197 | 7 | 20 | 85% |
| **Accessory** | 727 | 8 | 15 | 80% |
| **Component** | 564 | 8 | 14 | 80% |
| **Consumable** | 534 | 0 | 14 | 80% |
| **Category** | 347 | 2 | 13 | 80% |
| **Company** | 373 | 6 | 14 | 80% |
| **Checkout** | 193 | 6 | 10 | 75% |
| **Statuslabel** | ~200 | existente | +7 | 80% |
| **Depreciable** (opcional) | ~200 | 0 | 5 | 75% |
| **TOTAL** | ~7,420 | ~66 | ~139 | **81%** |

### 6.2 Criterios de entrada (inicio de fase)

La ejecución de pruebas unitarias puede comenzar cuando se cumplan **todas** las siguientes condiciones:

- [ ] El entorno de testing está configurado (`phpunit.xml` verificado)
- [ ] La base de datos de testing está disponible y migrada
- [ ] Las factories necesarias existen y son funcionales para cada módulo en alcance
- [ ] Los archivos de test están creados en la estructura `tests/Unit/`
- [ ] El equipo tiene acceso al repositorio con permisos de escritura

### 6.3 Criterios de salida (fin de fase)

La fase de pruebas unitarias se considera completa cuando:

- [ ] El 100% de los tests definidos en este plan se han ejecutado
- [ ] La cobertura mínima del 80% se alcanza en todos los módulos de alta prioridad
- [ ] Cero tests en estado `FAIL` al momento de la entrega del Sprint
- [ ] El reporte de cobertura HTML ha sido generado y archivado
- [ ] Los resultados han sido documentados en el **Informe de Pruebas Unitarias** (Wiki — Hito 2)
- [ ] Los defectos encontrados han sido registrados en **GitHub Issues** con etiqueta `bug`

### 6.4 Criterio de suspensión y reanudación

**Suspensión**: las pruebas se detienen si:
- Más del 20% de los tests fallan de forma inesperada (no por lógica de negocio incorrecta)
- El entorno de CI/CD presenta errores de configuración que impidan la ejecución
- Una factory o dependencia crítica está rota y bloquea múltiples módulos

**Reanudación**: se retoman cuando el defecto bloqueante ha sido corregido y verificado por el responsable en GitHub Issues.

### 6.5 Indicadores de éxito

| Indicador | Valor objetivo | Herramienta |
|-----------|---------------|-------------|
| Cobertura de código | ≥ 80% en módulos críticos | `php artisan test --coverage` |
| Tests pasando | 100% sin FAIL | PHPUnit / GitHub Actions |
| Tiempo de ejecución total | < 30 segundos | PHPUnit output |
| Defectos documentados | 100% en GitHub Issues | GitHub Issues — etiqueta `bug` |
| Tests con docblock descriptivo | 100% | Revisión de código |

---

## 7. Riesgos y mitigación

| ID | Riesgo | Probabilidad | Impacto | Mitigación |
|----|--------|-------------|---------|------------|
| **R-01** | Factories incompletas para módulos con brechas (Consumable) | Media | Alto | Verificar y completar factories antes de comenzar el Sprint |
| **R-02** | Entorno de BD de testing inestable | Baja | Alto | Usar SQLite en memoria (`:memory:`) para tests unitarios |
| **R-03** | Dependencias entre modelos que dificulten el aislamiento | Alta | Medio | Usar `RefreshDatabase` y factories con `make()` en lugar de `create()` |
| **R-04** | `getFullNameAttribute()` y `preferredLocale()` dependen de `Setting::getSettings()` | Alta | Medio | Mockear `Setting` o usar `InitializesSettings` trait del proyecto |
| **R-05** | Conflictos de merge al trabajar en paralelo sobre los mismos archivos | Media | Bajo | Asignar un archivo de test por integrante; usar ramas de feature |
| **R-06** | Tiempo insuficiente para alcanzar cobertura objetivo antes del Hito 2 | Media | Alto | Priorizar Consumable (0 tests) y License (brechas críticas) primero |
| **R-07** | Cambios en el código fuente del fork que rompan tests existentes | Baja | Medio | Ejecutar suite completa antes de cada merge; proteger rama `main` con CI |
| **R-08** | `booted()` hooks difíciles de testear en aislamiento | Alta | Medio | Usar `RefreshDatabase` + factories para verificar efectos en cascada |

---

## 8. Responsabilidades

| Rol | Integrante | Responsabilidades |
|-----|------------|-------------------|
| QA Lead | _Nombre 1_ | Redacción y mantenimiento del plan, revisión final, Asset + AssetModel |
| Tester | _Nombre 2_ | User, Checkout |
| Tester | _Nombre 3_ | License + LicenseSeat |
| Tester | _Nombre 4_ | Accessory, Component |
| Tester / CI | _Nombre 5_ | Consumable (prioridad), Category, configuración GitHub Actions |
| Revisor / Docs | _Nombre 6_ | Company, Statuslabel, documentación Wiki, informe de resultados |

---

## 9. Fases de implementación

| Fase | Actividad | Responsable | Estado |
|------|-----------|-------------|--------|
| **Fase 1** | Configuración de entorno y verificación de factories | _Nombre 5_ | [ ] Pendiente |
| **Fase 2** | Tests módulos críticos sin cobertura: Consumable, brechas de License | _Nombres 3 y 5_ | [ ] Pendiente |
| **Fase 3** | Ampliar tests de módulos con brechas: Asset, AssetModel, User, Category | _Nombres 1 y 2_ | [ ] Pendiente |
| **Fase 4** | Ampliar tests de módulos secundarios: Accessory, Component, Company, Statuslabel | _Nombres 4 y 6_ | [ ] Pendiente |
| **Fase 5** | Ejecución completa, reporte de cobertura, documentación en Wiki | _Nombre 6_ | [ ] Pendiente |
| **Fase 6** | Revisión del plan y ajustes para el Informe de Pruebas (Hito 2) | _Nombre 1_ | [ ] Pendiente |

---

## 10. Consideraciones especiales

### 10.1 Inconsistencia detectada: `percentRemaining()` en Accessory vs Consumable

Durante la auditoría del código fuente se detectó una **inconsistencia de comportamiento** entre dos módulos del mismo sistema:

```php
// Accessory.php — primero verifica qty
public function percentRemaining() {
    if ($this->qty == 0) return 0;           // ← primero qty
    if ($this->checkouts_count == 0) return 100;
    return ($this->qty - $this->checkouts_count) / $this->qty * 100;
}

// Consumable.php — primero verifica checkouts
public function percentRemaining() {
    if ($this->consumables_users_count == 0) return 100;  // ← primero checkouts
    if ($this->qty == 0) return 0;
    return ($this->qty - $this->consumables_users_count) / $this->qty * 100;
}
```

**Resultado del caso borde `qty=0, checkouts=0`:**
- `Accessory` → retorna **0**
- `Consumable` → retorna **100**

Esta inconsistencia debe documentarse como deuda técnica en GitHub Issues y cubrirse con tests explícitos.

### 10.2 Validación automática con `watson/validating`

El proyecto usa el paquete `watson/validating` que proporciona validación automática antes de cada `save()`:

```php
$consumable = new Consumable(['name' => '']);
$consumable->save(); // Lanza excepción si 'name' está vacío
```

Los tests de validación deben verificar que las reglas de `$rules` se aplican correctamente usando `assertFalse($model->isValid())` o capturando la excepción.

### 10.3 Soft deletes

Todos los modelos principales usan `SoftDeletes`. Las queries por defecto excluyen registros soft-deleted. Para incluirlos usar `->withTrashed()`.

### 10.4 Dependencia de `Setting::getSettings()` en métodos de User

Métodos como `getFullNameAttribute()` y `preferredLocale()` dependen de `Setting::getSettings()`. Para tests unitarios, usar el trait `InitializesSettings` que ya existe en `tests/Support/`:

```php
use Tests\Support\InitializesSettings;

class UserTest extends TestCase {
    use InitializesSettings;
    // ...
}
```

---

## 11. Trazabilidad de casos de prueba

| Rango de IDs | Módulo | Tipo de prueba | Regla / comportamiento validado |
|-------------|--------|---------------|--------------------------------|
| AST-021 a AST-024 | Asset | Event hooks / Type safety | Cascada en soft/force delete, tipo de asignación |
| AM-005 a AM-012 | AssetModel | Event hooks / Business logic | Cascada en delete, disponibilidad, scopes |
| U-026 a U-037 | User | Event hooks / Authorization / Business logic | Tokens OAuth, permisos, jerarquía de managers |
| L-008 a LS-003 | License + LicenseSeat | Business logic / Query scopes | Estado de licencia, asientos, filtros |
| AC-008 a AC-015 | Accessory | Business logic / Mutators | Disponibilidad, eliminación, costos |
| CO-009 a CO-014 | Component | Accessor / Event hooks | Costo calculado, caché de checkouts |
| CN-001 a CN-014 | Consumable | Business logic / Mutators / Presentation | Todos los métodos (sin cobertura previa) |
| CAT-003 a CAT-013 | Category | Business logic | isDeletable bifurcado, itemCount switch, EULA |
| CMP-007 a CMP-014 | Company | Authorization / Data | isDeletable, FMCS access control |
| CKO-007 a CKO-010 | Checkout | Business logic / Presentation | Estado de aceptación, tipo de item |
| SL-001 a SL-007 | Statuslabel | Business logic | Clasificación de tipos de estado |
| DEP-001 a DEP-005 | Depreciable | Math (opcional) | Cálculos de depreciación lineal y por progreso |

---

## 12. Referencias y documentación

### 12.1 Recursos del proyecto

- **Configuración:** `phpunit.xml`
- **Clase base de tests:** `tests/TestCase.php`
- **Soporte de tests:** `tests/Support/InitializesSettings.php`
- **Factories:** `database/factories/*Factory.php`
- **Modelos:** `app/Models/*.php`

### 12.2 Documentación externa

- [PHPUnit 10.5 Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Model Factories](https://laravel.com/docs/eloquent-factories)
- [ISO/IEC/IEEE 29119-3 — Software Testing: Test Documentation](https://www.iso.org/standard/56737.html)

### 12.3 Herramientas del proyecto

- **Gestión de tareas:** GitHub Projects (tablero Kanban/Scrum)
- **Seguimiento de defectos:** GitHub Issues (etiqueta `bug`)
- **CI/CD:** GitHub Actions (workflows en `.github/workflows/`)
- **Documentación:** GitHub Wiki (`jhuamaniCond/snipe-it/wiki`)

---

*Fin del documento — Plan de Pruebas Unitarias v2.0*
*Próximo documento: Informe de Pruebas Unitarias (Hito 2 — entrega 10.JUN.2026)*
