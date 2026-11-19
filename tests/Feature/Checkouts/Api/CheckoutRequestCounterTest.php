<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('cancel without active request returns 404 and does not touch counter', function () {
    // Reg-test for the cancel-request counter drift: hitting the cancel
    // endpoint when the caller has no active CheckoutRequest used to
    // unconditionally decrement requests_counter, which drove the
    // counter negative and misrepresented pending admin work.
    $asset = Asset::factory()->requestable()->create(['requests_counter' => 0]);
    $user = User::factory()->create();

    $this->actingAsForApi($user)
        ->postJson(route('api.assets.requests.destroy', $asset))
        ->assertStatus(404)
        ->assertStatusMessageIs('error');

    expect($asset->fresh()->requests_counter)->toEqual(0);
});

test('duplicate active request returns 409 and increments counter only once', function () {
    // Reg-test for duplicate-active-request counter drift: a second
    // POST from the same caller used to add a second CheckoutRequest
    // row AND bump requests_counter a second time. On cancel only one
    // decrement fired, so the counter and the pending queue drifted
    // apart.
    $asset = Asset::factory()->requestable()->create(['requests_counter' => 0]);
    $user = User::factory()->create();

    $this->actingAsForApi($user)
        ->postJson(route('api.assets.requests.store', $asset))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->actingAsForApi($user)
        ->postJson(route('api.assets.requests.store', $asset))
        ->assertStatus(409)
        ->assertStatusMessageIs('error');

    expect($asset->fresh()->requests_counter)->toEqual(1);
    expect($asset->openRequests()->where('user_id', $user->id)->count())->toEqual(1, 'Second request should not have created a second active CheckoutRequest row.');
});

test('cancel after active request decrements counter by exactly one', function () {
    // Companion to the two above: a legitimate request-then-cancel
    // round trip must leave the counter at 0.
    $asset = Asset::factory()->requestable()->create(['requests_counter' => 0]);
    $user = User::factory()->create();

    $this->actingAsForApi($user)
        ->postJson(route('api.assets.requests.store', $asset))
        ->assertOk();

    expect($asset->fresh()->requests_counter)->toEqual(1);

    $this->actingAsForApi($user)
        ->postJson(route('api.assets.requests.destroy', $asset))
        ->assertOk()
        ->assertStatusMessageIs('success');

    expect($asset->fresh()->requests_counter)->toEqual(0);
});

test('double cancel only decrements counter once', function () {
    // Combined regression: request once, cancel twice. The second
    // cancel must 404 without dragging the counter below zero.
    $asset = Asset::factory()->requestable()->create(['requests_counter' => 0]);
    $user = User::factory()->create();

    $this->actingAsForApi($user)->postJson(route('api.assets.requests.store', $asset))->assertOk();
    $this->actingAsForApi($user)->postJson(route('api.assets.requests.destroy', $asset))->assertOk();
    $this->actingAsForApi($user)
        ->postJson(route('api.assets.requests.destroy', $asset))
        ->assertStatus(404);

    expect($asset->fresh()->requests_counter)->toEqual(0);
});
