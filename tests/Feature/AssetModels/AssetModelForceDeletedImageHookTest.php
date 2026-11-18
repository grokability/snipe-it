<?php

use App\Models\AssetModel;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $model = AssetModel::factory()->create(['image' => 'macbook-pro.png']);
    Storage::disk('public')->put('models/macbook-pro.png', 'fake image bytes');

    $model->delete();

    Storage::disk('public')->assertExists('models/macbook-pro.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $model = AssetModel::factory()->create(['image' => 'macbook-pro.png']);
    Storage::disk('public')->put('models/macbook-pro.png', 'fake image bytes');

    $model->forceDelete();

    Storage::disk('public')->assertMissing('models/macbook-pro.png');
});
