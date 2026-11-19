<?php

use App\Models\Location;
use App\Models\User;
use Laravel\Passport\Passport;

test('user without any view permission is forbidden', function () {
    Passport::actingAs(User::factory()->create());

    $this->getJson(route('api.dashboard.locations'))
        ->assertForbidden();
});

test('scoped viewer only gets count columns for viewable types', function () {
    Location::factory()->create();

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $row = $this->getJson(route('api.dashboard.locations'))
        ->assertOk()
        ->json('rows.0');

    expect($row)->toHaveKey('accessories_count');
    $this->assertArrayNotHasKey('assets_count', $row);
    $this->assertArrayNotHasKey('assigned_assets_count', $row);
    $this->assertArrayNotHasKey('consumables_count', $row);
    $this->assertArrayNotHasKey('components_count', $row);
    $this->assertArrayNotHasKey('users_count', $row);
});

test('asset viewer gets both asset count columns', function () {
    Location::factory()->create();

    Passport::actingAs(User::factory()->viewAssets()->create());

    $row = $this->getJson(route('api.dashboard.locations'))
        ->assertOk()
        ->json('rows.0');

    expect($row)->toHaveKey('assets_count');
    expect($row)->toHaveKey('assigned_assets_count');
});

test('row carries available actions view flag', function () {
    Location::factory()->create();

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $row = $this->getJson(route('api.dashboard.locations'))
        ->assertOk()
        ->json('rows.0');

    expect($row['available_actions']['view'])->toBeFalse();
});

test('stale sort column for type the viewer cannot see falls back', function () {
    // Regression: bs-table persists the last-clicked sort column
    // in localStorage, which can name a *_count field the current
    // caller does not have. Before the allowlist tracked the
    // withCount list, that URL blew up with a 42S22 unknown-column
    // error mid-render. Falling back to the default (name) keeps
    // the widget alive.
    Location::factory()->count(2)->create();

    Passport::actingAs(User::factory()->viewComponents()->create());

    $this->getJson(route('api.dashboard.locations', ['sort' => 'accessories_count', 'order' => 'desc']))
        ->assertOk();
});

test('search param filters rows but other params are ignored', function () {
    // Issue #19979: `search` was ignored. Everything else still is.
    Location::factory()->count(3)->create();

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $unfilteredTotal = $this->getJson(route('api.dashboard.locations'))
        ->assertOk()
        ->json('total');

    $searchHonoredTotal = $this->getJson(route('api.dashboard.locations', ['search' => 'never-matches']))
        ->assertOk()
        ->json('total');
    expect($searchHonoredTotal)->toBe(0);

    $extraParamsIgnoredTotal = $this->getJson(route('api.dashboard.locations', [
        'filter' => 'never-matches',
        'name' => 'never-matches',
        'city' => 'never-matches',
        'created_by' => 999_999,
    ]))
        ->assertOk()
        ->json('total');
    expect($extraParamsIgnoredTotal)->toBe($unfilteredTotal);
});
