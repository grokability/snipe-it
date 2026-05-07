<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteAssetModelsTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $assetModel = AssetModel::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.models.destroy', $assetModel))
            ->assertForbidden();

        $this->assertNotSoftDeleted($assetModel);
    }

    public function test_cannot_delete_asset_model_that_still_has_associated_assets()
    {
        $assetModel = Asset::factory()->create()->model;

        $this->actingAsForApi(User::factory()->deleteAssetModels()->create())
            ->deleteJson(route('api.models.destroy', $assetModel))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($assetModel);
    }

    public function test_can_delete_asset_model()
    {
        $assetModel = AssetModel::factory()->create();

        $this->actingAsForApi(User::factory()->deleteAssetModels()->create())
            ->deleteJson(route('api.models.destroy', $assetModel))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($assetModel);
    }
}
