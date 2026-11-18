<?php

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

test('requires permission to create asset model', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.models.store'))
        ->assertForbidden();
});

test('can create asset model with asset model type', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.store'), [
            'name' => 'Test AssetModel',
            'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->json();

    expect(AssetModel::where('name', 'Test AssetModel')->exists())->toBeTrue();

    $model = AssetModel::find($response['payload']['id']);
    expect($model->name)->toEqual('Test AssetModel');
});

test('cannot create asset model without category', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.store'), [
            'name' => 'Test AssetModel',
        ])
        ->assertStatus(200)
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertJson([
            'messages' => [
                'category_id' => ['The category id field is required.'],
            ],
        ])
        ->json();

    expect(AssetModel::where('name', 'Test AssetModel')->exists())->toBeFalse();
});

test('uniqueness across model name and model number', function () {
    AssetModel::factory()->create(['name' => 'Test Model', 'model_number' => '1234']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.store'), [
            'name' => 'Test Model',
            'model_number' => '1234',
            'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
        ])
        ->assertStatus(200)
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertJson([
            'messages' => [
                'name' => ['The name must be unique across models and model number. '],
                'model_number' => ['The model number must be unique across models and name. '],
            ],
        ])
        ->json();
});

test('uniqueness across model name and model number with blank model number', function () {
    AssetModel::factory()->create(['name' => 'Test Model']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.models.store'), [
            'name' => 'Test Model',
            'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
        ])
        ->assertStatus(200)
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertJson([
            'messages' => [
                'name' => ['The name must be unique across models and model number. '],
            ],
        ])
        ->json();
});
