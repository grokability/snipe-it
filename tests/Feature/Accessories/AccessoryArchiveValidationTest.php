<?php

use App\Enums\CheckoutRequestState;
use App\Models\Accessory;
use App\Models\CheckoutRequest;
use App\Models\User;

test('accessory can save as requestable without archived', function () {
    $accessory = Accessory::factory()->create();

    $accessory->requestable = 1;
    $accessory->archived = 0;

    expect($accessory->save())->toBeTrue();
});

test('accessory can save as archived without requestable', function () {
    $accessory = Accessory::factory()->create();

    $accessory->archived = 1;
    $accessory->requestable = 0;

    expect($accessory->save())->toBeTrue();
});

test('accessory cannot save as both requestable and archived', function () {
    $accessory = Accessory::factory()->create();

    $accessory->requestable = 1;
    $accessory->archived = 1;

    expect($accessory->save())->toBeFalse();
    expect($accessory->getErrors()->toArray())->toHaveKey('archived');
    expect($accessory->getErrors()->toArray())->toHaveKey('requestable');
});

test('accessory with active checkouts cannot be archived', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create();
    $accessory->refresh();

    $accessory->archived = 1;

    expect($accessory->save())->toBeFalse();
    expect($accessory->getErrors()->get('archived')[0])->toBe(trans('general.archived_blocked_by_checkouts', ['item_type' => trans('general.accessory')]));
});

test('accessory without active checkouts can be archived', function () {
    $accessory = Accessory::factory()->create();

    $accessory->archived = 1;

    expect($accessory->save())->toBeTrue();
});

test('archiving accessory auto cancels pending requests', function () {
    $accessory = Accessory::factory()->create();
    $pending = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $accessory->archived = 1;
    $accessory->save();

    $pending->refresh();
    expect($pending->canceled_at)->not->toBeNull();
    expect($pending->state)->toBe(CheckoutRequestState::Canceled);
});

test('deleting accessory auto cancels pending requests', function () {
    $accessory = Accessory::factory()->create();
    $pending = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $accessory->delete();

    $pending->refresh();
    expect($pending->canceled_at)->not->toBeNull();
    expect($pending->state)->toBe(CheckoutRequestState::Canceled);
});

test('archiving does not touch already canceled requests', function () {
    $accessory = Accessory::factory()->create();
    $previousCancelAt = now()->subDay();
    $alreadyCanceled = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'canceled_at' => $previousCancelAt,
    ]);

    $accessory->archived = 1;
    $accessory->save();

    $alreadyCanceled->refresh();
    expect($alreadyCanceled->canceled_at->toDateTimeString())->toEqual($previousCancelAt->toDateTimeString());
});

test('un archiving does not touch requests', function () {
    $accessory = Accessory::factory()->archived()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);

    $accessory->archived = 0;
    $accessory->save();

    $request->refresh();
    expect($request->canceled_at)->toBeNull();
});
