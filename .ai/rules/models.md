---
paths:
  - 'app/Models/**'
---

# Models

## Models validate themselves with watson/validating
Models carry their own validation: `use Watson\Validating\ValidatingTrait` plus a `protected $rules` array. `$model->save()` returns false when validation fails and `$model->getErrors()` holds the messages.

This is a second layer on top of the Form Request, not a replacement for it.

## Declare a presenter on the model
A model that renders in the UI sets `protected $presenter = \App\Presenters\<Entity>Presenter::class` and `use App\Presenters\Presentable`, exposing `$model->present()`.

## Share model behavior through opt-in traits
Cross-cutting model behavior comes from traits in `app/Models/Traits`, opted into per model: `CompanyableTrait` (FMCS scoping), `Loggable` (action log), `Searchable` (API/datatable search), `Requestable`, `Acceptable`, `HasUploads`.

Add behavior as a trait rather than pushing it into a base class.

## Casts go in the $casts property
Declare casts with `protected $casts = [...]`, not a `casts()` method, even though Laravel 12 supports the method form.

## Prefer builder classes over inline `scope*` methods

Query scopes belong in a dedicated builder class under `App\Models\Builders\<Model>QueryBuilder`, not as `scope*` methods on the model. The builder extends `Illuminate\Database\Eloquent\Builder`, declares each scope as a plain method (`active()`, `orderByCreatedBy(string $order)`, etc.), and the model wires it in via a `newEloquentBuilder` override:

```php
public function newEloquentBuilder($query): WidgetQueryBuilder
{
    return new WidgetQueryBuilder($query);
}
```

Benefits: the builder class is small and inspectable, PHPStan / IDE autocomplete see the methods via the ide-helper stub, cross-scope chaining uses `$this` on the builder (so `$this->notArchived()` just works), and the model stays focused on persistence + relationships.

Callsites don't change. Laravel's magic dispatch still routes `Widget::active()->orderByCreatedBy('desc')` through to the builder. PHP method calls are case-insensitive, so legacy `->Active()` CamelCase callers keep working alongside the new `->active()` convention.

A `scope*` method stays on the model (or on a trait) in two cases:

1. **Cross-cutting trait scopes.** When a scope is defined on a shared trait that many models opt into (e.g. `Searchable::scopeTextSearch`, `HasOrders::scopeOrderByOrderNumber`, `Requestable::scopeRequestedBy`), it stays as a `scope*` method on the trait. The trait IS the unification point. Moving it into a parallel builder-side trait that each builder mixes in just adds a second layer with no reduction in indirection.
2. **Needs internal access to a `protected` property the builder can't reach.** For example, `License::scopeTextSearchWithoutSerial` swaps `$this->searchableAttributes` for the duration of a nested call.

Example callsites that still work after a migration:
- Blade: `$user->assets()->AssetsForShow()->count()` (relation proxies the method to the Asset builder)
- `withCount` closure: `fn ($q) => $q->AssetsForShow()` (`$q` is bound to the model, routes to the builder)
- Nested closure: `where(function ($q) { $q->overdueForAudit(); })` (nested builders inherit the model binding)

Pattern reference: `App\Models\Builders\AssetQueryBuilder` (filter + sort + search scopes), `App\Models\Builders\MaintenanceQueryBuilder` (the first adoption).

## New-style Attribute accessors need @return Attribute generics for Larastan
If a model attribute has a new-style accessor (`protected function locationId(): Attribute`), Larastan drops the property entirely unless the method has a generic PHPDoc: `/** @return Attribute<TGet, TSet> */` (e.g. `Attribute<int|null, int|null>`). Without it, the DB-column extension defers to the accessor extension, the accessor extension requires strict generics, and the property reads as "undefined" — that is why `@property` tags crept into model docblocks (e.g. PR #19480). Fix by adding the `@return Attribute<...>` generic instead of `@property` tags; plain columns without accessors are inferred from migrations automatically and need no annotation.

TGet is the readable type; **TSet is the writable one — the setter's argument, i.e. what may be assigned**, not what the setter stores. Reserve `never` for attributes genuinely never written: no `set:` closure **and** nothing assigns to them (computed values like `expiresDiffInDays()`). No `set:` closure alone is not enough — an accessor decorating a real column still gets assigned elsewhere, and `never` turns each assignment into `Property Setting::$header_color (never) does not accept mixed`; use `mixed` there. Same reasoning for a setter that narrows: `requestable()` stores `(int) filter_var(...)` but accepts an untyped `$value`, so it is `Attribute<int, mixed>`.
