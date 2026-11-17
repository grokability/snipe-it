<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

/**
 * genericActionsFormatter in partials/bootstrap-table reads
 * row.available_actions.restore (top-level, not nested in
 * bulk_selectable) to decide whether to render the per-row restore
 * button on the Deleted index. Pin that key here so a future
 * refactor doesn't push it back under bulk_selectable only and
 * leave admins with a disabled trash fallback instead of restore.
 */
class DeletedIndexRestoreActionTest extends TestCase
{
    public function test_soft_deleted_consumable_row_offers_restore_action(): void
    {
        $consumable = Consumable::factory()->create();
        $consumable->delete();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.index', ['status' => 'Deleted']))
            ->assertOk();

        $row = collect($response->json('rows'))->firstWhere('id', $consumable->id);
        $this->assertNotNull($row, 'Soft-deleted consumable must appear on the Deleted index.');
        $this->assertTrue($row['available_actions']['restore'] ?? false);
    }

    public function test_live_consumable_row_does_not_offer_restore_action(): void
    {
        $consumable = Consumable::factory()->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.index'))
            ->assertOk();

        $row = collect($response->json('rows'))->firstWhere('id', $consumable->id);
        $this->assertNotNull($row);
        $this->assertFalse($row['available_actions']['restore'] ?? true);
    }
}
