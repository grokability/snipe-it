<?php

use App\Models\Asset;
use App\Models\User;

test('user with edit but no delete cannot bulk restore', function () {
    $asset = Asset::factory()->deleted()->create();

    $user = User::factory()->viewAssets()->editAssets()->create();

    $this->actingAs($user)
        ->post(route('hardware/bulkrestore'), [
            'ids' => [$asset->id],
        ])
        ->assertForbidden();

    $asset->refresh();
    expect($asset->deleted_at)->not->toBeNull('Asset must remain soft-deleted after a forbidden restore attempt.');
});

test('user with only view cannot bulk restore', function () {
    $asset = Asset::factory()->deleted()->create();

    $user = User::factory()->viewAssets()->create();

    $this->actingAs($user)
        ->post(route('hardware/bulkrestore'), [
            'ids' => [$asset->id],
        ])
        ->assertForbidden();

    $asset->refresh();
    expect($asset->deleted_at)->not->toBeNull();
});

test('user with delete permission can bulk restore', function () {
    $asset = Asset::factory()->deleted()->create();

    $user = User::factory()->viewAssets()->deleteAssets()->create();

    $this->actingAs($user)
        ->post(route('hardware/bulkrestore'), [
            'ids' => [$asset->id],
        ])
        ->assertRedirect(route('hardware.index'));

    $asset->refresh();
    expect($asset->deleted_at)->toBeNull('Asset should be restored (deleted_at cleared) after an authorized bulk restore.');
});

test('bulk restore skips nonexistent ids instead of 500', function () {
    // Prior code did Asset::withTrashed()->find($id)->restore() with no
    // null guard, so any invalid or forged ID crashed the whole request.
    $realAsset = Asset::factory()->deleted()->create();

    $user = User::factory()->viewAssets()->deleteAssets()->create();

    $this->actingAs($user)
        ->post(route('hardware/bulkrestore'), [
            'ids' => [$realAsset->id, 99999999],
        ])
        ->assertRedirect(route('hardware.index'));

    $realAsset->refresh();
    expect($realAsset->deleted_at)->toBeNull();
});
