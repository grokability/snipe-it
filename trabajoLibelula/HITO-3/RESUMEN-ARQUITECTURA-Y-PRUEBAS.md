# Resumen de arquitectura, tamaño y tipos de prueba — Snipe-IT

> Documento base para arrancar las **pruebas de integración** del Hito 3.
> Métricas medidas directamente sobre el código (excluye `trabajoLibelula/`, `vendor/` y los assets compilados de `public/`). Fecha de medición: 2026-07-04.
> **Líneas físicas** (`wc -l`, incluyen blancos y comentarios); el SLOC "puro" sería ~30–40 % menor.

---

## 1. Tamaño del sistema (KLOC)

| Capa | Carpetas | Líneas | ≈ KLOC |
|---|---|---:|---:|
| **Backend (lógica PHP)** | `app/` (87 126) + `routes/` (3 054) + `database/` (4 965) + `config/` (4 401) | **99 546** | ~99.5 |
| **Frontend / presentación** | `resources/views/` Blade (39 069) + `resources/assets/js` (4 246) + `resources/assets/less` (4 214) | **47 529** | ~47.5 |
| **Producción total (sin tests)** | — | **~147 075** | **~147 KLOC** |
| Código de pruebas | `tests/` | 53 566 | ~53.6 |

- Producción ≈ **147 KLOC físicas** (≈ 90–100 KLOC de SLOC efectivo).
- Reparto: **backend ~2/3**, **presentación ~1/3**.

---

## 2. Backend vs Frontend

Snipe-IT es un **monolito MVC renderizado en servidor** (Laravel 12 / PHP 8.2+). **No** es un SPA; no usa React/Vue como framework de UI.

### Backend (PHP / Laravel)
- `app/`: Models (**41**), Http/Controllers (**61 web + 30 API**), Policies (**22**), Middleware, Requests, Presenters, Transformers, Services, Jobs, Events, Listeners, Observers, Mail, Notifications, Rules, Providers, Console, Importer, Helpers, Enums, Traits.
- `routes/` (web + api), `database/` (migraciones/factories/seeders), `config/`.

### Frontend / capa de presentación
- `resources/views/`: plantillas **Blade** (render en servidor, **AdminLTE 2 / Bootstrap 3**).
- `resources/assets/js`: JS propio (jQuery + `snipeit.js`, select2, Chart.js v2). ~4.2 KLOC.
- `resources/assets/less` + `css`: estilos.
- `public/`: bundles **compilados** por Laravel Mix (no es fuente; no cuenta como KLOC propio).
- `app/Livewire/` (**8** componentes): híbrido — UI reactiva **manejada desde PHP**.

### Capas técnicas transversales ("lo que sobra")
Ni negocio-back ni UI-front, son infraestructura/soporte: `Console` (comandos artisan), `Jobs`, `Events`/`Listeners`, `Observers`, `Mail`, `Notifications`, `Providers`, `Exceptions`, `Rules` (validación), `Traits`, `Helpers`, `Services`, `Enums`, `Http/Middleware`.

---

## 3. Módulos / subsistemas (~22 funcionales)

Referencia: recursos de negocio (modelos + controladores + las 22 policies de autorización).

Assets · Asset Models · Licenses · License Seats · Accessories · Consumables · Components · Predefined Kits · Users · Groups · Departments · Locations · Companies · Categories · Manufacturers · Suppliers · Depreciations · Status Labels · Custom Fields/Fieldsets · Maintenances · Reports / Report Templates · Settings.

Subsistemas de soporte: Dashboard · Setup · Auth (login / 2FA / LDAP / SAML) · Importer · Action Log / History · Notifications.

---

## 4. ¿Las pruebas unitarias y la cobertura son de back o de front?

**Del backend (PHP).** Se ejecutan con **PHPUnit + PCOV**; el `<source>` de `phpunit.xml` apunta a `app/`.
El **frontend no tiene pruebas automatizadas**: no hay Jest, Vitest, Cypress, Playwright ni Dusk (verificado en `package.json` y `composer.json`). Las vistas Blade solo se ejercitan **indirectamente** cuando un Feature test renderiza una página, pero la cobertura mide **código PHP**.

---

## 5. Tipos de prueba en el repositorio

| Carpeta | # archivos `*Test.php` | Qué son |
|---|---:|---|
| `tests/Unit` | **168** | **Pruebas unitarias** puras: aíslan clases/métodos (Models, Presenters, Transformers, Rules, Helpers, Policies). |
| `tests/Feature` | **292** | **Pruebas de integración / funcionales** a nivel de aplicación (HTTP). |

### `tests/Feature` en detalle
En la terminología de Laravel son *"Feature tests"*. Patrón real (ej. `AccessoryCheckoutTest`): `actingAs(...)->post(route(...))`, **factories + base de datos real**, aserciones sobre respuesta HTTP, estado en BD, correos y eventos.

| Criterio | Clasificación |
|---|---|
| **Nivel** | Integración / funcional (arrancan framework + rutas + BD; combinan varias unidades). NO unitarias. |
| **Caja** | Técnicamente **caja blanca/gris** (las escriben devs conociendo el código), pero ejercitan el sistema por su **interfaz externa** (rutas web/API) → comportamiento tipo **caja negra en el límite**. |
| **NO son** | Pruebas de **sistema E2E** (no hay navegador/Selenium/Dusk) ni de **aceptación** formal (no hay Gherkin/BDD ni firma de negocio automatizada). |

> Los casos **CPF** ejecutados manualmente (wiki, Hito 2) son **pruebas funcionales de caja negra manuales**, independientes de `tests/Feature`.

---

## 6. Implicaciones para las PRUEBAS DE INTEGRACIÓN (Hito 3)

- **Ya existe una base de integración** en `tests/Feature` (292 archivos). Conviene **apoyarse y extenderla**, no reinventar: mismo estilo (`TestCase`, factories, HTTP).
- **Puntos de integración naturales a cubrir** (flujos entre módulos):
  - Checkout/Checkin de Asset ↔ **Licenses/LicenseSeats** (liberación de asientos), ↔ **Users/Locations**, ↔ **Action Log/History**.
  - **Accessories/Consumables/Components** ↔ Users/Assets (pivotes de checkout, decremento de stock).
  - **Categories/Manufacturers/Models** ↔ Assets (integridad referencial, borrado con dependencias).
  - **Custom Fields/Fieldsets** ↔ Assets (validación dinámica, encriptación).
  - **Settings (FMCS)** ↔ scoping por compañía en múltiples módulos.
  - **Auth** (login/2FA/LDAP) ↔ permisos/policies ↔ acceso a recursos.
  - **Importer** ↔ creación masiva de entidades.
  - **API v1** ↔ Transformers ↔ persistencia.
- **Herramientas disponibles:** PHPUnit ^11, factories, DB (MySQL/Postgres/SQLite en CI), y los workflows de GitHub Actions (`tests-mysql.yml`, `tests-postgres.yml`, `tests-sqlite.yml`, `tests-unit-coverage.yml`).
- **Frontend:** al no haber framework de pruebas JS, la integración de UI se valida **server-side** (Feature tests que renderizan Blade) o **manualmente** (caja negra).

---

*Resumen técnico base — Hito 3 (Pruebas de Integración). Curso de Pruebas de Software.*
