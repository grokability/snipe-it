<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class ShowAssetTest extends TestCase
{
    public function test_permission_required_to_view_asset()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hardware.show', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_view_asset()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.show', $asset))
            ->assertSeeText($asset->asset_tag)
            ->assertOk();
    }

    public function test_page_renders_when_journal_note_has_no_author()
    {
        $asset = Asset::factory()->create();

        Actionlog::factory()->for($asset, 'item')->create([
            'action_type' => 'note added',
            'item_type' => Asset::class,
            'note' => 'A note with no author',
            'created_by' => null,
        ]);

        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk();
    }

    public function test_maintenance_tab_ships_complete_button_infrastructure()
    {
        // Regression guard for #issue: the mark-complete button previously
        // rendered only on the maintenances index page because the custom
        // maintenancesActionsFormatter override lived in that page's inline
        // <script>. It now lives in partials/bootstrap-table.blade.php and
        // the confirmation modal in x-modals.maintenance-complete, so any
        // page rendering the maintenances table (including the asset view)
        // must ship both.
        $asset = Asset::factory()->create();

        $response = $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk();

        $response->assertSee('id="completeMaintenanceModal"', false);
        $response->assertSee('maintenancesActionsFormatter', false);
        $response->assertSee('complete-maintenance', false);
    }

    public function test_page_for_asset_with_missing_model_still_renders()
    {
        $asset = Asset::factory()->create();

        $asset->model_id = null;
        $asset->forceSave();

        $asset->refresh();

        $this->assertNull($asset->fresh()->model_id, 'This test needs model_id to be null to be helpful.');

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk();
    }
}
