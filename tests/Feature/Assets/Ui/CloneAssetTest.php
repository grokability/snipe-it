<?php

use App\Models\Asset;
use App\Models\User;

test('permission required to clone asset', function () {
    $asset = Asset::factory()->create();
    $this->actingAs(User::factory()->create())
        ->get(route('clone/hardware', $asset))
        ->assertForbidden();
});

test('page can be accessed', function () {
    $asset = Asset::factory()->create();
    $response = $this->actingAs(User::factory()->cloneAssets()->create())
        ->get(route('clone/hardware', $asset));
    $response->assertStatus(200);
});

test('asset can be cloned', function () {
    $asset_to_clone = Asset::factory()->create(['name' => 'Asset to clone']);
    $this->actingAs(User::factory()->cloneAssets()->create())
        ->get(route('clone/hardware', $asset_to_clone))
        ->assertOk()
        ->assertSee([
            'Asset to clone',
        ], false);
});

test('create permission alone is not enough to clone asset', function () {
    // Cloning renders the source asset's data into the create form, so
    // a user with only assets.create (no assets.view) must be blocked.
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->createAssets()->create())
        ->get(route('clone/hardware', $asset))
        ->assertForbidden();
});
