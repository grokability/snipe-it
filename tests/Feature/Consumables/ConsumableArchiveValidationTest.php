<?php

use App\Enums\CheckoutRequestState;
use App\Models\CheckoutRequest;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;

test('consumable can save as requestable without archived', function () {
    $consumable = Consumable::factory()->create();

    $consumable->requestable = 1;
    $consumable->archived = 0;

    expect($consumable->save())->toBeTrue();
});

test('consumable can save as archived without requestable', function () {
    $consumable = Consumable::factory()->create();

    $consumable->archived = 1;
    $consumable->requestable = 0;

    expect($consumable->save())->toBeTrue();
});

test('consumable cannot save as both requestable and archived', function () {
    $consumable = Consumable::factory()->create();

    $consumable->requestable = 1;
    $consumable->archived = 1;

    expect($consumable->save())->toBeFalse();
    expect($consumable->getErrors()->toArray())->toHaveKey('archived');
    expect($consumable->getErrors()->toArray())->toHaveKey('requestable');
});

test('consumable with outstanding distributions cannot be archived', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $consumable->refresh();

    $consumable->archived = 1;

    expect($consumable->save())->toBeFalse();
    expect($consumable->getErrors()->get('archived')[0])->toBe(trans('general.archived_blocked_by_outstanding', ['item_type' => trans('general.consumable')]));
});

test('consumable without outstanding distributions can be archived', function () {
    $consumable = Consumable::factory()->create();

    $consumable->archived = 1;

    expect($consumable->save())->toBeTrue();
});

test('consumable with all consumed distributions can be archived', function () {
    // Backfill behavior test: after every pivot row has been marked
    // consumed, the archive guard no longer fires because
    // numOutstanding() returns 0. Historical distributions still
    // sit in the pivot table, they just don't gate the archive.
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $consumable->refresh();
    ConsumableAssignment::query()
        ->where('consumable_id', $consumable->id)
        ->update(['consumed_at' => now()]);

    $consumable->archived = 1;

    expect($consumable->save())->toBeTrue();
});

test('archiving consumable auto cancels pending requests', function () {
    $consumable = Consumable::factory()->create();
    $pending = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
    ]);

    $consumable->archived = 1;
    $consumable->save();

    $pending->refresh();
    expect($pending->canceled_at)->not->toBeNull();
    expect($pending->state)->toBe(CheckoutRequestState::Canceled);
});

test('deleting consumable auto cancels pending requests', function () {
    $consumable = Consumable::factory()->create();
    $pending = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
    ]);

    $consumable->delete();

    $pending->refresh();
    expect($pending->canceled_at)->not->toBeNull();
    expect($pending->state)->toBe(CheckoutRequestState::Canceled);
});

test('archiving does not touch already canceled requests', function () {
    $consumable = Consumable::factory()->create();
    $previousCancelAt = now()->subDay();
    $alreadyCanceled = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
        'canceled_at' => $previousCancelAt,
    ]);

    $consumable->archived = 1;
    $consumable->save();

    $alreadyCanceled->refresh();
    expect($alreadyCanceled->canceled_at->toDateTimeString())->toEqual($previousCancelAt->toDateTimeString());
});

test('un archiving does not touch requests', function () {
    $consumable = Consumable::factory()->archived()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $consumable->id,
        'requestable_type' => Consumable::class,
    ]);

    $consumable->archived = 0;
    $consumable->save();

    $request->refresh();
    expect($request->canceled_at)->toBeNull();
});

test('set archived attribute normalizes empty string', function () {
    $consumable = Consumable::factory()->create();

    $consumable->archived = '';

    expect($consumable->archived)->toBeFalse();
});
