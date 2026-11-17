<?php

namespace Tests\Feature\Components\Api;

use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

/**
 * The per-row action buttons on the Deleted index are purely driven by
 * available_actions flags in the transformer payload (the shared
 * genericActionsFormatter inspects row.deleted_at + available_actions
 * to decide between active, disabled, and restore renderings).
 *
 * Pin the transformer's deleted-row shape so a future transformer
 * refactor can't silently flip edit/adjust-quantity/checkout from
 * disabled-but-visible back to active, or drop the restore flag that
 * swaps the trash button out for restore.
 */
class ComponentIndexDeletedActionsTest extends TestCase
{
    private function rowFor(Component $component): array
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index', ['status' => 'Deleted']))
            ->assertOk();

        $row = collect($response->json('rows'))->firstWhere('id', $component->id);
        $this->assertNotNull($row, "deleted component {$component->id} should appear in Deleted index");

        return $row;
    }

    public function test_deleted_row_blocks_update_and_delete_and_request(): void
    {
        $component = Component::factory()->create();
        $component->delete();

        $row = $this->rowFor($component);
        $this->assertFalse($row['available_actions']['update']);
        $this->assertFalse($row['available_actions']['delete']);
        $this->assertFalse($row['available_actions']['request']);
    }

    public function test_deleted_row_keeps_adjust_quantity_true_so_formatter_renders_disabled(): void
    {
        // adjust_quantity stays policy-true even for deleted rows so
        // the generic formatter renders a visible-but-disabled plus-
        // minus button (driven off row.deleted_at). Flipping this to
        // false would hide the button entirely, which is NOT what we
        // want on the Deleted index.
        $component = Component::factory()->create();
        $component->delete();

        $this->assertTrue($this->rowFor($component)['available_actions']['adjust_quantity']);
    }

    public function test_deleted_row_keeps_checkout_true_so_formatter_renders_disabled(): void
    {
        // Same rationale as adjust_quantity: keep the flag policy-
        // true, let the InOut formatter inspect row.deleted_at and
        // render a disabled checkout stand-in.
        $component = Component::factory()->create();
        $component->delete();

        $this->assertTrue($this->rowFor($component)['available_actions']['checkout']);
    }

    public function test_deleted_row_keeps_clone_available(): void
    {
        // Cloning a deleted component is explicitly allowed as a
        // restore-and-tweak shortcut: the clone produces a brand-new
        // row and leaves the deleted parent untouched.
        $component = Component::factory()->create();
        $component->delete();

        $this->assertTrue($this->rowFor($component)['available_actions']['clone']);
    }

    public function test_deleted_row_exposes_bulk_selectable_restore(): void
    {
        // The Deleted index's bulk-actions dropdown offers Restore
        // instead of Edit/Delete. The dropdown's auto-uncheck guard
        // (applyActionDependentRowStyling) reads bulk_selectable.restore
        // to decide whether a row can participate in the restore batch.
        $component = Component::factory()->create();
        $component->delete();

        $bulk = $this->rowFor($component)['available_actions']['bulk_selectable'];
        $this->assertTrue($bulk['restore']);
        $this->assertFalse($bulk['edit']);
        $this->assertFalse($bulk['delete']);
    }

    public function test_live_row_does_not_expose_restore_bulk_selectable(): void
    {
        $component = Component::factory()->create();

        $this->settings->set(['show_archived_in_list' => 1]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index'))
            ->assertOk();

        $row = collect($response->json('rows'))->firstWhere('id', $component->id);
        $this->assertNotNull($row);
        $this->assertFalse($row['available_actions']['bulk_selectable']['restore']);
    }
}
