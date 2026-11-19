<?php

use App\Enums\CheckoutRequestState;
use App\Models\Asset;
use App\Models\Category;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\User;

test('component can save as requestable without archived', function () {
    $component = Component::factory()->create();

    $component->requestable = 1;
    $component->archived = 0;

    expect($component->save())->toBeTrue();
});

test('component can save as archived without requestable', function () {
    $component = Component::factory()->create();

    $component->archived = 1;
    $component->requestable = 0;

    expect($component->save())->toBeTrue();
});

test('component cannot save as both requestable and archived', function () {
    $component = Component::factory()->create();

    $component->requestable = 1;
    $component->archived = 1;

    expect($component->save())->toBeFalse();
    expect($component->getErrors()->toArray())->toHaveKey('archived');
    expect($component->getErrors()->toArray())->toHaveKey('requestable');
});

test('component with active checkouts cannot be archived', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();
    $component->assets()->attach($asset->id, [
        'assigned_qty' => 2,
        'created_at' => now(),
        'created_by' => 1,
    ]);

    $component->archived = 1;

    expect($component->save())->toBeFalse();
    expect($component->getErrors()->get('archived')[0])->toBe(trans('general.archived_blocked_by_checkouts', ['item_type' => trans('general.component')]));
});

test('component without active checkouts can be archived', function () {
    $component = Component::factory()->create(['qty' => 10]);

    $component->archived = 1;

    expect($component->save())->toBeTrue();
});

test('archiving component auto cancels pending requests', function () {
    // Pending requests for a just-archived component would otherwise
    // sit in /requests as un-fulfillable rows and in the user's
    // /account/requested list with a dead link. Observer closes them
    // out so neither surface shows the orphaned row.
    $component = Component::factory()->create();
    $pending = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
    ]);

    $component->archived = 1;
    $component->save();

    $pending->refresh();
    expect($pending->canceled_at)->not->toBeNull();
    expect($pending->state)->toBe(CheckoutRequestState::Canceled);
});

test('deleting component auto cancels pending requests', function () {
    $component = Component::factory()->create();
    $pending = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
    ]);

    $component->delete();

    $pending->refresh();
    expect($pending->canceled_at)->not->toBeNull();
    expect($pending->state)->toBe(CheckoutRequestState::Canceled);
});

test('archiving does not touch already canceled requests', function () {
    $component = Component::factory()->create();
    $previousCancelAt = now()->subDay();
    $alreadyCanceled = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
        'canceled_at' => $previousCancelAt,
    ]);

    $component->archived = 1;
    $component->save();

    $alreadyCanceled->refresh();
    expect($alreadyCanceled->canceled_at->toDateTimeString())->toEqual($previousCancelAt->toDateTimeString());
});

test('un archiving does not touch requests', function () {
    $component = Component::factory()->archived()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => User::factory()->create()->id,
        'requestable_id' => $component->id,
        'requestable_type' => Component::class,
    ]);

    $component->archived = 0;
    $component->save();

    $request->refresh();
    expect($request->canceled_at)->toBeNull();
});

test('new component can be created as archived', function () {
    // Regression check: ComponentsController::store() was setting
    // every attribute by hand and skipping `archived`, so a form
    // POST with `archived=1` persisted as 0. Pin so a future refactor
    // can't silently drop the field again.
    $component = new Component([
        'name' => 'archived at birth',
        'qty' => 1,
        'category_id' => Category::factory()->create()->id,
    ]);
    $component->archived = 1;

    expect($component->save())->toBeTrue();
    expect((bool) $component->fresh()->archived)->toBeTrue();
});
