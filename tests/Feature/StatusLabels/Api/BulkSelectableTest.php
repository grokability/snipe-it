<?php

namespace Tests\Feature\StatusLabels\Api;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Verifies the JS-visible flag that drives the bulk-delete checkbox on the
 * status-labels index. The bootstrap-table `checkboxEnabledFormatter` reads
 * `available_actions.bulk_selectable.delete` and disables the row's checkbox
 * when every entry there is false. A status label with any associated asset
 * must therefore report `bulk_selectable.delete === false`; a clean label
 * must report `true`.
 */
class BulkSelectableTest extends TestCase
{
    public function test_clean_status_label_is_bulk_selectable()
    {
        $statusLabel = Statuslabel::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.statuslabels.show', $statusLabel))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', true);
    }

    public function test_status_label_with_assets_is_not_bulk_selectable()
    {
        $statusLabel = Statuslabel::factory()->create();
        Asset::factory()->create(['status_id' => $statusLabel->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.statuslabels.show', $statusLabel))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }
}
