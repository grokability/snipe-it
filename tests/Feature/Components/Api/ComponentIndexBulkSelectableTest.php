<?php

namespace Tests\Feature\Components\Api;

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

/**
 * The index payload's available_actions.bulk_selectable drives the
 * row-checkbox gating on the components list (see
 * applyActionDependentRowStyling + checkboxEnabledFormatter in
 * partials/bootstrap-table). Both keys must flow through for every
 * row so the dropdown-driven disable can react correctly, otherwise
 * the "pick delete, inapplicable rows auto-uncheck" flow silently
 * breaks.
 */
class ComponentIndexBulkSelectableTest extends TestCase
{
    public function test_deletable_component_reports_edit_and_delete_selectable(): void
    {
        $component = Component::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $component->id,
            ])
            ->assertJsonPath('rows.0.available_actions.bulk_selectable.edit', true)
            ->assertJsonPath('rows.0.available_actions.bulk_selectable.delete', true);
    }

    public function test_component_with_active_checkouts_reports_delete_unselectable(): void
    {
        $component = Component::factory()->create(['qty' => 10]);
        $component->assets()->attach(Asset::factory()->create()->id, [
            'assigned_qty' => 2,
            'created_at' => now(),
            'created_by' => 1,
        ]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index'))
            ->assertOk();

        $row = collect($response->json('rows'))->firstWhere('id', $component->id);
        $this->assertNotNull($row);
        $this->assertTrue($row['available_actions']['bulk_selectable']['edit']);
        $this->assertFalse($row['available_actions']['bulk_selectable']['delete']);
    }
}
