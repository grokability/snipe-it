<?php

use App\Models\Asset;
use App\Models\User;

test('barcode route requires asset view permission', function () {
    // Reg-test for the legacy barcode endpoint's missing authz:
    // before the fix, any authenticated user could pull the barcode
    // PNG for any asset regardless of permissions or company scope,
    // enumerating protected asset tags via the rendered barcode.
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('barcode/hardware', $asset->id))
        ->assertForbidden();
});

test('barcode route allows asset viewer', function () {
    // Baseline: a user with assets.view still succeeds. Guards
    // against the authz gate over-reaching and breaking legitimate
    // access.
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->viewAssets()->create())
        ->get(route('barcode/hardware', $asset->id))
        ->assertOk();
});
