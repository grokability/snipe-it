<?php

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Laravel\Passport\Passport;

test('user without any view permission is forbidden', function () {
    Passport::actingAs(User::factory()->create());

    $this->getJson(route('api.dashboard.activity'))
        ->assertForbidden();
});

test('scoped viewer sees only activity for viewable types', function () {
    $asset = Asset::factory()->create();
    $accessory = Accessory::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
        'note' => 'ASSET_ROW_SENTINEL',
    ]);
    Actionlog::factory()->create([
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkout',
        'note' => 'ACCESSORY_ROW_SENTINEL',
    ]);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $body = $this->getJson(route('api.dashboard.activity'))
        ->assertOk()
        ->getContent();

    $this->assertStringContainsString('ACCESSORY_ROW_SENTINEL', $body);
    $this->assertStringNotContainsString('ASSET_ROW_SENTINEL', $body);
});

test('activity view holder sees every type without filtering', function () {
    // Give the caller viewAccessories (so they pass the widget's
    // canViewUsersAndCheckoutables entry gate) plus canViewReports
    // (which resolves Gate::allows('activity.view') to true). The
    // activity.view branch skips the viewable-type filter, so an
    // Asset row should appear even though this user cannot view
    // assets directly.
    $asset = Asset::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
        'note' => 'ASSET_ROW_SENTINEL',
    ]);

    Passport::actingAs(User::factory()->viewAccessories()->canViewReports()->create());

    $this->getJson(route('api.dashboard.activity'))
        ->assertOk()
        ->assertSee('ASSET_ROW_SENTINEL');
});

test('limit caps regardless of query string', function () {
    // The widget defaults to 25. The report page (which also
    // reads from this endpoint for scoped viewers) can page
    // deeper. Cap is 500. A hostile caller asking for 10k rows
    // is still capped.
    Actionlog::factory()->count(510)->create([
        'item_type' => Accessory::class,
        'item_id' => Accessory::factory()->create()->id,
        'action_type' => 'checkout',
    ]);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $rows = $this->getJson(route('api.dashboard.activity', ['limit' => 10_000]))
        ->assertOk()
        ->json('rows');

    expect(count($rows))->toBeLessThanOrEqual(500);
});

test('per attribute probe params are ignored', function () {
    // Probe attempt: scoped viewer trying to narrow to a
    // specific action_type / created_by / filter attribute. The
    // widget does not need those, so the endpoint ignores them.
    // Same-caller totals should be identical with and without
    // the probe. Free-text `search` is deliberately excluded from
    // this test since it is supported (see the search test below).
    $accessory = Accessory::factory()->create();
    Actionlog::factory()->count(3)->create([
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkout',
    ]);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $unfiltered = $this->getJson(route('api.dashboard.activity'))
        ->assertOk()
        ->json('total');
    $withProbe = $this->getJson(route('api.dashboard.activity', [
        'filter' => 'never-matches-anything',
        'action_type' => 'delete',
        'created_by' => 999_999,
        'action_source' => 'never-matches',
        'remote_ip' => '9.9.9.9',
        'item_type' => 'App\Models\Asset',
    ]))
        ->assertOk()
        ->json('total');

    expect($withProbe)->toBe($unfiltered);
});

test('search narrows within viewable scope', function () {
    // Search runs against Actionlog's Searchable trait AFTER
    // the viewable-type whereIn filter, so a scoped viewer
    // cannot use it to reach rows they were not already
    // allowed to see. Also proves search actually works
    // (the on-page UX would be broken if search were a no-op).
    $accessory = Accessory::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkout',
        'note' => 'MATCH_ME_SPECIFICALLY',
    ]);
    Actionlog::factory()->create([
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkout',
        'note' => 'other note',
    ]);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $matched = $this->getJson(route('api.dashboard.activity', ['search' => 'MATCH_ME_SPECIFICALLY']))
        ->assertOk()
        ->json('total');
    expect($matched)->toBe(1);
});

test('search matches target asset tag for component checkout row', function () {
    // Regression pin for the Actionlog target-search gap:
    // component checkouts store item=Component + target=Asset,
    // and the trait's searchRelations only walks item_id. The
    // Actionlog::advancedTextSearch() override adds a
    // polymorphic target search so an asset tag entered in the
    // search box finds component-checkout rows targeted at that
    // asset. Uses a positive-membership assertion because
    // Actionlog's built-in searchRelations may also match the
    // asset's own create log via the item_id-keyed relations,
    // and both matches are legitimate.
    $component = Component::factory()->create();
    $asset = Asset::factory()->create(['asset_tag' => 'TAG-DASH-9977']);

    $checkoutLog = Actionlog::factory()->create([
        'item_type' => Component::class,
        'item_id' => $component->id,
        'target_type' => Asset::class,
        'target_id' => $asset->id,
        'action_type' => 'checkout',
    ]);

    Passport::actingAs(User::factory()->viewComponents()->create());

    $ids = collect($this->getJson(route('api.dashboard.activity', ['search' => 'TAG-DASH-9977']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    expect($ids)->toContain($checkoutLog->id);
});

test('search cannot reach rows the caller could not view unfiltered', function () {
    // The type-filter runs before search, so a scoped viewer
    // searching for a substring that matches an Asset-typed row
    // still gets zero results.
    $asset = Asset::factory()->create();
    $accessory = Accessory::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
        'note' => 'CROSS_TYPE_SENTINEL',
    ]);
    Actionlog::factory()->create([
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkout',
        'note' => 'other note',
    ]);

    Passport::actingAs(User::factory()->viewAccessories()->create());

    $matched = $this->getJson(route('api.dashboard.activity', ['search' => 'CROSS_TYPE_SENTINEL']))
        ->assertOk()
        ->json('total');
    expect($matched)->toBe(0);
});
