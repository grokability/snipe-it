<?php

use App\Actions\CheckoutRequests\FulfillCheckoutRequestAction;
use App\Enums\CheckoutRequestState;
use App\Exceptions\InvalidCheckoutRequestTransition;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\User;

test('new request defaults to pending', function () {
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Asset::factory()->create()->id,
        'requestable_type' => Asset::class,
    ]);

    expect($request->state)->toBe(CheckoutRequestState::Pending);
});

test('pending transitions to fulfilled via action', function () {
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Asset::factory()->create()->id,
        'requestable_type' => Asset::class,
    ]);

    FulfillCheckoutRequestAction::run($request);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($request->fulfilled_at)->not->toBeNull();
});

test('pending transitions to canceled via cancel request', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);

    $affected = $asset->cancelRequest($user->id);
    $request->refresh();

    expect($affected)->toBe(1);
    expect($request->state)->toBe(CheckoutRequestState::Canceled);
    expect($request->canceled_at)->not->toBeNull();
});

test('canceled cannot be fulfilled', function () {
    // Terminal-state guard: a canceled row is done, and the
    // fulfillment hook must not resurrect it if the same user
    // later gets a checkout for the same item.
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);
    $asset->cancelRequest($user->id);
    $request->refresh();

    $this->expectException(InvalidCheckoutRequestTransition::class);
    FulfillCheckoutRequestAction::run($request);
});

test('fulfilled is idempotent', function () {
    // The fulfillment hook can fire twice (retry, tests). A
    // second call on a fulfilled row must no-op instead of
    // throwing so the checkout controller doesn't 500 on a
    // benign re-entry.
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Asset::factory()->create()->id,
        'requestable_type' => Asset::class,
    ]);
    FulfillCheckoutRequestAction::run($request);
    $request->refresh();
    $firstFulfilledAt = $request->fulfilled_at;

    FulfillCheckoutRequestAction::run($request);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Fulfilled);

    // Second call didn't overwrite the original timestamp,
    // which would happen if the guard was missing.
    expect($request->fulfilled_at->timestamp)->toEqual($firstFulfilledAt->timestamp);
});

test('cancel request only touches pending rows', function () {
    // If a user's request is already fulfilled (e.g. auto-
    // fulfilled by a prior checkout), a later cancel by the
    // same user for the same item must not flip the fulfilled
    // row back to canceled - the pending-state filter in
    // Requestable::cancelRequest gates on state, not just
    // canceled_at.
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);
    FulfillCheckoutRequestAction::run($request);

    $affected = $asset->cancelRequest($user->id);

    expect($affected)->toBe(0);
    expect($request->fresh()->state)->toBe(CheckoutRequestState::Fulfilled);
});

test('open scope excludes terminal states', function () {
    $asset = Asset::factory()->create();
    $accessory = Accessory::factory()->create();

    // Pending: counts as open.
    CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);

    // Fulfilled: does not count as open.
    $fulfilled = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);
    FulfillCheckoutRequestAction::run($fulfilled);

    // Canceled: does not count as open.
    $canceledUser = User::factory()->create();
    CheckoutRequest::factory()->create([
        'user_id' => $canceledUser->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
    ]);
    $accessory->cancelRequest($canceledUser->id);

    expect(CheckoutRequest::open()->count())->toBe(1);
});

test('is requested by returns null for fulfilled row', function () {
    // Requestable::isRequestedBy is what gates the "you already
    // have an open request" duplicate check + the request/
    // cancel button-swap on the requestable-tab. A fulfilled row
    // must not count so the user can request the same item
    // again after their previous request closed.
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $asset->id,
        'requestable_type' => Asset::class,
    ]);
    FulfillCheckoutRequestAction::run($request);

    expect($asset->isRequestedBy($user))->toBeNull();
});

test('partial fulfillment advances counter and leaves pending', function () {
    // Admin fulfills 3 of a 5-requested row. State stays
    // pending because there's still 2 outstanding; counter
    // advances so the next fulfillment knows how much is left.
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Accessory::factory()->create()->id,
        'requestable_type' => Accessory::class,
        'quantity' => 5,
    ]);

    FulfillCheckoutRequestAction::run($request, null, 3);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Pending);
    expect($request->fulfilled_quantity)->toBe(3);
    expect($request->remainingQuantity())->toBe(2);
    expect($request->fulfilled_at)->toBeNull();
    expect($request->isPartiallyFulfilled())->toBeTrue();
});

test('full fulfillment across multiple partial calls closes the row', function () {
    // Two partial fulfillments that together hit the requested
    // qty flip the row to fulfilled. fulfilled_at is set only
    // on the pass that pushes the counter past the threshold.
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Accessory::factory()->create()->id,
        'requestable_type' => Accessory::class,
        'quantity' => 4,
    ]);

    FulfillCheckoutRequestAction::run($request, null, 1);
    $request->refresh();
    expect($request->state)->toBe(CheckoutRequestState::Pending);
    expect($request->fulfilled_at)->toBeNull();

    FulfillCheckoutRequestAction::run($request, null, 3);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($request->fulfilled_quantity)->toBe(4);
    expect($request->remainingQuantity())->toBe(0);
    expect($request->fulfilled_at)->not->toBeNull();
    expect($request->isPartiallyFulfilled())->toBeFalse();
});

test('overfulfill caps at requested quantity', function () {
    // Admin somehow submits a qty larger than what was
    // requested (misclicked, hand-crafted POST). The action
    // caps at the remainder so fulfilled_quantity never
    // exceeds quantity - the audit trail preserves the
    // original ask exactly.
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Accessory::factory()->create()->id,
        'requestable_type' => Accessory::class,
        'quantity' => 2,
    ]);

    FulfillCheckoutRequestAction::run($request, null, 99);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($request->fulfilled_quantity)->toBe(2);
});

test('zero qty fulfillment is a noop', function () {
    // Belt: an event with qty=0 (edge case, shouldn't happen
    // from a real checkout but callers might slip a 0 through)
    // must not advance the counter or flip the state.
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Accessory::factory()->create()->id,
        'requestable_type' => Accessory::class,
        'quantity' => 3,
    ]);

    FulfillCheckoutRequestAction::run($request, null, 0);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Pending);
    expect($request->fulfilled_quantity)->toBe(0);
});

test('null qty fulfillment consumes the full remainder', function () {
    // Backwards compat: single-target checkout controllers that
    // predate partial-fulfillment tracking call the action
    // without a qty. The action assumes "this fulfillment
    // covers the whole thing" so the request closes in one
    // pass. Keeps the old behavior for callers not opting into
    // the new partial semantics.
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => Accessory::factory()->create()->id,
        'requestable_type' => Accessory::class,
        'quantity' => 7,
    ]);

    FulfillCheckoutRequestAction::run($request);
    $request->refresh();

    expect($request->state)->toBe(CheckoutRequestState::Fulfilled);
    expect($request->fulfilled_quantity)->toBe(7);
});

test('partial row still counts as requested by for duplicate guard', function () {
    // Requestable::isRequestedBy gates the "you already have an
    // open request" duplicate check. A partially-fulfilled row
    // is still open (state=pending), so it must still count -
    // otherwise the user could file a new request while the
    // partial is outstanding and end up with two open rows for
    // the same item.
    $user = User::factory()->create();
    $accessory = Accessory::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_id' => $accessory->id,
        'requestable_type' => Accessory::class,
        'quantity' => 5,
    ]);

    FulfillCheckoutRequestAction::run($request, null, 2);

    expect($accessory->isRequestedBy($user))->not->toBeNull();
});
