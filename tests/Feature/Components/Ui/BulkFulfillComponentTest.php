<?php

use App\Enums\CheckoutRequestState;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('requires checkout permission on components', function () {
    $component = Component::factory()->create(['qty' => 10]);

    $this->actingAs(User::factory()->create())
        ->get(route('components.fulfill-requests.create', $component))
        ->assertForbidden();
});

test('row disabled for requester with no assigned assets', function () {
    // Component installs INTO an asset the requester owns. A
    // requester with zero assigned assets has no target to
    // install into, so their row surfaces with an empty
    // available-asset set (view renders disabled with an
    // explanatory message).
    $admin = User::factory()->checkoutComponents()->create();
    $component = Component::factory()->create(['qty' => 10]);
    $noAssetsRequester = User::factory()->create();

    CheckoutRequest::factory()->create([
        'user_id' => $noAssetsRequester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
    ]);

    $this->actingAs($admin)
        ->get(route('components.fulfill-requests.create', $component))
        ->assertOk()
        ->assertViewHas('rowContext', function ($ctx) use ($noAssetsRequester) {
            $req = CheckoutRequest::where('user_id', $noAssetsRequester->id)->first();

            return isset($ctx[$req->id])
                && $ctx[$req->id]['availableAssets']->isEmpty();
        });
});

test('fulfills ticked row and closes request explicitly', function () {
    // Component targets an Asset, and the state-machine
    // listener only fires for User-target checkouts. Verify
    // the controller closes the request explicitly (the
    // listener would silently skip it).
    $admin = User::factory()->checkoutComponents()->create();
    $component = Component::factory()->create(['qty' => 10]);
    $requester = User::factory()->create();
    $targetAsset = Asset::factory()->assignedToUser($requester)->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'quantity' => 2,
    ]);

    $this->actingAs($admin)
        ->post(route('components.fulfill-requests.store', $component), [
            'enabled_requests' => [$request->id => '1'],
            'asset_id' => [$request->id => $targetAsset->id],
            'qty' => [$request->id => 2],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    expect($request->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($request->fresh()->fulfilled_quantity)->toBe(2);
});

test('asset not belonging to requester is rejected', function () {
    // Requester-scoping guard: hand-crafted POST pointing at
    // an asset that belongs to a DIFFERENT user must not
    // install the component into that other user's asset.
    $admin = User::factory()->checkoutComponents()->create();
    $component = Component::factory()->create(['qty' => 10]);
    $requester = User::factory()->create();
    $someoneElse = User::factory()->create();
    $someoneElsesAsset = Asset::factory()->assignedToUser($someoneElse)->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'quantity' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('components.fulfill-requests.store', $component), [
            'enabled_requests' => [$request->id => '1'],
            'asset_id' => [$request->id => $someoneElsesAsset->id],
            'qty' => [$request->id => 1],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    expect($request->fresh()->state)->toBe(CheckoutRequestState::Pending);
});

test('partial qty leaves row pending with counter advanced', function () {
    $admin = User::factory()->checkoutComponents()->create();
    $component = Component::factory()->create(['qty' => 10]);
    $requester = User::factory()->create();
    $targetAsset = Asset::factory()->assignedToUser($requester)->create();

    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'quantity' => 5,
    ]);

    $this->actingAs($admin)
        ->post(route('components.fulfill-requests.store', $component), [
            'enabled_requests' => [$request->id => '1'],
            'asset_id' => [$request->id => $targetAsset->id],
            'qty' => [$request->id => 2],
            'notes' => [],
        ])
        ->assertRedirect(route('requests.index'));

    $fresh = $request->fresh();
    expect($fresh->state)->toBe(CheckoutRequestState::Pending);
    expect($fresh->fulfilled_quantity)->toBe(2);
    expect($fresh->remainingQuantity())->toBe(3);
});
