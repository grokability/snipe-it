<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Statuslabel;
use App\Models\User;

beforeEach(function () {
    $this->actor = User::factory()->superuser()->create();
    $this->assignedUser = User::factory()->create();
});

test('sets alert on response if enabled by category for accessory', function () {
    $accessory = Accessory::factory()->create();
    $accessory->category->update([
        'require_acceptance' => true,
        'alert_on_response' => true,
    ]);

    postAccessoryCheckout($accessory);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Accessory::class,
        'checkoutable_id' => $accessory->id,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => $this->actor->id,
        'qty' => 1,
    ]);
});

test('does not set alert on response if disabled by category for accessory', function () {
    $accessory = Accessory::factory()->create();
    $accessory->category->update([
        'require_acceptance' => true,
        'alert_on_response' => false,
    ]);

    postAccessoryCheckout($accessory);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Accessory::class,
        'checkoutable_id' => $accessory->id,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => null,
    ]);
});

test('sets alert on response if enabled by category for asset', function () {
    $asset = Asset::factory()->create();
    $asset->model->category->update([
        'require_acceptance' => true,
        'alert_on_response' => true,
    ]);

    postAssetCheckout($asset);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Asset::class,
        'checkoutable_id' => $asset->id,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => $this->actor->id,
        'qty' => 1,
    ]);
});

test('does not set alert on response if disabled by category for asset', function () {
    $asset = Asset::factory()->create();
    $asset->model->category->update([
        'require_acceptance' => true,
        'alert_on_response' => false,
    ]);

    postAssetCheckout($asset);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Asset::class,
        'checkoutable_id' => $asset->id,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => null,
    ]);
});

test('sets alert on response if enabled by category for license', function () {
    $license = License::factory()->create();

    $license->category->update([
        'require_acceptance' => true,
        'alert_on_response' => true,
    ]);

    postLicenseCheckout($license);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => LicenseSeat::class,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => $this->actor->id,
        'qty' => 1,
    ]);
});

test('does not set alert on response if disabled by category for license', function () {
    $license = License::factory()->create();

    $license->category->update([
        'require_acceptance' => true,
        'alert_on_response' => false,
    ]);

    postLicenseCheckout($license);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => LicenseSeat::class,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => null,
    ]);
});

test('sets alert on response if enabled by category for consumable', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);
    $consumable->category->update([
        'require_acceptance' => true,
        'alert_on_response' => true,
    ]);

    postConsumableCheckout($consumable);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Consumable::class,
        'checkoutable_id' => $consumable->id,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => $this->actor->id,
    ]);
});

test('does not set alert on response if disabled by category for consumable', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);
    $consumable->category->update([
        'require_acceptance' => true,
        'alert_on_response' => false,
    ]);

    postConsumableCheckout($consumable);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Consumable::class,
        'checkoutable_id' => $consumable->id,
        'assigned_to_id' => $this->assignedUser->id,
        'alert_on_response_id' => null,
    ]);
});

function postAssetCheckout(Asset $asset): void
{
    $this->actingAs($this->actor)
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'status_id' => (string) Statuslabel::factory()->readyToDeploy()->create()->id,
            'assigned_user' => $this->assignedUser->id,
        ]);
}

function postAccessoryCheckout(Accessory $accessory): void
{
    $this->actingAs($this->actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'status_id' => (string) Statuslabel::factory()->readyToDeploy()->create()->id,
            'assigned_user' => $this->assignedUser->id,
            'checkout_qty' => 1,
        ]);
}

function postLicenseCheckout(License $license): void
{
    $this->actingAs($this->actor)
        ->post("/licenses/{$license->id}/checkout/", [
            'checkout_to_type' => 'user',
            'assigned_user' => $this->assignedUser->id,
        ]);
}

function postConsumableCheckout(Consumable $consumable): void
{
    $this->actingAs($this->actor)
        ->post(route('consumables.checkout.store', $consumable), [
            'checkout_to_type' => 'user',
            'assigned_user' => $this->assignedUser->id,
            'checkout_qty' => 1,
        ]);
}
