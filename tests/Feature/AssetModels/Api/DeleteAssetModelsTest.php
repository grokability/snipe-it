<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;

test('requires permission', function () {
    $assetModel = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.models.destroy', $assetModel))
        ->assertForbidden();

    $this->assertNotSoftDeleted($assetModel);
});

test('cannot delete asset model that still has associated assets', function () {
    $assetModel = Asset::factory()->create()->model;

    $this->actingAsForApi(User::factory()->deleteAssetModels()->create())
        ->deleteJson(route('api.models.destroy', $assetModel))
        ->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($assetModel);
});

test('can delete asset model', function () {
    $assetModel = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->deleteAssetModels()->create())
        ->deleteJson(route('api.models.destroy', $assetModel))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($assetModel);
});
