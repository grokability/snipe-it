# Snipe-IT Custom Development Guide

## Project
- **Snipe-IT** - Open source IT asset management (AGPL-3.0)
- **Framework**: Laravel 11, PHP 8.2+
- **Frontend**: AdminLTE 2 (Bootstrap 3), jQuery, Laravel Mix 6, Livewire 4
- **Auth**: Laravel Passport (API), SAML, Google Socialite, 2FA

## Environment
- **Database**: MySQL 9.6 on Docker, port 3307
- **Dev server**: `php artisan serve --host=127.0.0.1 --port=8000`
- **Build frontend**: `npm run dev` (development), `npm run prod` (production)
- **Testing**: `php artisan test`

## Golden Rules
1. **NEVER modify vendor/ or core app/ files directly** - all customizations must survive `git pull` from upstream
2. Custom logic goes in `App\Custom` namespace (`app/Custom/` directory)
3. Override routes BEFORE core routes in `web.php` only if absolutely necessary
4. Use Observers and Events instead of modifying core Models
5. Use `config()` overrides instead of modifying config files directly
6. New database changes go in NEW migration files only - never edit existing migrations

## Key Models
- `Asset` (extends `Depreciable` extends `SnipeModel`) - central model, polymorphic checkout
- `User`, `License`, `Accessory`, `Component`, `Consumable`
- `AssetModel`, `Category`, `Manufacturer`, `Supplier`, `Location`, `Department`
- `CustomField` / `CustomFieldset` - dynamic fields per asset model
- `Statuslabel` - deployable/archived/pending states

## API
- RESTful at `/api/v1/` with Passport auth + rate limiting
- Key endpoints: `hardware`, `users`, `licenses`, `accessories`, `consumables`, `components`, `models`, `categories`, `locations`

## Architecture & Extension Points

### Files to NEVER modify directly
- `app/Models/Asset.php` and other core models
- `app/Http/Controllers/` (core controllers)
- `app/Providers/AppServiceProvider.php` and other core providers
- `routes/web.php`, `routes/api.php`, `routes/web/*.php`
- `config/*.php` files
- `database/migrations/` (existing files)
- `app/Http/Kernel.php`
- `resources/views/` (core Blade templates)

### Safe extension patterns
1. **ServiceProvider** (`app/Custom/Providers/CustomServiceProvider.php`):
   - Register in `config/app.php` providers array (ONLY file we touch in config)
   - Register custom Observers, Event listeners, route files, view overrides
2. **Observers** - hook into model lifecycle without modifying models
   - Existing observers: Asset, User, License, Accessory, Component, Consumable, Location, Maintenance, Setting
3. **Events** - existing events to listen to:
   - `CheckoutableCheckedOut`, `CheckoutableCheckedIn`
   - `CheckoutAccepted`, `CheckoutDeclined`
   - `CheckoutablesCheckedOutInBulk`, `UserMerged`
4. **Listeners** - existing subscribers: `LogListener`, `CheckoutableListener`
5. **Custom routes** - load from `app/Custom/routes/` via our ServiceProvider
6. **View overrides** - use `loadViewsFrom()` with higher priority namespace
7. **Middleware** - register custom middleware via ServiceProvider
8. **Migrations** - add new files in `database/migrations/` (never edit existing)

### Recommended custom code structure
```
app/Custom/
  Providers/
    CustomServiceProvider.php    # Main entry point, registered in config/app.php
    CustomEventServiceProvider.php
  Models/
    Traits/                      # Custom traits to extend core models
  Observers/                     # Custom observers for core models
  Listeners/                     # Custom event listeners
  Http/
    Controllers/                 # Custom controllers
    Middleware/                   # Custom middleware
    Requests/                    # Custom form requests
  Events/                        # Custom events
  Services/                      # Business logic services
  routes/
    web.php                      # Custom web routes
    api.php                      # Custom API routes
```

### Route loading order (in RouteServiceProvider)
Web routes load: `routes/web/hardware.php` ... `routes/web/kits.php` ... `routes/web.php`
- Custom routes loaded via our ServiceProvider run AFTER core routes
- To override a core route, load custom routes BEFORE core routes (requires RouteServiceProvider change - avoid if possible)

### Model hierarchy
```
Illuminate\Database\Eloquent\Model
  -> SnipeModel (common setters, pagination, EULA)
    -> Depreciable (depreciation calculations)
      -> Asset (checkout/checkin, custom fields, polymorphic assignment)
      -> License
```

### Key traits on models
- `Searchable` - multi-column + relation search
- `Loggable` - audit trail via Actionlog
- `CompanyableTrait` - multi-tenant scoping
- `Acceptable` - checkout acceptance workflow
- `Requestable` - asset request workflow
- `HasUploads` - file upload tracking

### Middleware stack (for reference)
- Global: TrustProxies, SecurityHeaders, CheckForSetup, CheckForDebug
- Web: EncryptCookies, VerifyCsrfToken, CheckLocale, CheckUserIsActivated, CheckForTwoFactor, Passport CreateFreshApiToken
- API: auth:api, CheckLocale
- Route: `authorize` (CheckPermissions), `api-throttle` (SetAPIResponseHeaders)
