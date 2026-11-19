<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Depreciation;
use App\Models\License;

test('a depreciation has models', function () {
    $depreciation = Depreciation::factory()->create();

    AssetModel::factory()
        ->count(5)
        ->create(
            [
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'depreciation_id' => $depreciation->id,
            ]);

    expect($depreciation->models->count())->toEqual(5);
});

test('depreciation amount', function () {
    $depreciation = Depreciation::factory()->create([
        'depreciation_type' => 'amount',
        'depreciation_min' => 1000,
        'months' => 36,
    ]);

    $asset = Asset::factory()
        ->laptopMbp()
        ->create(
            [
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'purchase_date' => now()->subDecade(),
                'purchase_cost' => 4000,
            ]);
    $asset->model->update([
        'depreciation_id' => $depreciation->id,
    ]);

    $asset->getLinearDepreciatedValue();

    expect($asset->getLinearDepreciatedValue())->toEqual($depreciation->depreciation_min);
});

test('depreciation percentage', function () {
    $depreciation = Depreciation::factory()->create([
        'depreciation_type' => 'percent',
        'depreciation_min' => 50,
        'months' => 36,
    ]);

    $asset = Asset::factory()
        ->laptopMbp()
        ->create(
            [
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'purchase_date' => now()->subDecade(),
                'purchase_cost' => 4000,
            ]);
    $asset->model->update([
        'depreciation_id' => $depreciation->id,
    ]);

    $asset->getLinearDepreciatedValue();

    expect($asset->getLinearDepreciatedValue())->toEqual(2000);
});

test('a depreciation has licenses', function () {
    $depreciation = Depreciation::factory()->create();
    License::factory()
        ->count(5)
        ->photoshop()
        ->create(
            [
                'category_id' => Category::factory()->licenseGraphicsCategory()->create(),
                'depreciation_id' => $depreciation->id,
            ]);

    expect($depreciation->licenses()->count())->toEqual(5);
});
