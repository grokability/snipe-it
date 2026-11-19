<?php

use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;

test('single consume requires checkout permission', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

    $this->actingAs(User::factory()->viewConsumables()->create())
        ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]))
        ->assertForbidden();

    expect($assignment->fresh()->consumed_at)->toBeNull();
});

test('single consume sets consumed at', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]))
        ->assertRedirect(route('consumables.show', $consumable))
        ->assertSessionHas('success');

    expect($assignment->fresh()->consumed_at)->not->toBeNull();
});

test('single consume decrements outstanding count', function () {
    // Regression pin for the archive-gate behavior: an outstanding
    // row that gets marked consumed should drop out of
    // numOutstanding() so the parent becomes archivable.
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

    expect($consumable->fresh()->numOutstanding())->toBe(1);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]));

    expect($consumable->fresh()->numOutstanding())->toBe(0);
});

test('single consume is one way', function () {
    // Posting again against an already-consumed row warns and
    // leaves consumed_at pinned to its original stamp.
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();
    $firstStamp = now()->subHour();
    $assignment->consumed_at = $firstStamp;
    $assignment->save();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]))
        ->assertRedirect(route('consumables.show', $consumable))
        ->assertSessionHas('warning');

    expect($assignment->fresh()->consumed_at->toDateTimeString())->toEqual($firstStamp->toDateTimeString());
});

test('single consume rejects cross consumable assignment', function () {
    // A pivot id that belongs to a different consumable than the
    // one in the URL must 404. Prevents a user with checkout on
    // consumable A from ending the lifecycle on consumable B's
    // pivot rows just by swapping the URL id.
    $consumableA = Consumable::factory()->checkedOutToUser()->create();
    $consumableB = Consumable::factory()->checkedOutToUser()->create();
    $assignmentB = ConsumableAssignment::where('consumable_id', $consumableB->id)->firstOrFail();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.consume', ['consumable' => $consumableA->id, 'assignment' => $assignmentB->id]))
        ->assertNotFound();

    expect($assignmentB->fresh()->consumed_at)->toBeNull();
});

test('single consume writes audit log', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]));

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Consumable::class,
        'item_id' => $consumable->id,
        'target_type' => User::class,
        'target_id' => $assignment->assigned_to,
        'action_type' => 'consumed',
    ]);
});

test('bulk consume show filters already consumed rows', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
    $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
    $outstanding = $assignments->first();
    $alreadyConsumed = $assignments->last();
    $alreadyConsumed->consumed_at = now();
    $alreadyConsumed->save();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkconsume.show'), [
            'ids' => [$outstanding->id, $alreadyConsumed->id],
        ])
        ->assertOk()
        ->assertViewIs('consumables.bulk-consume');

    $visible = $response->viewData('assignments');
    expect($visible->contains('id', $outstanding->id))->toBeTrue();
    expect($visible->contains('id', $alreadyConsumed->id))->toBeFalse();
});

test('bulk consume store marks every selected row', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
    $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkconsume.store'), [
            'ids' => $assignments->pluck('id')->all(),
        ])
        ->assertRedirect(route('consumables.show', $consumable))
        ->assertSessionHas('success');

    $assignments->each(fn ($a) => expect($a->fresh()->consumed_at)->not->toBeNull());
    expect($consumable->fresh()->numOutstanding())->toBe(0);
});

test('bulk consume store requires checkout permission', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

    $this->actingAs(User::factory()->viewConsumables()->create())
        ->post(route('consumables.bulkconsume.store'), [
            'ids' => [$assignment->id],
        ])
        ->assertForbidden();

    expect($assignment->fresh()->consumed_at)->toBeNull();
});

test('bulk consume store rejects mixed consumable submission', function () {
    // Mixed-parent submissions are a sign the UI was tampered
    // with. Reject the whole batch rather than silently closing
    // out a cross-parent set.
    $consumableA = Consumable::factory()->checkedOutToUser()->create();
    $consumableB = Consumable::factory()->checkedOutToUser()->create();
    $assignmentA = ConsumableAssignment::where('consumable_id', $consumableA->id)->firstOrFail();
    $assignmentB = ConsumableAssignment::where('consumable_id', $consumableB->id)->firstOrFail();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkconsume.store'), [
            'ids' => [$assignmentA->id, $assignmentB->id],
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('error');

    expect($assignmentA->fresh()->consumed_at)->toBeNull();
    expect($assignmentB->fresh()->consumed_at)->toBeNull();
});

test('bulk consume store writes one log per row', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
    $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
    $before = Actionlog::where('item_type', Consumable::class)
        ->where('action_type', 'consumed')
        ->count();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkconsume.store'), [
            'ids' => $assignments->pluck('id')->all(),
        ]);

    $after = Actionlog::where('item_type', Consumable::class)
        ->where('action_type', 'consumed')
        ->count();
    expect($after)->toBe($before + $assignments->count());
});
