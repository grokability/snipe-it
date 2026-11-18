<?php

use App\Enums\CheckoutRequestState;
use App\Models\Accessory;
use App\Models\CheckoutRequest;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('requires checkout permission on accessories', function () {
    // Endpoint gate: caller without checkoutAccessories should
    // be blocked before they can see the pending queue.
    $accessory = Accessory::factory()->create(['qty' => 10]);

    $this->actingAs(User::factory()->create())
        ->get(route('accessories.fulfill-requests.create', $accessory))
        ->assertForbidden();
});

test('show lists all pending requests for the accessory', function () {
    $admin = User::factory()->checkoutAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $aliceRequest = CheckoutRequest::factory()->create([
        'user_id' => $alice->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 2,
    ]);
    $bobRequest = CheckoutRequest::factory()->create([
        'user_id' => $bob->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->get(route('accessories.fulfill-requests.create', $accessory))
        ->assertOk()
        ->assertViewHas('pendingRequests', function ($pending) use ($aliceRequest, $bobRequest) {
            $ids = collect($pending)->pluck('id')->all();

            return count($ids) === 2
                && in_array($aliceRequest->id, $ids, true)
                && in_array($bobRequest->id, $ids, true);
        });
});

test('fulfills only ticked rows and leaves others pending', function () {
    // Opt-in semantics: rows admin didn't check stay pending
    // on the queue for a later pass.
    $admin = User::factory()->checkoutAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $aliceRequest = CheckoutRequest::factory()->create([
        'user_id' => $alice->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 2,
    ]);
    $bobRequest = CheckoutRequest::factory()->create([
        'user_id' => $bob->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->post(route('accessories.fulfill-requests.store', $accessory), [
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
    // Admin fulfills 1 of a 3-request. State stays pending;
    // fulfilled_quantity advances so the next pass knows what's
    // still owed. Original quantity stays immutable.
    $admin = User::factory()->checkoutAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);
    $requester = User::factory()->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->post(route('accessories.fulfill-requests.store', $accessory), [
            'enabled_requests' => [$request->id => '1'],
            'user_id' => [$request->id => $requester->id],
            'qty' => [$request->id => 1],
            'notes' => [$request->id => ''],
        ])
        ->assertRedirect(route('requests.index'));

    $fresh = $request->fresh();
    expect($fresh->state)->toBe(CheckoutRequestState::Pending);
    expect($fresh->quantity)->toBe(3);
    expect($fresh->fulfilled_quantity)->toBe(1);
    expect($fresh->remainingQuantity())->toBe(2);
});

test('stale or already fulfilled row is silently skipped', function () {
    // Between page load and submit, another admin already
    // fulfilled or canceled the request. The bulk-fulfill
    // controller should skip that row (per-row partial success)
    // without failing the whole batch.
    $admin = User::factory()->checkoutAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $canceledRequest = CheckoutRequest::factory()->create([
        'user_id' => $alice->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 1,
        'canceled_at' => now(),
    ]);
    $stillPending = CheckoutRequest::factory()->create([
        'user_id' => $bob->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('accessories.fulfill-requests.store', $accessory), [
            'enabled_requests' => [
                $canceledRequest->id => '1',
                $stillPending->id => '1',
            ],
            'user_id' => [
                $canceledRequest->id => $alice->id,
                $stillPending->id => $bob->id,
            ],
            'qty' => [
                $canceledRequest->id => 1,
                $stillPending->id => 1,
            ],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    expect($canceledRequest->fresh()->state)->toBe(CheckoutRequestState::Canceled);
    expect($stillPending->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
});

test('empty selection redirects back with error', function () {
    // Submit without ticking any rows: nothing to do. Redirect
    // back to the fulfill screen with a "no rows selected"
    // flash rather than confusing the admin with a bare
    // success redirect.
    $admin = User::factory()->checkoutAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);
    CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('accessories.fulfill-requests.store', $accessory), [
            // no enabled_requests
            'user_id' => [],
            'qty' => [],
            'notes' => [],
        ])
        ->assertRedirect(route('accessories.fulfill-requests.create', $accessory))
        ->assertSessionHas('error');
});
