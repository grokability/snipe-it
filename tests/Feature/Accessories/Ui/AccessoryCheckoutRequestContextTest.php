<?php

use App\Models\Accessory;
use App\Models\CheckoutRequest;
use App\Models\Consumable;
use App\Models\User;

test('view receives checkout request when request id is valid', function () {
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $accessory = Accessory::factory()->create();
    $checkoutRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $this->actingAs($admin)
        ->get(route('accessories.checkout.show', [
            'accessory' => $accessory->id,
            'request_id' => $checkoutRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('checkoutRequest', fn ($r) => $r?->id === $checkoutRequest->id);
});

test('view receives null when request id absent', function () {
    $admin = User::factory()->superuser()->create();
    $accessory = Accessory::factory()->create();

    $this->actingAs($admin)
        ->get(route('accessories.checkout.show', ['accessory' => $accessory->id]))
        ->assertOk()
        ->assertViewHas('checkoutRequest', null)
        ->assertViewHas('otherPendingRequests', fn ($p) => collect($p)->isEmpty());
});

test('forged request id for a different requestable type is ignored', function () {
    // Cross-type guard: a Consumable's request_id passed to the
    // accessory screen must not hydrate an off-scope requester
    // into the accessory checkout context.
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $accessory = Accessory::factory()->create();
    $consumable = Consumable::factory()->create();

    $consumableRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
    ]);

    $this->actingAs($admin)
        ->get(route('accessories.checkout.show', [
            'accessory' => $accessory->id,
            'request_id' => $consumableRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('checkoutRequest', null);
});

test('view receives other pending requests ordered oldest first', function () {
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $earlier = User::factory()->create();
    $later = User::factory()->create();
    $accessory = Accessory::factory()->create();

    // Waiting-list ordering: created_at then id. Persist rows in
    // reverse to confirm the helper sorts, doesn't rely on insert
    // order.
    $currentRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);
    $laterOther = CheckoutRequest::factory()->create([
        'user_id' => $later->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'created_at' => now()->subMinutes(2),
    ]);
    $earlierOther = CheckoutRequest::factory()->create([
        'user_id' => $earlier->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'created_at' => now()->subHours(2),
    ]);

    $this->actingAs($admin)
        ->get(route('accessories.checkout.show', [
            'accessory' => $accessory->id,
            'request_id' => $currentRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('otherPendingRequests', function ($pending) use ($earlierOther, $laterOther, $currentRequest) {
            $ids = collect($pending)->pluck('id')->all();

            return $ids === [$earlierOther->id, $laterOther->id]
                && ! in_array($currentRequest->id, $ids, true);
        });
});
