<?php

use App\Models\Accessory;
use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Passport;

test('user without any view permission is forbidden', function () {
    Passport::actingAs(User::factory()->create());

    $this->getJson(route('api.dashboard.categories'))
        ->assertForbidden();
});

test('scoped viewer sees only matching category types', function () {
    Category::factory()->create(['name' => 'Asset Cat', 'category_type' => 'asset']);
    Category::factory()->create(['name' => 'Accessory Cat', 'category_type' => 'accessory']);
    Category::factory()->create(['name' => 'License Cat', 'category_type' => 'license']);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $rows = $this->getJson(route('api.dashboard.categories'))
        ->assertOk()
        ->json('rows');
    $names = collect($rows)->pluck('name')->all();

    expect($names)->toContain('Accessory Cat');
    expect($names)->not->toContain('Asset Cat');
    expect($names)->not->toContain('License Cat');
});

test('scoped viewer only gets count columns for viewable types', function () {
    Category::factory()->create(['category_type' => 'accessory']);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $row = $this->getJson(route('api.dashboard.categories'))
        ->assertOk()
        ->json('rows.0');

    expect($row)->toHaveKey('accessories_count');
    $this->assertArrayNotHasKey('assets_count', $row);
    $this->assertArrayNotHasKey('consumables_count', $row);
    $this->assertArrayNotHasKey('components_count', $row);
    $this->assertArrayNotHasKey('licenses_count', $row);
});

test('row carries available actions view flag', function () {
    Category::factory()->create(['category_type' => 'accessory']);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $row = $this->getJson(route('api.dashboard.categories'))
        ->assertOk()
        ->json('rows.0');

    expect($row['available_actions']['view'])->toBeFalse();
});

test('stale sort column for type the viewer cannot see falls back', function () {
    // See LocationsWidgetTest for full rationale. localStorage-
    // persisted bs-table sort state can name a *_count column the
    // current caller does not have. Falling back to name keeps
    // the widget rendering.
    Category::factory()->count(2)->create(['category_type' => 'component']);

    Passport::actingAs(User::factory()->viewComponents()->create());

    $this->getJson(route('api.dashboard.categories', ['sort' => 'accessories_count', 'order' => 'desc']))
        ->assertOk();
});

test('search param filters rows but other params are ignored', function () {
    // Issue #19979: `search` was ignored. Everything else still is.
    Category::factory()->count(3)->create(['category_type' => 'accessory']);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $unfilteredTotal = $this->getJson(route('api.dashboard.categories'))
        ->assertOk()
        ->json('total');

    $searchHonoredTotal = $this->getJson(route('api.dashboard.categories', ['search' => 'never-matches-anything']))
        ->assertOk()
        ->json('total');
    expect($searchHonoredTotal)->toBe(0);

    $extraParamsIgnoredTotal = $this->getJson(route('api.dashboard.categories', [
        'filter' => 'never-matches-anything',
        'name' => 'never-matches-anything',
        'created_by' => 999_999,
    ]))
        ->assertOk()
        ->json('total');
    expect($extraParamsIgnoredTotal)->toBe($unfilteredTotal);
});

test('limit caps at fifty', function () {
    Accessory::factory()->count(0)->create();
    Category::factory()->count(60)->create(['category_type' => 'accessory']);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $rows = $this->getJson(route('api.dashboard.categories', ['limit' => 500]))
        ->assertOk()
        ->json('rows');

    expect(count($rows))->toBeLessThanOrEqual(50);
});
