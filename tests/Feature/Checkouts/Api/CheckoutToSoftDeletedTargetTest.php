<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\User;

test('asset checkout rejects soft deleted user target', function () {
    $asset = Asset::factory()->create();
    $targetUser = User::factory()->create();
    $targetUser->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $targetUser->id,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('assets', [
        'id' => $asset->id,
        'assigned_to' => $targetUser->id,
    ]);
});

test('asset checkout rejects soft deleted asset target', function () {
    $asset = Asset::factory()->create();
    $targetAsset = Asset::factory()->create();
    $targetAsset->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'asset',
            'assigned_asset' => $targetAsset->id,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('assets', [
        'id' => $asset->id,
        'assigned_to' => $targetAsset->id,
        'assigned_type' => Asset::class,
    ]);
});

test('asset checkout rejects soft deleted location target', function () {
    // Reporter flagged this case as "unverified due to FMCS mismatch" but
    // the code path is identical to the other two, so the fix must cover it.
    $asset = Asset::factory()->create();
    $targetLocation = Location::factory()->create();
    $targetLocation->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'location',
            'assigned_location' => $targetLocation->id,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('assets', [
        'id' => $asset->id,
        'assigned_to' => $targetLocation->id,
        'assigned_type' => Location::class,
    ]);
});

test('consumable checkout rejects soft deleted user target', function () {
    $consumable = Consumable::factory()->create();
    $target = User::factory()->create();
    $target->delete();

    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $target->id,
            'checkout_qty' => 1,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('consumables_users', [
        'consumable_id' => $consumable->id,
        'assigned_to' => $target->id,
    ]);
});

test('component checkout rejects soft deleted asset target', function () {
    $component = Component::factory()->create();
    $target = Asset::factory()->create();
    $target->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.components.checkout', $component->id), [
            'assigned_to' => $target->id,
            'assigned_qty' => 1,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('components_assets', [
        'component_id' => $component->id,
        'asset_id' => $target->id,
    ]);
});

test('accessory checkout rejects soft deleted user target', function () {
    $accessory = Accessory::factory()->create();
    $target = User::factory()->create();
    $target->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $target->id,
            'checkout_to_type' => 'user',
            'checkout_qty' => 1,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $target->id,
        'assigned_type' => User::class,
    ]);
});

test('asset checkout still works with live user target', function () {
    $asset = Asset::factory()->create();
    $target = User::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $target->id,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => $target->id,
        'assigned_type' => User::class,
    ]);
});

test('consumable checkout still works with live user target', function () {
    $consumable = Consumable::factory()->create();
    $target = User::factory()->create();

    $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $target->id,
            'checkout_qty' => 1,
        ])
        ->assertStatusMessageIs('success');
});
