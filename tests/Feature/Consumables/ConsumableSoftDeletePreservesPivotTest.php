<?php

use App\Models\Consumable;
use App\Models\ConsumableAssignment;

test('soft delete preserves pivot rows', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $pivotId = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail()->id;

    $consumable->delete();

    $this->assertDatabaseHas('consumables_users', ['id' => $pivotId]);
});

test('soft delete preserves consumed at stamps', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();
    $stamp = now();
    $assignment->consumed_at = $stamp;
    $assignment->save();

    $consumable->delete();

    $this->assertDatabaseHas('consumables_users', [
        'id' => $assignment->id,
        'consumed_at' => $stamp->toDateTimeString(),
    ]);
});

test('restore brings pivot history back intact', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
    $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
    $consumedOne = $assignments->first();
    $consumedOne->consumed_at = now();
    $consumedOne->save();

    $consumable->delete();
    $consumable->fresh()->restore();

    $restored = Consumable::query()->findOrFail($consumable->id);
    expect($restored->numCheckedOut())->toBe(2);
    expect($restored->numOutstanding())->toBe(1);
    expect($restored->numConsumed())->toBe(1);
});

test('force delete removes pivot rows', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $pivotId = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail()->id;

    $consumable->forceDelete();

    $this->assertDatabaseMissing('consumables_users', ['id' => $pivotId]);
});

test('purge removes pivot rows for soft deleted consumables', function () {
    // Purge runs raw DB::table()->delete(), bypassing Eloquent
    // observers and the forceDeleted hook. The pivot cleanup
    // must come from Purge's childTables map instead.
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $pivotId = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail()->id;
    $consumable->delete();

    $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

    $this->assertDatabaseMissing('consumables_users', ['id' => $pivotId]);
    $this->assertDatabaseMissing('consumables', ['id' => $consumable->id]);
});
