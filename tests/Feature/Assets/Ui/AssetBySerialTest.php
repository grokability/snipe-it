<?php

use App\Models\Asset;
use App\Models\User;

test('redirects to asset when serial in path', function () {
    $asset = Asset::factory()->create(['serial' => 'TEST-SERIAL-123']);
    $user = User::factory()->viewAssets()->create();

    $this->actingAs($user)
        ->get(route('findbyserial/hardware', ['any' => 'TEST-SERIAL-123']))
        ->assertRedirectToRoute('hardware.show', $asset->id);
});

test('redirects to asset when serial in query string', function () {
    $asset = Asset::factory()->create(['serial' => 'TEST-SERIAL-456']);
    $user = User::factory()->viewAssets()->create();

    $this->actingAs($user)
        ->get(route('findbyserial/hardware').'?serial=TEST-SERIAL-456')
        ->assertRedirectToRoute('hardware.show', $asset->id);
});

test('redirects to index when serial not found', function () {
    $user = User::factory()->viewAssets()->create();

    $this->actingAs($user)
        ->get(route('findbyserial/hardware', ['any' => 'DOES-NOT-EXIST']))
        ->assertRedirectToRoute('hardware.index');
});

test('requires permission', function () {
    Asset::factory()->create(['serial' => 'TEST-SERIAL-789']);

    $this->actingAs(User::factory()->create())
        ->get(route('findbyserial/hardware', ['any' => 'TEST-SERIAL-789']))
        ->assertForbidden();
});
