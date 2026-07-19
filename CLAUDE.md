# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **PHP 8.2+** / **Laravel 12** (framework), **Vite 8** (`vite.config.js`) for frontend assets — replaces laravel-mix as of the Vite migration
- **AdminLTE 2** / **Bootstrap 3** UI — Blade views, jQuery-driven with select Livewire components
- **Chart.js v2.9.4** — static-copied to `public/build/vendor/Chart.min.js` by the vite-plugin-static-copy target; use `horizontalBar` type (v2 API, not v3)

## Common Commands

```bash
# Run all tests
php artisan test
# or
vendor/bin/phpunit

# Run a single test file
php artisan test tests/Feature/Assets/AssetsTest.php

# Run a specific test method
php artisan test --filter testSomeMethod

# Vite dev server (HMR — supersedes `mix watch`)
npm run dev

# Production build (writes hashed assets + manifest to public/build/)
npm run build

# Tinker / REPL
php artisan tinker

# Clear caches after config/route changes
php artisan optimize:clear
```

## Frontend build layout

Vite entry files live under `resources/assets/`:

- `resources/assets/js/app.js` — main JS entry. Establishes `window.jQuery`
  and `window.moment` globals BEFORE any plugin imports, then imports
  Bootstrap 3, AdminLTE, select2, colorpicker, eonasdan-bootstrap-datetimepicker,
  ekko-lightbox, signature pad, jquery-validation, list.js, clipboard,
  canvas-confetti, then `./snipeit.js` and `./snipeit_modals.js`.
- `resources/assets/less/vite-main.less` — CSS entry. `@import`s bootstrap
  → fontawesome → AdminLTE.less → widget CSS → app.less → select2 → overrides.less
  in that order (order matters; overrides.less is last on purpose).
- `resources/assets/js/bootstrap-table.js` + `vite-bootstrap-table.less` —
  separate bundle for the bootstrap-table stack (+ 8 extensions + jsPDF +
  DejaVu font loader). Loaded only on pages that include
  `resources/views/partials/bootstrap-table.blade.php`.

Blade layouts consume the bundles via the `@vite([...])` directive.

Static passthroughs (kept out of the JS bundles):

- `public/build/vendor/Chart.min.js` — Chart.js is a UMD blob, only used on
  the dashboard + reports/index. Copied by `vite-plugin-static-copy` from
  `node_modules/chart.js/dist/`.
- `public/build/select2/i18n/*.js` — one locale JS file per language. The
  default layout loads the current locale at render time via a plain
  `<script src>`.

Dev server is served via **Laravel Herd** (`herd coverage` for coverage reports).

## Architecture

### Controllers

Two parallel controller trees:
- `app/Http/Controllers/` — web/UI controllers (Blade views)
- `app/Http/Controllers/Api/` — REST API controllers (JSON, used by datatables + select2)

Subdirectory groupings: `Assets/`, `Licenses/`, `Users/`, `Accessories/`, `Consumables/`, `Components/`, `Kits/`, `Account/`, `Auth/`

### API Pattern

Every API controller returns data via a **Transformer** (`app/Http/Transformers/`). Never return raw model attributes from API controllers — always pass through the transformer. `DatatablesTransformer` wraps paginated results.

```php
return (new AssetsTransformer)->transformAssets($assets, $assets->count());
```

### Authorization

All authorization goes through **Policies** (`app/Policies/`). `CheckoutablePermissionsPolicy` is the base for assets/licenses/accessories/consumables — its `checkout()` / `checkin()` methods accept `$item = null` so you can use `@can('checkout', \App\Models\Asset::class)` without an instance.

### FMCS (Full Multiple Company Support)

`Setting::getSettings()->full_multiple_companies_support == '1'` gates company-scoped filtering. The select2 API endpoints (`selectlist()` methods) accept a `companyId` query param — apply it like this:

```php
if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($request->filled('companyId'))) {
    $query->where('table.company_id', $request->input('companyId'));
}
```

Pass `data-company-id="{{ $user->company_id }}"` in Blade to wire it to select2.

### Select2 AJAX Dropdowns

Use `class="js-data-ajax"` with `data-endpoint="hardware|licenses|consumables|..."`. `snipeit.js` auto-initializes these, forwarding `data-company-id` as `companyId` and `data-asset-status-type` as `statusType` to the API.

### Routes

All routes are in `routes/web.php` (UI) and `routes/api.php` (API). Breadcrumbs are defined inline using `->breadcrumbs(fn (Trail $trail) => ...)` from `tabuna/breadcrumbs`. Every UI route should have a breadcrumb.

Note: the `reports/unaccepted_assets` route is named with slashes, not dots — use `route('reports/unaccepted_assets')`.

### Translations

String keys live in `resources/lang/en-US/general.php` (and other files in that directory). Always add new UI strings as translation keys rather than hard-coding English.

### Checkout Redirect Flow

After checkout, `Helper::getRedirectOption()` reads `$request->redirect_option`. For redirecting back to the assigned user after checkout:
- Set `redirect_option=target` in the form
- Set `checkout_to_type=user` in the form
- Set `assigned_user={{ $user->id }}` in the form

### Key Helper Methods (`app/Helpers/Helper.php`)

- `Helper::deployableStatusLabelList()` — status labels for checkout forms
- `Helper::defaultChartColors()` — 10-color palette used in charts
- `Helper::getRedirectOption($request, $id, $table)` — post-checkout redirect logic

### Global View Variables

`$snipeSettings` is injected into all views via a service provider — no need to pass `Setting::getSettings()` from every controller. Use it directly in Blade.

## Testing

Tests live in `tests/Feature/` (organized by entity) and `tests/Unit/`. Feature tests hit the database; the test environment uses `array` cache/session/mail drivers. Tests use factories for data setup.
