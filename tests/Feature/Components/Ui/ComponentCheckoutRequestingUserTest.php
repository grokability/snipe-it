<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\User;

test('view receives requesting user id from valid request id', function () {
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $component = Component::factory()->create(['qty' => 5]);
    Asset::factory()->assignedToUser($requester)->create();
    $checkoutRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
    ]);

    $this->actingAs($admin)
        ->get(route('components.checkout.show', [
            'componentID' => $component->id,
            'request_id' => $checkoutRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('requestingUserId', $requester->id);
});

test('view falls back to null when request id absent', function () {
    $admin = User::factory()->superuser()->create();
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAs($admin)
        ->get(route('components.checkout.show', ['componentID' => $component->id]))
        ->assertOk()
        ->assertViewHas('requestingUserId', null);
});

test('forged request id targeting different component is ignored', function () {
    // Admin twiddles the URL to reference a CheckoutRequest that
    // targets a DIFFERENT component. Guard must ignore it so an
    // admin can't see which users have open requests on
    // components they aren't currently working with.
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();

    $component = Component::factory()->create(['qty' => 5]);
    $otherComponent = Component::factory()->create(['qty' => 5]);

    $requestForOther = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $otherComponent->id,
        'requestable_type' => Component::class,
    ]);

    $this->actingAs($admin)
        ->get(route('components.checkout.show', [
            'componentID' => $component->id,
            'request_id' => $requestForOther->id,
        ]))
        ->assertOk()
        ->assertViewHas('requestingUserId', null);
});

test('forged request id of different requestable type is ignored', function () {
    // Same guard, non-Component requestable_type. An accessory
    // request-id passed to a component checkout must not hydrate
    // the picker from an unrelated request's user.
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $component = Component::factory()->create(['qty' => 5]);
    $accessory = Accessory::factory()->create();

    $accessoryRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $this->actingAs($admin)
        ->get(route('components.checkout.show', [
            'componentID' => $component->id,
            'request_id' => $accessoryRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('requestingUserId', null);
});

test('canceled request is ignored', function () {
    // Stale canceled rows shouldn't influence live checkout UX.
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $component = Component::factory()->create(['qty' => 5]);
    $canceledRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'canceled_at' => now()->subMinute(),
    ]);

    $this->actingAs($admin)
        ->get(route('components.checkout.show', [
            'componentID' => $component->id,
            'request_id' => $canceledRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('requestingUserId', null);
});

test('nonexistent request id is ignored', function () {
    $admin = User::factory()->superuser()->create();
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAs($admin)
        ->get(route('components.checkout.show', [
            'componentID' => $component->id,
            'request_id' => 999999,
        ]))
        ->assertOk()
        ->assertViewHas('requestingUserId', null);
});

test('selectlist scopes to users assigned assets when assigned to is set', function () {
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $otherUser = User::factory()->create();

    $assignedToRequester = Asset::factory()->assignedToUser($requester)->create();
    $assignedToOther = Asset::factory()->assignedToUser($otherUser)->create();

    $rows = $this->actingAsForApi($admin)
        ->getJson(route('assets.selectlist', ['assignedTo' => $requester->id]))
        ->assertOk()
        ->json('results');

    $ids = collect($rows)->pluck('id')->all();
    expect($ids)->toContain($assignedToRequester->id);
    expect($ids)->not->toContain($assignedToOther->id);
});

test('view receives checkout request and other pending requests', function () {
    // The info callout above the form reads from two view vars:
    // $checkoutRequest (the row we came in on) and
    // $otherPendingRequests (anyone else still waiting on the same
    // component). Both must resolve when the request_id is valid;
    // the other-pending list must exclude the current row and any
    // canceled rows so the admin sees an accurate waiting list.
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $anotherRequester = User::factory()->create();
    $thirdRequester = User::factory()->create();
    $component = Component::factory()->create(['qty' => 5]);

    $currentRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'quantity' => 2,
    ]);
    $otherOpen = CheckoutRequest::factory()->create([
        'user_id' => $anotherRequester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'quantity' => 1,
    ]);

    // Canceled row shouldn't appear in the waiting list.
    CheckoutRequest::factory()->create([
        'user_id' => $thirdRequester->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'canceled_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('components.checkout.show', [
            'componentID' => $component->id,
            'request_id' => $currentRequest->id,
        ]))
        ->assertOk();

    $response->assertViewHas('checkoutRequest', fn ($request) => $request?->id === $currentRequest->id);
    $response->assertViewHas('otherPendingRequests', function ($pending) use ($otherOpen, $currentRequest) {
        $ids = collect($pending)->pluck('id')->all();

        return count($ids) === 1
            && in_array($otherOpen->id, $ids, true)
            && ! in_array($currentRequest->id, $ids, true);
    });
});

test('view falls back to empty pending list when request id absent', function () {
    $admin = User::factory()->superuser()->create();
    $component = Component::factory()->create(['qty' => 5]);

    $response = $this->actingAs($admin)
        ->get(route('components.checkout.show', ['componentID' => $component->id]))
        ->assertOk();

    $response->assertViewHas('checkoutRequest', null);
    $response->assertViewHas('otherPendingRequests', fn ($pending) => collect($pending)->isEmpty());
});

test('selectlist returns empty when target user has no assigned assets', function () {
    // When the requester has nothing assigned to them, the picker
    // returns an empty list rather than falling through to the
    // full fleet. Showing every asset would defeat the point of
    // scoping ("install this into one of the requester's assets")
    // and misleads the admin about who owns what.
    $admin = User::factory()->superuser()->create();
    $userWithNoAssets = User::factory()->create();
    Asset::factory()->create();

    $rows = $this->actingAsForApi($admin)
        ->getJson(route('assets.selectlist', ['assignedTo' => $userWithNoAssets->id]))
        ->assertOk()
        ->json('results');

    expect($rows)->toBe([]);
});
