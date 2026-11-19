<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\User;

test('view receives checkout request when request id is valid', function () {
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $asset = Asset::factory()->create();
    $checkoutRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);

    $this->actingAs($admin)
        ->get(route('hardware.checkout.create', [
            'asset' => $asset->id,
            'request_id' => $checkoutRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('checkoutRequest', fn ($r) => $r?->id === $checkoutRequest->id);
});

test('view receives null when request id absent', function () {
    $admin = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();

    $this->actingAs($admin)
        ->get(route('hardware.checkout.create', ['asset' => $asset->id]))
        ->assertOk()
        ->assertViewHas('checkoutRequest', null)
        ->assertViewHas('otherPendingRequests', fn ($p) => collect($p)->isEmpty());
});

test('forged request id for a different requestable type is ignored', function () {
    // Cross-type guard: an Accessory's request_id passed to the
    // asset screen must not hydrate an off-scope requester.
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $asset = Asset::factory()->create();
    $accessory = Accessory::factory()->create();

    $accessoryRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $this->actingAs($admin)
        ->get(route('hardware.checkout.create', [
            'asset' => $asset->id,
            'request_id' => $accessoryRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('checkoutRequest', null);
});

test('view receives other pending requests excluding current and canceled', function () {
    $admin = User::factory()->superuser()->create();
    $requester = User::factory()->create();
    $other = User::factory()->create();
    $asset = Asset::factory()->create();

    $currentRequest = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);
    $otherOpen = CheckoutRequest::factory()->create([
        'user_id' => $other->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);

    // Canceled row must not appear.
    CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
        'canceled_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('hardware.checkout.create', [
            'asset' => $asset->id,
            'request_id' => $currentRequest->id,
        ]))
        ->assertOk()
        ->assertViewHas('otherPendingRequests', function ($pending) use ($otherOpen, $currentRequest) {
            $ids = collect($pending)->pluck('id')->all();

            return count($ids) === 1
                && in_array($otherOpen->id, $ids, true)
                && ! in_array($currentRequest->id, $ids, true);
        });
});
