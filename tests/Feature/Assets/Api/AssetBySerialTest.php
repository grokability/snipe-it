<?php

use App\Models\Asset;
use App\Models\User;

test('returns asset by serial', function () {
    $asset = Asset::factory()->create(['serial' => 'TEST-API-SERIAL-123']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'TEST-API-SERIAL-123']))
        ->assertOk()
        ->assertJsonFragment(['serial' => 'TEST-API-SERIAL-123'])
        ->assertJsonStructure(['total', 'rows']);
});

test('returns multiple assets with same serial', function () {
    Asset::factory()->count(3)->create(['serial' => 'DUPE-SERIAL']);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'DUPE-SERIAL']))
        ->assertOk();

    expect($response->json('total'))->toEqual(3);
});

test('returns error when serial not found', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'DOES-NOT-EXIST']))
        ->assertOk()
        ->assertJson(['status' => 'error']);
});

test('requires permission', function () {
    Asset::factory()->create(['serial' => 'TEST-API-SERIAL-AUTH']);

    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'TEST-API-SERIAL-AUTH']))
        ->assertForbidden();
});

test('does not return deleted assets by default', function () {
    $asset = Asset::factory()->create(['serial' => 'DELETED-SERIAL']);
    $asset->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'DELETED-SERIAL']))
        ->assertOk()
        ->assertJson(['status' => 'error']);
});

test('returns deleted assets when requested', function () {
    $asset = Asset::factory()->create(['serial' => 'DELETED-SERIAL-2']);
    $asset->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'DELETED-SERIAL-2']).'?deleted=true')
        ->assertOk()
        ->assertJsonFragment(['serial' => 'DELETED-SERIAL-2']);
});

test('serial with slashes works in path', function () {
    $asset = Asset::factory()->create(['serial' => 'SN/WITH/SLASHES']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show.byserial', ['any' => 'SN/WITH/SLASHES']))
        ->assertOk()
        ->assertJsonFragment(['serial' => 'SN/WITH/SLASHES']);
});
