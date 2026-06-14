# Informe de Diagnóstico — Snipe-IT (Testing)

> Fecha: 2026-06-13 · Solo análisis (sin cambios, sin instalación, sin ejecución de pruebas)

## 1. Resumen de arquitectura

**Snipe-IT** es un sistema open source de gestión de activos (asset management) construido sobre Laravel.

| Componente | Detalle |
|---|---|
| Paquete | `grokability/snipe-it` |
| Framework | **Laravel ^12.0** |
| Lenguaje | **PHP ^8.2** (requerido por `composer.json`) |
| PHP local instalado | **PHP 8.4.22** (vía Laravel Herd, NTS x64) |
| Frontend | Laravel Mix (webpack), AdminLTE 2 / Bootstrap 3, Blade |
| Autenticación API | **Laravel Passport ^12.0** (OAuth2) |
| UI reactiva | Livewire ^4.0 |
| Testing | **PHPUnit ^11.0**, Mockery, php-mock, Faker |
| Calidad | Larastan ^3, Pint, PHP_CodeSniffer, PHPInsights |

**Patrón de arquitectura** (según `CLAUDE.md`):
- Doble árbol de controladores: `app/Http/Controllers/` (web/Blade) y `app/Http/Controllers/Api/` (REST JSON).
- API siempre devuelve datos vía **Transformers** (`app/Http/Transformers/`).
- Autorización mediante **Policies** (`app/Policies/`).
- FMCS (Full Multiple Company Support) para multi-empresa.
- Rutas en `routes/web.php` y `routes/api.php`.

### Dependencias principales
- **Núcleo:** laravel/framework, laravel/passport, laravel/socialite, laravel/ui, livewire/livewire, nesbot/carbon
- **Activos / PDF / códigos:** tecnickcom/tcpdf, tc-lib-barcode, bacon/bacon-qr-code, intervention/image
- **Datos / import:** league/csv, doctrine/dbal
- **Integraciones:** SAML (onelogin/php-saml), SCIM (laravel-scim-server), Slack, Teams, Google Chat
- **Dev:** phpunit ^11, mockery, larastan, pint, telescope, debugbar

---

## 2. Estructura de carpetas relevantes (testing)

```
tests/
├── TestCase.php              # Clase base de tests
├── CreatesApplication.php    # (trait usado por TestCase)
├── Unit/                     # Suite Unit  (45 archivos *Test.php)
│   ├── AssetTest.php, UserTest.php, CategoryTest.php, ...
│   ├── Actions/  BladeComponents/  Helpers/  Importer/
│   ├── Labels/  Listeners/  Mail/  Models/  Presenters/  Transformers/
│   └── ...
├── Feature/                  # Suite Feature (292 archivos *Test.php)
│   ├── Assets/  Licenses/  Users/  Accessories/  Consumables/
│   ├── Components/  Checkouts/  Checkins/  Importing/  Reporting/
│   ├── Security/  Settings/  Setup/  Notifications/  Livewire/  ...
└── Support/                  # Helpers de testing
    ├── InteractsWithAuthentication.php   # usa Passport::actingAs()
    ├── InitializesSettings.php
    ├── AssertHasActionLogs.php
    ├── AssertsAgainstSlackNotifications.php
    ├── CanSkipTests.php
    ├── CustomTestMacros.php
    └── ProvidesDataForFullMultipleCompanySupportTesting.php
```

### Conteo de pruebas
- **Archivos de test:** 337 (`tests/Feature` = 292, `tests/Unit` = 45)
- **Métodos de prueba:** ~**1.949** en total
  - `public function test*`: 1.834
  - Anotaciones `#[Test]`: 115

> El número exacto que reportará PHPUnit puede variar por uso de *data providers* (un método con varios datasets cuenta como múltiples casos).

---

## 3. Configuración de PHPUnit (`phpunit.xml`)

```xml
bootstrap        = bootstrap/autoload.php
cacheDirectory   = .phpunit.cache
colors           = true
processIsolation = false
stopOnFailure    = false
schema           = PHPUnit 10.5
```

**Suites declaradas:**
| Suite | Directorio | Sufijo |
|---|---|---|
| Unit | `./tests/Unit` | `Test.php` |
| Feature | `./tests/Feature` | `Test.php` |

**Variables de entorno embebidas (`<php>`):**
- `APP_ENV=testing`
- `BCRYPT_ROUNDS=4` (hashing rápido para tests)
- `CACHE_DRIVER=array`, `SESSION_DRIVER=array`, `QUEUE_DRIVER=sync`
- `MAIL_MAILER=array`, `MAIL_FROM_ADDR=app@example.com`
- `display_errors=true`

> ⚠️ Nota: el schema apunta a `10.5` pero el paquete instalado es PHPUnit `^11`. Funciona, pero conviene tenerlo presente.

### Base de datos en pruebas
- `config/database.php` define la conexión **`sqlite_testing` → `:memory:`** (SQLite en memoria, ideal para tests rápidos).
- `TestCase` usa el trait `LazilyRefreshDatabase` (migra/refresca la BD entre tests).
- Extensiones PHP confirmadas en Herd: `pdo_sqlite`, `sqlite3`, `pdo_mysql`, `gd`, `curl`, `mbstring` ✅

### Guardarraíl importante (`tests/TestCase.php`)
```php
private function guardAgainstMissingEnv(): void {
    if (! file_exists(realpath(__DIR__.'/../').'/.env.testing')) {
        throw new RuntimeException(
            '.env.testing file does not exist. Aborting to avoid wiping your local database.'
        );
    }
}
```
**Toda la suite aborta si no existe `.env.testing`.** Es un mecanismo de seguridad para no borrar la BD de desarrollo.

---

## 4. Configuración de cobertura

No hay cobertura definida dentro de `phpunit.xml` (solo un bloque `<source>` que incluye `app/`). La cobertura se gestiona por scripts y CI:

**Scripts en `composer.json`:**
```bash
coverage:herd:clover  →  herd coverage vendor/bin/phpunit --coverage-clover tests/coverage/clover.xml
coverage:herd:html    →  herd coverage vendor/bin/phpunit --coverage-html  tests/coverage/html
```
> Localmente la cobertura se obtiene con **Herd** (`herd coverage ...`), porque Herd habilita el driver de cobertura.

**CI — `.github/workflows/tests-unit-coverage.yml`** (ya presente en el repo):
- Matriz PHP **8.2 / 8.3 / 8.4**
- Driver de cobertura **PCOV** (`coverage: pcov`)
- Conexión `DB_CONNECTION=sqlite_testing` (SQLite in-memory)
- Ejecuta **solo la suite Unit**:
  ```bash
  vendor/bin/phpunit --testsuite Unit \
    --coverage-clover coverage/clover.xml \
    --coverage-html coverage/html \
    --log-junit coverage/junit.xml
  ```
- Publica `coverage/` como artefacto.

**`<source>` (qué se mide):** `app/**/*.php`.

---

## 5. Estado actual del entorno

| Requisito | Estado | Notas |
|---|---|---|
| PHP ^8.2 | ✅ | PHP 8.4.22 vía Herd (`~/.config/herd/bin/php.bat`) |
| Composer | ✅ | Disponible vía Herd (`~/AppData/Roaming/Herd/composer.bat`) — no en PATH global |
| Extensiones PHP | ✅ | pdo_sqlite, sqlite3, gd, curl, mbstring confirmadas |
| `composer.lock` | ✅ | Presente (versiones fijadas) |
| **`vendor/`** | ❌ | **NO instalado** — falta `composer install` |
| **`.env.testing`** | ❌ | **NO existe** — solo plantillas (`*.example`) |
| `.env` | ❌ | No presente (no crítico para tests, pero CI lo copia) |
| Llaves de Passport | ❌ | No generadas (los tests usan `Passport::actingAs`) |
| Conexión de test | ✅ | `sqlite_testing` (`:memory:`) ya configurada |
| `.phpunit.cache` | ⚪ | No existe aún (se crea al ejecutar) |

**Archivos de entorno disponibles:**
- `.env.example`, `.env.testing.example`, `.env.testing-ci`, `.env.tests`, `.env.unit-tests`, `.env.dusk.example`, `.env.docker`, `.env.dev.docker`
- `.env.testing.example` usa MySQL por defecto; `.env.testing-ci` y `.env.unit-tests` usan SQLite.

> `.gitignore` excluye `vendor/`, `.env` y `.env.testing` (por eso no están versionados — es lo esperado).

---

## 6. Riesgos / bloqueos encontrados

1. **🔴 BLOQUEO — `vendor/` no instalado.** Sin dependencias no se puede ejecutar PHPUnit ni artisan. Requiere `composer install`.
2. **🔴 BLOQUEO — Falta `.env.testing`.** `TestCase::guardAgainstMissingEnv()` lanza `RuntimeException` y aborta toda la suite. Hay que crearlo a partir de una plantilla.
3. **🟠 Llaves de Passport ausentes.** Los tests de API usan `Passport::actingAs()`; sin `php artisan passport:keys` algunos tests fallarán.
4. **🟠 Elección de driver de BD para tests.** `.env.testing.example` apunta a **MySQL** (requiere servidor MySQL). Para correr offline conviene usar **SQLite in-memory** (`DB_CONNECTION=sqlite_testing`), como hace el workflow de cobertura.
5. **🟡 Cobertura requiere driver.** Sin PCOV/Xdebug no hay reportes de cobertura. Localmente se resuelve con `herd coverage ...`; en CI con PCOV.
6. **🟡 Composer/PHP no están en el PATH global** del shell — hay que invocarlos vía las rutas de Herd o ajustar el PATH.
7. **🟡 Schema PHPUnit 10.5 vs paquete ^11.** Inconsistencia menor de versión en `phpunit.xml`; no bloquea.
8. **🟡 `APP_KEY`.** Las plantillas traen una clave; si se usa un `.env.testing` propio sin clave, ejecutar `php artisan key:generate`.

---

## 7. Pasos recomendados para continuar

> Pendiente de tu aprobación — **aún no he ejecutado nada ni modificado archivos.**

**A. Preparar dependencias y entorno (una sola vez):**
```bash
# Desde la raíz del proyecto, usando el PHP/Composer de Herd
composer install

# Crear el .env.testing requerido por el guardarraíl
cp .env.testing.example .env.testing      # (o .env.unit-tests para SQLite)
cp .env.testing.example .env

php artisan key:generate
php artisan passport:keys                  # genera llaves OAuth para tests de API
```

**B. Elegir motor de base de datos para pruebas:**
- **Recomendado (rápido, sin servidor):** SQLite in-memory →
  exportar `DB_CONNECTION=sqlite_testing` o usar `.env.unit-tests`.
- **Fiel a producción:** MySQL local con la BD/credenciales de `.env.testing.example`.

**C. Ejecutar pruebas (según el proyecto):**
```bash
# Toda la suite
php artisan test

# Una suite concreta
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Feature

# Un archivo o método
php artisan test tests/Feature/Assets/AssetsTest.php
php artisan test --filter testSomeMethod
```

**D. Cobertura (con Herd):**
```bash
composer run coverage:herd:html      # reporte HTML en tests/coverage/html
composer run coverage:herd:clover    # Clover XML
```

**E. Validación recomendada antes de avanzar:**
- Confirmar que `php artisan test` arranca sin el `RuntimeException` del `.env.testing`.
- Empezar por `--testsuite Unit` (más aislada y rápida) antes de Feature.

---

### Resumen ejecutivo
El proyecto está **bien estructurado y con CI sólido** (workflows MySQL, Postgres, SQLite y cobertura ya definidos), pero el entorno local **NO está listo todavía**: faltan `vendor/` (composer install), `.env.testing` (bloqueo duro por guardarraíl) y las llaves de Passport. PHP 8.4 y SQLite están disponibles vía Herd. Una vez resueltos esos tres puntos, ~1.949 pruebas en 337 archivos quedarían ejecutables, preferentemente con SQLite in-memory.
