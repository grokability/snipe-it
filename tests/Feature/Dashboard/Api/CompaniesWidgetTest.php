<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Passport\Passport;

test('user without any view permission is forbidden', function () {
    Passport::actingAs(User::factory()->create());

    $this->getJson(route('api.dashboard.companies'))
        ->assertForbidden();
});

test('scoped viewer only gets count columns for viewable types', function () {
    Company::factory()->create();

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $row = $this->getJson(route('api.dashboard.companies'))
        ->assertOk()
        ->json('rows.0');

    expect($row)->toHaveKey('accessories_count');
    $this->assertArrayNotHasKey('assets_count', $row);
    $this->assertArrayNotHasKey('consumables_count', $row);
    $this->assertArrayNotHasKey('components_count', $row);
    $this->assertArrayNotHasKey('licenses_count', $row);
    $this->assertArrayNotHasKey('users_count', $row);
});

test('row carries available actions view flag and no extra fields', function () {
    Company::factory()->create();

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $row = $this->getJson(route('api.dashboard.companies'))
        ->assertOk()
        ->json('rows.0');

    // Widget-shape whitelist. Anything beyond these fields is a
    // widening that leaks past what the widget renders. Extend
    // this list intentionally when adding a widget column, not
    // accidentally by extending the transformer.
    $allowed = ['id', 'name', 'tag_color', 'available_actions', 'accessories_count'];
    sort($allowed);
    $actual = collect(array_keys($row))->sort()->values()->all();
    expect($actual)->toBe($allowed);
    expect($row['available_actions']['view'])->toBeFalse();
});

test('admin sees view flag true', function () {
    Company::factory()->create();

    Passport::actingAs(User::factory()->superuser()->create());

    $row = $this->getJson(route('api.dashboard.companies'))
        ->assertOk()
        ->json('rows.0');

    expect($row['available_actions']['view'])->toBeTrue();
});

test('stale sort column for type the viewer cannot see falls back', function () {
    // See LocationsWidgetTest for full rationale.
    Company::factory()->count(2)->create();

    Passport::actingAs(User::factory()->viewComponents()->create());

    $this->getJson(route('api.dashboard.companies', ['sort' => 'accessories_count', 'order' => 'desc']))
        ->assertOk();
});

test('search param filters rows but other params are ignored', function () {
    // Issue #19979: `search` was ignored, which broke the dashboard
    // filter field. Everything else still is ignored (the index
    // controller handles per-column filters, the dashboard widget
    // only wires the free-text search field).
    Company::factory()->count(3)->create();

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $unfilteredTotal = $this->getJson(route('api.dashboard.companies'))
        ->assertOk()
        ->json('total');

    $searchHonoredTotal = $this->getJson(route('api.dashboard.companies', ['search' => 'never-matches-anything']))
        ->assertOk()
        ->json('total');
    expect($searchHonoredTotal)->toBe(0, 'search param must filter the widget');

    $extraParamsIgnoredTotal = $this->getJson(route('api.dashboard.companies', [
        'filter' => 'never-matches',
        'name' => 'never-matches',
        'email' => 'never-matches',
        'created_by' => 999_999,
        'tag_color' => '#ffffff',
    ]))
        ->assertOk()
        ->json('total');
    expect($extraParamsIgnoredTotal)->toBe($unfilteredTotal, 'non-search filter params are not wired on the widget');
});
