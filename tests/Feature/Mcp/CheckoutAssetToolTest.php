<?php

use App\Mcp\Tools\CheckoutAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutAssets()->create());
});

function checkoutAssetTool(array $args): ResponseFactory
{
    return resolve(CheckoutAssetTool::class)->handle(new Request($args));
}

test('checks out asset to user by asset tag', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    $content = checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
    ]);
});

test('checks out asset to user by numeric id', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    $content = checkoutAssetTool([
        'id' => $asset->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('checks out asset to location', function () {
    $asset = Asset::factory()->create();
    $location = Location::factory()->create();

    $content = checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'location',
        'assigned_location' => $location->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => $location->id,
        'assigned_type' => Location::class,
    ]);
});

test('checks out asset to another asset', function () {
    $asset = Asset::factory()->create();
    $target = Asset::factory()->create();

    $content = checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'asset',
        'assigned_asset' => $target->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => $target->id,
        'assigned_type' => Asset::class,
    ]);
});

test('returns error when asset tag not found', function () {
    expect(checkoutAssetTool([
        'asset_tag' => 'DOES-NOT-EXIST',
        'checkout_to_type' => 'user',
        'assigned_user' => User::factory()->create()->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset already checked out', function () {
    $existingUser = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($existingUser)->create();
    $newUser = User::factory()->create();

    expect(checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => $newUser->id,
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => $existingUser->id,
    ]);
});

test('returns error when target user not found', function () {
    $asset = Asset::factory()->create();

    expect(checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => 99999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when target location not found', function () {
    $asset = Asset::factory()->create();

    expect(checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'location',
        'assigned_location' => 99999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('response includes asset tag and target info', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    $content = checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->getStructuredContent();

    expect($content['asset_tag'])->toEqual($asset->asset_tag);
    expect($content['checked_out_to_type'])->toEqual('user');
    expect($content['checked_out_to_id'])->toEqual($user->id);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    expect(checkoutAssetTool([
        'asset_tag' => $asset->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ])->responses()->first()->isError())->toBeTrue();
});
