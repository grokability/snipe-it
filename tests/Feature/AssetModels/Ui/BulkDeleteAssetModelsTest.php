<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;

test('shows confirmation when at least one model is deletable', function () {
    // One model has assets attached (not deletable), one is clean.
    $modelWithAssets = AssetModel::factory()->create();
    Asset::factory()->for($modelWithAssets, 'model')->create();
    $cleanModel = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.bulkedit.index'), [
            'ids' => [$modelWithAssets->id, $cleanModel->id],
            'bulk_actions' => 'delete',
        ])
        ->assertStatus(200)
        ->assertSee($modelWithAssets->name)
        ->assertSee($cleanModel->name);
});

test('redirects to index with error when no selected models are deletable', function () {
    // Both models have assets attached, so neither is deletable.
    $modelA = AssetModel::factory()->create();
    Asset::factory()->for($modelA, 'model')->create();
    $modelB = AssetModel::factory()->create();
    Asset::factory()->for($modelB, 'model')->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('models.index'))
        ->post(route('models.bulkedit.index'), [
            'ids' => [$modelA->id, $modelB->id],
            'bulk_actions' => 'delete',
        ])
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('error');
});

test('redirects to index when no models selected', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('models.index'))
        ->post(route('models.bulkedit.index'), [
            'ids' => null,
            'bulk_actions' => 'delete',
        ])
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('error');
});
