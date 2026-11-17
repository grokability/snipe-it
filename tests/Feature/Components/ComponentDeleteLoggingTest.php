<?php

namespace Tests\Feature\Components;

use App\Models\Actionlog;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression pin for the double-log on soft-delete: SoftDeletes
 * routes the delete flow through save(), which fires `updated`
 * alongside `deleting`. The observer's `updated` hook used to log a
 * generic "update" entry with just the deleted_at diff, producing a
 * phantom audit row next to the real "delete" entry. Observer now
 * skips deleted_at in the changed-field scan.
 */
class ComponentDeleteLoggingTest extends TestCase
{
    public function test_soft_delete_writes_only_a_single_delete_log_entry(): void
    {
        $this->actingAs(User::factory()->superuser()->create());

        $component = Component::factory()->create();

        $logsBefore = Actionlog::where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->count();

        $component->delete();

        $logsAfter = Actionlog::where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->orderBy('id')
            ->get();

        $this->assertCount($logsBefore + 1, $logsAfter, 'soft-delete should write exactly one new action_log row');
        $this->assertSame('delete', $logsAfter->last()->action_type);
    }

    public function test_regular_update_still_logs_an_update_entry(): void
    {
        // Pin that stripping deleted_at from the diff didn't accidentally
        // disable all update logging.
        $this->actingAs(User::factory()->superuser()->create());

        $component = Component::factory()->create();
        $component->name = 'Renamed';
        $component->save();

        $this->assertTrue(
            Actionlog::where('item_type', Component::class)
                ->where('item_id', $component->id)
                ->where('action_type', 'update')
                ->exists(),
            'a plain update should still log an "update" row',
        );
    }
}
