<?php

use App\Models\AssetModel;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\User;

test('clean depreciation is bulk selectable', function () {
    $depreciation = Depreciation::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.depreciations.show', $depreciation))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('depreciation with asset models is not bulk selectable', function () {
    $depreciation = Depreciation::factory()->create();
    AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.depreciations.show', $depreciation))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});

test('depreciation with licenses is not bulk selectable', function () {
    $depreciation = Depreciation::factory()->create();
    License::factory()->create(['depreciation_id' => $depreciation->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.depreciations.show', $depreciation))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});
