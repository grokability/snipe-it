<?php

use App\Models\Asset;
use App\Models\User;

test('checking in asset by tag requires correct permission', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => $asset->asset_tag])
        ->assertForbidden();
});

test('asset can be checked in by tag', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => $asset->asset_tag])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->assignedTo)->toBeNull();
});

test('checkin by tag returns error for unknown tag', function () {
    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => 'DOES-NOT-EXIST'])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('asset name is cleared on checkin by tag when clear name is set', function () {
    $asset = Asset::factory()->assignedToUser()->create(['name' => 'My Asset Name']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), [
            'asset_tag' => $asset->asset_tag,
            'clear_name' => '1',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->name)->toBeNull();
});

test('asset name is not cleared on checkin by tag when clear name is not set', function () {
    $asset = Asset::factory()->assignedToUser()->create(['name' => 'My Asset Name']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => $asset->asset_tag])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->name)->toEqual('My Asset Name');
});

test('asset can be checked in by asset tag via checkin key', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), [
            'checkin_key' => $asset->asset_tag,
            'checkin_by_field' => 'asset_tag',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->assignedTo)->toBeNull();
});

test('asset can be checked in by serial when unique serial is enabled', function () {
    $this->settings->set(['unique_serial' => '1']);

    $asset = Asset::factory()->assignedToUser()->create(['serial' => 'SN-CHECKIN-BY-SERIAL-1']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), [
            'checkin_key' => 'SN-CHECKIN-BY-SERIAL-1',
            'checkin_by_field' => 'serial',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->assignedTo)->toBeNull();
});

test('checkin by serial is refused when unique serial is disabled', function () {
    // Guard rail: when serial uniqueness is not enforced, serial-based checkin
    // must not resolve (matches the audit-side behavior).
    $this->settings->set(['unique_serial' => '0']);

    $asset = Asset::factory()->assignedToUser()->create(['serial' => 'SN-DISABLED-1']);

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), [
            'checkin_key' => 'SN-DISABLED-1',
            'checkin_by_field' => 'serial',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    // Asset stays assigned because no lookup should have resolved it.
    expect($asset->refresh()->assignedTo)->not->toBeNull();
});

test('legacy asset tag body field still works for backward compat', function () {
    // Callers that predate the checkin_key convention (older integrations,
    // the pre-serial quickscan-checkin blade) send `asset_tag` in the body.
    // That path must keep working after the resolver refactor.
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAsForApi(User::factory()->checkinAssets()->create())
        ->postJson(route('api.asset.checkinbytag'), [
            'asset_tag' => $asset->asset_tag,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->refresh()->assignedTo)->toBeNull();
});
