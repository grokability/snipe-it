<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutAccessories()->checkinAccessories()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

function checkoutToUser(Accessory $accessory, User $user): AccessoryCheckout
{
    $response = handle(new Request([
        'id' => $accessory->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]));

    $checkoutId = $response->getStructuredContent()['checkout_id'];

    return AccessoryCheckout::find($checkoutId);
}

test('checks in accessory by checkout id', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $checkout = checkoutToUser($accessory, $user);

    $content = handle(['checkout_id' => $checkout->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
});

test('response includes accessory name', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->create(['name' => 'Checkin Test Accessory', 'qty' => 5]);
    $checkout = checkoutToUser($accessory, $user);

    $content = handle(['checkout_id' => $checkout->id])->getStructuredContent();

    expect($content['accessory_name'])->toEqual('Checkin Test Accessory');
    expect($content['accessory_id'])->toEqual($accessory->id);
});

test('returns error when checkout not found', function () {
    expect(handle(['checkout_id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('creates checkin action log entry', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $checkout = checkoutToUser($accessory, $user);

    handle(['checkout_id' => $checkout->id]);

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkin from',
    ]);
});

test('checkout record from factory can be checked in', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $checkout = AccessoryCheckout::where('accessory_id', $accessory->id)->first();

    $content = handle(['checkout_id' => $checkout->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
});

test('returns error when user lacks permission', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();
    $checkout = AccessoryCheckout::where('accessory_id', $accessory->id)->first();

    $this->actingAs(User::factory()->create());

    expect(handle(['checkout_id' => $checkout->id])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('accessories_checkout', ['id' => $checkout->id]);
});
