<?php

use App\Models\Asset;
use App\Models\User;

test('regular user without view assets permission sees own assets', function () {
    // Regression coverage for GH #19709: a user with no view-assets
    // permission would see an empty print page for their own inventory,
    // because the blade class-level @can gate blocked their own items.
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $this->actingAs($user)
        ->get(route('profile.print'))
        ->assertOk()
        ->assertSee($asset->asset_tag);
});
