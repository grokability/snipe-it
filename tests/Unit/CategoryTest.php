<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;

test('fails empty validation', function () {
    // An Asset requires a name, a qty, and a category_id.
    $a = Category::create();
    expect($a->isValid())->toBeFalse();

    $fields = [
        'name' => 'name',
        'category_type' => 'category type',
    ];
    $errors = $a->getErrors();
    foreach ($fields as $field => $fieldTitle) {
        expect($errors->get($field)[0])->toEqual("The $fieldTitle field is required.");
    }
});

test('a category can have assets', function () {
    $category = Category::factory()->assetDesktopCategory()->create();

    // Generate 5 models via factory
    $models = AssetModel::factory()
        ->count(5)
        ->create(
            [
                'category_id' => $category->id,
            ]
        );

    // Loop through the models and create 2 assets in each model
    $models->each(function ($model) {
        // dd($model);
        $asset = Asset::factory()
            ->count(2)
            ->create(
                [
                    'model_id' => $model->id,
                ]
            );
        // dd($asset);
    });

    expect($category->models)->toHaveCount(5);
    expect($category->models)->toHaveCount(5);
    expect($category->itemCount())->toEqual(10);
});
