<?php

namespace Tests\Feature\Consumables;

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use Tests\TestCase;

/**
 * Pins the behavior change in the Consumable observer: soft-delete
 * preserves pivot rows (and their consumed_at stamps) so a restore
 * brings the item back with its mark-consumed lifecycle intact.
 * Only force-delete tears the pivot down.
 */
class ConsumableSoftDeletePreservesPivotTest extends TestCase
{
    public function test_soft_delete_preserves_pivot_rows(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $pivotId = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail()->id;

        $consumable->delete();

        $this->assertDatabaseHas('consumables_users', ['id' => $pivotId]);
    }

    public function test_soft_delete_preserves_consumed_at_stamps(): void
    {
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
    }

    public function test_restore_brings_pivot_history_back_intact(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $consumedOne = $assignments->first();
        $consumedOne->consumed_at = now();
        $consumedOne->save();

        $consumable->delete();
        $consumable->fresh()->restore();

        $restored = Consumable::query()->findOrFail($consumable->id);
        $this->assertSame(2, $restored->numCheckedOut());
        $this->assertSame(1, $restored->numOutstanding());
        $this->assertSame(1, $restored->numConsumed());
    }

    public function test_force_delete_removes_pivot_rows(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $pivotId = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail()->id;

        $consumable->forceDelete();

        $this->assertDatabaseMissing('consumables_users', ['id' => $pivotId]);
    }

    public function test_purge_removes_pivot_rows_for_soft_deleted_consumables(): void
    {
        // Purge runs raw DB::table()->delete(), bypassing Eloquent
        // observers and the forceDeleted hook. The pivot cleanup
        // must come from Purge's childTables map instead.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $pivotId = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail()->id;
        $consumable->delete();

        $this->artisan('snipeit:purge', ['--force' => 'true'])->assertExitCode(0);

        $this->assertDatabaseMissing('consumables_users', ['id' => $pivotId]);
        $this->assertDatabaseMissing('consumables', ['id' => $consumable->id]);
    }
}
