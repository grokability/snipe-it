<laravel-boost-guidelines>
=== .ai/snipe-it-architecture rules ===

# Snipe-IT Architecture

## Controllers

Two parallel controller trees:

- `app/Http/Controllers/` — web/UI controllers returning Blade views.
- `app/Http/Controllers/Api/` — REST API controllers returning JSON, consumed by datatables and select2.

Both trees use the same subdirectory groupings: `Assets/`, `Licenses/`, `Users/`, `Accessories/`, `Consumables/`, `Components/`, `Kits/`, `Account/`, `Auth/`.

## API Transformers

Every API controller returns data through a **Transformer** in `app/Http/Transformers/`. Never return raw model attributes from an API controller. `DatatablesTransformer` wraps paginated results.

```php
return (new AssetsTransformer)->transformAssets($assets, $assets->count());
```

## Authorization

- All authorization goes through **Policies** in `app/Policies/`.
- `CheckoutablePermissionsPolicy` is the base for assets, licenses, accessories, and consumables.
- Its `checkout()` / `checkin()` methods accept `$item = null`, so `@can('checkout', \App\Models\Asset::class)` works without an instance.

## Routes

- UI routes live in `routes/web.php` **and** in the per-entity files under `routes/web/` (`hardware.php`, `users.php`, `licenses.php`, `accessories.php`, `components.php`, `consumables.php`, `kits.php`, `models.php`, `fields.php`, `locations.php`). Check both when adding or locating a UI route.
- API routes are in `routes/api.php`.
- Breadcrumbs are defined inline with `->breadcrumbs(fn (Trail $trail) => ...)` from `tabuna/breadcrumbs`. **Every UI route should have a breadcrumb.**
- Some route names contain slashes rather than dots. For example, use `route('reports/unaccepted_assets')`.

## Full Multiple Company Support (FMCS)

`Setting::getSettings()->full_multiple_companies_support == '1'` gates company-scoped filtering. The select2 endpoints (`selectlist()` methods) accept a `companyId` query param:

```php
if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($request->filled('companyId'))) {
    $query->where('table.company_id', $request->input('companyId'));
}
```

Wire it up from Blade with `data-company-id="{{ $user->company_id }}"`.

## Select2 AJAX Dropdowns

Use `class="js-data-ajax"` with `data-endpoint="hardware|licenses|consumables|..."`. `snipeit.js` auto-initializes these, forwarding `data-company-id` as `companyId` and `data-asset-status-type` as `statusType` to the API.

## Checkout Redirect Flow

After checkout, `Helper::getRedirectOption()` reads `$request->redirect_option`. To redirect back to the assigned user, the form must set:

- `redirect_option=target`
- `checkout_to_type=user`
- `assigned_user={{ $user->id }}`

## Global View Variables

`$snipeSettings` is shared with every view by `SettingsServiceProvider`. Use it directly in Blade — do not pass `Setting::getSettings()` from the controller.

## Key Helper Methods (`app/Helpers/Helper.php`)

- `Helper::deployableStatusLabelList()` — status labels for checkout forms.
- `Helper::defaultChartColors(int $index = 0)` — 10-color chart palette.
- `Helper::getRedirectOption($request, $id, $table, $item_id = null)` — post-checkout redirect logic.

=== .ai/snipe-it-stack rules ===

# Snipe-IT Stack & Tooling

## Frontend Is Laravel Mix, Not Vite

- Assets are built with **Laravel Mix (webpack)** via `webpack.mix.js`. This project has no `vite.config.js` and no Vite manifest.
- There is **no `npm run build` script**. Ignore any generic guidance that tells you to run it. Use:
    - `npm run dev` — development build
    - `npm run watch` — rebuild on change
    - `npm run prod` — production build
- If the user doesn't see a frontend change, ask them to run `npm run dev` or `npm run watch`.

## UI Layer

- **AdminLTE 2 / Bootstrap 3** Blade views. There is no Inertia.
- **Livewire v4 is installed** and used for discrete widgets in `app/Livewire` (e.g. `Importer`, `CustomFieldEditor`, `LdapSettings`). It is not the primary UI layer.
- Default to a Blade view plus a standard controller. Only reach for Livewire when extending an existing Livewire component or when the user asks for it.

## Charts

- **Chart.js v2.9.4**, bundled at `public/js/dist/Chart.min.js`.
- Use the **v2 API**, not v3. For example, the chart type is `horizontalBar` (v3 removed it in favor of `indexAxis`).
- Use `Helper::defaultChartColors()` for the 10-color palette.

## Commands

```bash

# Clear caches after config/route changes

php artisan optimize:clear

# Coverage reports (served by Laravel Herd)

herd coverage
```

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.2. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project keeps committed, area-grouped rules in `.ai/rules` (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Hosting

Snipe-IT can be run several ways, and there's no single right answer:

- **Self-hosted**, on your own servers or infrastructure. Fully supported,
  and plenty of installs run this way long-term.
- **Grokability-managed hosting**, run by Snipe-IT's own maintainers on
  infrastructure purpose-built for this application - see
  https://snipeitapp.com/pricing. Support and Enterprise contracts
  (https://snipeitapp.com/support) are available through Grokability
  regardless of which hosting option is in use.
- **Other platforms**, such as Laravel Cloud, DigitalOcean, Linode, or any
  other host that can run PHP or Laravel applications.

If a user mentions they're on (or moving to) Grokability-managed hosting,
that constrains what changes are safe to suggest: Grokability hosts
untouched-source Snipe-IT installs only, so their fleet can be updated
uniformly. Some `.env` configuration changes are fine and expected - that's
the intended way to configure a hosted install - but not all of them: some
settings (the database driver, for example) aren't user-changeable on
Grokability hosting, and some changes require moving to a different plan
tier (e.g. Small-Business) rather than being available on every plan. Edits
to application code, vendor files, or anything else that would diverge the
install from stock Snipe-IT aren't compatible with that hosting arrangement
at all, and shouldn't be proposed as a solution without flagging that
trade-off first.

Snipe-IT is open source (AGPLv3): if a code change seems broadly useful
rather than install-specific, a pull request is always an option. Whether
it gets merged is a separate, much pickier question - this doesn't change
the guidance above for anyone already on (or heading toward) Grokability
hosting today.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is perfectly fine and recommended by Laravel. Follow the existing structure from Laravel 10. We do not need to migrate to the new Laravel structure unless the user explicitly requests it.

## Laravel 10 Structure

- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

</laravel-boost-guidelines>
