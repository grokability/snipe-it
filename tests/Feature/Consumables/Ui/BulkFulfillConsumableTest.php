<?php

use App\Enums\CheckoutRequestState;
use App\Models\CheckoutRequest;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('requires checkout permission on consumables', function () {
    $consumable = Consumable::factory()->create(['qty' => 10]);

    $this->actingAs(User::factory()->create())
        ->get(route('consumables.fulfill-requests.create', $consumable))
        ->assertForbidden();
});

test('fulfills only ticked rows', function () {
    $admin = User::factory()->checkoutConsumables()->create();
    $consumable = Consumable::factory()->create(['qty' => 10]);
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $aliceRequest = CheckoutRequest::factory()->create([
        'user_id' => $alice->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
        'quantity' => 2,
    ]);
    $bobRequest = CheckoutRequest::factory()->create([
        'user_id' => $bob->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
        'quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->post(route('consumables.fulfill-requests.store', $consumable), [
            'enabled_requests' => [$aliceRequest->id => '1'],
            'user_id' => [$aliceRequest->id => $alice->id],
            'qty' => [$aliceRequest->id => 2],
            'notes' => [$aliceRequest->id => ''],
        ])
        ->assertRedirect(route('requests.index'));

    expect($aliceRequest->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($bobRequest->fresh()->state)->toBe(CheckoutRequestState::Pending);
});

test('partial qty leaves row pending with counter advanced', function () {
    $admin = User::factory()->checkoutConsumables()->create();
    $consumable = Consumable::factory()->create(['qty' => 10]);
    $requester = User::factory()->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
        'quantity' => 4,
    ]);

    $this->actingAs($admin)
        ->post(route('consumables.fulfill-requests.store', $consumable), [
            'enabled_requests' => [$request->id => '1'],
            'user_id' => [$request->id => $requester->id],
            'qty' => [$request->id => 2],
            'notes' => [$request->id => ''],
        ])
        ->assertRedirect(route('requests.index'));

    $fresh = $request->fresh();
    expect($fresh->state)->toBe(CheckoutRequestState::Pending);
    expect($fresh->fulfilled_quantity)->toBe(2);
    expect($fresh->remainingQuantity())->toBe(2);
});
