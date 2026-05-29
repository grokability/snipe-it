# Plan de Pruebas Unitarias - Snipe-IT

## 1. Introducción

Este documento define la estrategia y planificación de pruebas unitarias para los **módulos principales del backend** del sistema Snipe-IT, un sistema libre y de código abierto para la gestión de activos y licencias de TI.

**Repositorio:** `jhuamaniCond/snipe-it`  
**Lenguaje:** PHP 92.3% (Framework: Laravel)  
**Herramienta de Testing:** PHPUnit  
**Fecha de Elaboración:** 2026-05-29

---

## 2. Alcance del Plan

### 2.1 Objetivos
- ✅ Garantizar la calidad del código en los módulos críticos del sistema
- ✅ Identificar y prevenir regresiones en funcionalidades clave
- ✅ Documentar comportamientos esperados de las clases principales
- ✅ Facilitar el mantenimiento y refactorización del código
- ✅ Establecer una base sólida para pruebas continuas (CI/CD)

### 2.2 Módulos en Alcance

| Módulo | Clase Principal | Ruta | Responsabilidad |
|--------|-----------------|------|-----------------|
| **AssetModel** | `App\Models\AssetModel` | `app/Models/AssetModel.php` | Gestión de modelos de activos (definiciones de tipos) |
| **User** | `App\Models\User` | `app/Models/User.php` | Gestión de usuarios, autenticación y permisos |
| **License** | `App\Models\License` | `app/Models/License.php` | Gestión de licencias de software y asignación de seats |

### 2.3 Módulos Fuera de Alcance
- Tests de integración (Feature tests) - Se harán en fase posterior
- Tests de API REST - Se harán en fase posterior
- Tests de interfaces de usuario (UI tests) - Se harán en fase posterior
- Tests de base de datos (migrations, seeders) - Se harán en fase posterior

---

## 3. Configuración del Entorno de Testing

### 3.1 Herramientas Requeridas

```
phpunit/phpunit: ^10.5
composer require --dev phpunit/phpunit
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

### 3.3 Estructura de Directorios

```
tests/
├── Unit/
│   └── Models/
│       ├── AssetModelTest.php
│       ├── UserModelTest.php
│       └── LicenseModelTest.php
├── TestCase.php
├── CreatesApplication.php
```

### 3.4 Ejecución de Tests

```bash
# Ejecutar todos los tests unitarios
php artisan test tests/Unit

# Ejecutar tests de un módulo específico
php artisan test tests/Unit/Models/AssetModelTest.php

# Ejecutar con coverage
php artisan test --coverage tests/Unit

# Ejecutar y generar reporte HTML
php artisan test --coverage tests/Unit --coverage-html=coverage
```

---

## 4. Estrategia de Testing

### 4.1 Patrón Testing

Utilizaremos el patrón **AAA (Arrange-Act-Assert)**:

```php
public function testExample()
{
    // Arrange: Preparar datos de prueba
    $user = User::factory()->create();
    
    // Act: Ejecutar la acción
    $result = $user->hasAccess('admin');
    
    // Assert: Verificar el resultado
    $this->assertTrue($result);
}
```

### 4.2 Uso de Factories

Aprovecharemos los **Laravel Model Factories** para generar datos de prueba:

```php
// Crear un usuario con datos específicos
$user = User::factory()->create(['username' => 'john.doe']);

// Crear múltiples usuarios
$users = User::factory()->count(5)->create();

// Crear con estados personalizados
$admin = User::factory()->admin()->create();
```

### 4.3 Convenciones de Nomenclatura

Cada test debe tener un identificador único y descriptivo:

```
test{ModuleCode}-{SequentialNumber}: {DescriptionInEnglish}

Ejemplos:
- testAM-001: ValidationFailsWithoutRequiredFields
- testU-002: PasswordIsHashedAndHidden
- testL-003: LicenseIsExpired
```

**Códigos de módulo:**
- `AM` = AssetModel
- `U` = User
- `L` = License

---

## 5. Módulos y Casos de Prueba

### 5.1 MÓDULO: AssetModel

**Clase:** `App\Models\AssetModel`  
**Responsabilidad:** Define atributos y reglas comunes para tipos de activos

#### Casos de Prueba Identificados

| ID | Caso de Prueba | Tipo | Descripción |
|-----|---|---|---|
| **AM-001** | Validation Fails Without Required Fields | Validation | Verifica que `name` y `category_id` son requeridos |
| **AM-002** | Mass Assignment Protection | Security | Solo atributos en `$fillable` pueden asignarse masivamente |
| **AM-003** | Relationship - assets() | Relationship | AssetModel.hasMany(Asset) funciona correctamente |
| **AM-004** | Percent Remaining Calculation | Business Logic | Calcula porcentaje de activos disponibles: `(avail/total)*100` |
| **AM-005** | isDeletable() - No Assets | Authorization | Se puede eliminar si no hay activos asociados |
| **AM-006** | isDeletable() - With Assets | Authorization | No se puede eliminar si hay activos asociados |
| **AM-007** | Soft Delete Cascading | Data Integrity | Al soft-delete, se eliminan requests asociadas |
| **AM-008** | Scope - inCategory() | Query | Filtra modelos por array de `category_id` |
| **AM-009** | Scope - requestableModels() | Query | Solo retorna modelos marcados como `requestable=true` |
| **AM-010** | Attribute Casting | Type Safety | Tipos de datos se casted correctamente |
| **AM-011** | Model Validation Rules | Validation | Se aplican todas las reglas de validación |
| **AM-012** | Verify Model Has Presenter | Presentation | Usa `AssetModelPresenter` para presentación |
| **AM-013** | BelongsTo Category | Relationship | AssetModel.belongsTo(Category) correcto |
| **AM-014** | BelongsTo Manufacturer | Relationship | AssetModel.belongsTo(Manufacturer) opcional |
| **AM-015** | BelongsTo Depreciation | Relationship | AssetModel.belongsTo(Depreciation) opcional |

---

### 5.2 MÓDULO: User

**Clase:** `App\Models\User`  
**Responsabilidad:** Autenticación, gestión de usuarios y permisos

#### Casos de Prueba Identificados

| ID | Caso de Prueba | Tipo | Descripción |
|-----|---|---|---|
| **U-001** | Validation Fails Without Required Fields | Validation | `first_name`, `username`, `password` requeridos |
| **U-002** | Password Is Hashed and Hidden | Security | Contraseña se hashea, no se devuelve en JSON |
| **U-003** | BelongsTo Location | Relationship | User.belongsTo(Location) relación correcta |
| **U-004** | BelongsTo Company | Relationship | User.belongsTo(Company) relación correcta |
| **U-005** | BelongsTo Manager (Self-referential) | Relationship | User puede tener manager (self-ref) |
| **U-006** | Soft Delete Functionality | Data Integrity | Soporta soft deletes, mantiene `deleted_at` |
| **U-007** | Unique Username Validation | Validation | Username debe ser único (entre no-deleted) |
| **U-008** | Mass Assignment Protection | Security | Atributos sensibles protegidos de asignación masiva |
| **U-009** | Hidden Attributes in Output | Security | `password`, `permissions`, tokens no en JSON |
| **U-010** | Email Validation | Validation | Email valida formato y es opcional (nullable) |
| **U-011** | Attribute Casting | Type Safety | DateTime y integer fields castean correctamente |
| **U-012** | User Can Have API Tokens | Integration | Trait `HasApiTokens` funciona (Laravel Passport) |
| **U-013** | User Is Notifiable | Integration | Trait `Notifiable` para enviar notificaciones |
| **U-014** | User Display Name | Presentation | Campo `display_name` se genera/accede correctamente |
| **U-015** | User Locale Preference | Configuration | Implementa `HasLocalePreference` para i18n |
| **U-016** | User LDAP Import Flag | Integration | Flag `ldap_import` se almacena correctamente |
| **U-017** | Employee Number Storage | Data | `employee_num` se almacena y recupera |
| **U-018** | VIP User Flag | Business Logic | Flag `vip` se almacena y recupera correctamente |

---

### 5.3 MÓDULO: License

**Clase:** `App\Models\License`  
**Responsabilidad:** Gestión de licencias de software y asignación de seats

#### Casos de Prueba Identificados

| ID | Caso de Prueba | Tipo | Descripción |
|-----|---|---|---|
| **L-001** | Validation Fails Without Required Fields | Validation | `name`, `seats`, `category_id` requeridos |
| **L-002** | Remain Count Calculation | Business Logic | Calcula: `total - assigned - unreassignable` |
| **L-003** | License Is Expired | Business Logic | `isExpired()` retorna true si pasó `expiration_date` |
| **L-004** | License Is Terminated | Business Logic | `isTerminated()` retorna true si pasó `termination_date` |
| **L-005** | License Is Inactive | Business Logic | `isInactive()` retorna true si expired O terminated |
| **L-006** | Seat Count Adjustment on Creation | Data Integrity | Al crear, se generan N `LicenseSeat` records |
| **L-007** | Seat Count Adjustment on Update | Data Integrity | Al actualizar seats, se ajustan registros |
| **L-008** | Percent Remaining Calculation | Business Logic | Calcula: `(avail/total)*100` |
| **L-009** | Mass Assignment Protection | Security | Atributos sensibles protegidos |
| **L-010** | Scope - activeLicenses() | Query | Retorna solo licencias no expiradas/terminadas |
| **L-011** | Scope - expiredLicenses() | Query | Retorna solo licencias expiradas/terminadas |
| **L-012** | Scope - expiringLicenses() | Query | Retorna licencias expirando en N días |
| **L-013** | Attribute Casting | Type Safety | Dates castean a Carbon instances |
| **L-014** | BelongsTo Category | Relationship | License.belongsTo(Category) correcto |
| **L-015** | HasMany LicenseSeats | Relationship | License.hasMany(LicenseSeat) correcto |
| **L-016** | freeSeat() Method | Business Logic | Retorna primer seat disponible sin asignar |
| **L-017** | Soft Delete Functionality | Data Integrity | Soporta soft deletes |
| **L-018** | isDeletable() Logic | Authorization | Solo deletable si todos los seats están libres |

---

## 6. Métricas de Testing

### 6.1 Cobertura Esperada

| Módulo | Líneas de Código | Métodos Críticos | Cobertura Objetivo |
|--------|---|---|---|
| **AssetModel** | ~150 | 8 | 85% |
| **User** | ~500+ | 25+ | 80% |
| **License** | ~300+ | 15+ | 85% |
| **TOTAL** | ~950+ | 48+ | **83%** |

### 6.2 Indicadores de Éxito

✅ **Cobertura de Código:** Mínimo 80% para módulos críticos  
✅ **Tests Pasando:** 100% de tests deben pasar en CI/CD  
✅ **Ejecución:** Tests deben ejecutar en < 10 segundos (total)  
✅ **Mantenibilidad:** Tests deben ser claros y fáciles de mantener  
✅ **Documentación:** Cada test debe incluir docblock con descripción

---

## 7. Dependencias y Pre-requisitos

### 7.1 Librerías Requeridas

```json
{
  "require-dev": {
    "phpunit/phpunit": "^10.5",
    "laravel/factories": "included",
    "laravel/seeders": "included"
  }
}
```

### 7.2 Configuración Base de Datos Testing

```env
DB_CONNECTION=testing
TESTING_DB=snipe_it_test
```

### 7.3 Factories Necesarias

Se requieren factories para:
- `User::factory()` - Crear usuarios de prueba
- `Asset::factory()` - Crear activos de prueba
- `AssetModel::factory()` - Crear modelos de activos
- `License::factory()` - Crear licencias de prueba
- `Category::factory()` - Crear categorías
- `Location::factory()` - Crear ubicaciones
- `Company::factory()` - Crear empresas

---

## 8. Fases de Implementación

### Fase 1: Configuración (Semana 1)
- [ ] Revisar estructura de tests existente
- [ ] Configurar factories para cada modelo
- [ ] Preparar ambiente de testing
- [ ] Crear estructura de directorios

### Fase 2: AssetModel Tests (Semana 2)
- [ ] Crear `AssetModelTest.php` con 15 casos
- [ ] Ejecutar y validar tests
- [ ] Generar reporte de cobertura

### Fase 3: User Tests (Semana 3-4)
- [ ] Crear `UserModelTest.php` con 18 casos
- [ ] Ejecutar y validar tests
- [ ] Generar reporte de cobertura

### Fase 4: License Tests (Semana 5-6)
- [ ] Crear `LicenseModelTest.php` con 18 casos
- [ ] Ejecutar y validar tests
- [ ] Generar reporte de cobertura

### Fase 5: Documentación y CI/CD (Semana 7)
- [ ] Documentar resultados en Wiki
- [ ] Configurar GitHub Actions para CI
- [ ] Realizar quality gate checks

---

## 9. Consideraciones Especiales

### 9.1 Validación en Snipe-IT

El proyecto usa `watson/validating` package que proporciona validación automática:

```php
// Los modelos validarán antes de guardar
$user = new User(['username' => 'john']);
$user->save(); // Valida automáticamente según $rules
```

### 9.2 Soft Deletes

Varios modelos usan soft deletes (`SoftDeletes` trait):
- Queries por defecto excluyen registros soft-deleted
- Usar `->withTrashed()` para incluirlos en queries

### 9.3 Relaciones Polimórficas

`Asset` puede asignarse a: User, Location, o Asset (self-referential)
```php
$asset->assignedTo(); // Retorna User|Location|Asset|null
```

### 9.4 Traits Utilizados

Todos los modelos usan múltiples traits:
- `Loggable` - Auditoría de cambios
- `Searchable` - Búsqueda avanzada
- `HasFactory` - Factories para testing
- `SoftDeletes` - Soft delete capability

---

## 10. Referencias y Documentación

### 10.1 Recursos del Proyecto

- 📁 **Configuración:** `phpunit.xml` (líneas 12-18)
- 📁 **Base Test:** `tests/TestCase.php`
- 📁 **Models:** `app/Models/{AssetModel,User,License}.php`
- 📁 **Factories:** `database/factories/*Factory.php`

### 10.2 Documentación Externa

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Model Factories](https://laravel.com/docs/eloquent-factories)

### 10.3 Contacto y Soporte

Para preguntas sobre este plan:
- Revisar documentación del código fuente
- Consultar la base de datos del proyecto
- Ejecutar tests con flag `-v` para más detalles

---

## 11. Aprobación y Cambios

| Rol | Nombre | Fecha | Firma |
|-----|--------|-------|-------|
| **Diseñador de Pruebas** | Anette Gallegos | 2026-05-29 | ✅ |
| **Lead Developer** | Por asignar | - | ⏳ |
| **QA Manager** | Por asignar | - | ⏳ |

---

**Versión:** 1.0  
**Estado:** Aprobado para Desarrollo  
**Última Actualización:** 2026-05-29  
**Próxima Revisión:** 2026-06-30

