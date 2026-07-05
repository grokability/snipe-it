<?php

namespace Tests\Feature\Depreciations\Api;

use App\Models\AssetModel;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

/**
 * Verifies the JS-visible flag that drives the bulk-delete checkbox on the
 * depreciations index. The bootstrap-table `checkboxEnabledFormatter` reads
 * `available_actions.bulk_selectable.delete` and disables the row's checkbox
 * when every entry there is false. A depreciation attached to any asset,
 * asset model, or license must therefore report `bulk_selectable.delete === false`;
 * a clean depreciation must report `true`.
 */
class BulkSelectableTest extends TestCase
{
    public function test_clean_depreciation_is_bulk_selectable()
    {
        $depreciation = Depreciation::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.depreciations.show', $depreciation))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', true);
    }

    public function test_depreciation_with_asset_models_is_not_bulk_selectable()
    {
        $depreciation = Depreciation::factory()->create();
        AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.depreciations.show', $depreciation))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }

    public function test_depreciation_with_licenses_is_not_bulk_selectable()
    {
        $depreciation = Depreciation::factory()->create();
        License::factory()->create(['depreciation_id' => $depreciation->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.depreciations.show', $depreciation))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }
}
