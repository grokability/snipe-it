<?php

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

test('requires permission to edit asset model', function () {
    $model = AssetModel::factory()->create();
    $this->actingAsForApi(User::factory()->create())
        ->patchJson(route('api.models.update', $model))
        ->assertForbidden();
});

test('can update asset model via patch', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.models.update', $model), [
            'name' => 'Test Model',
            'category_id' => Category::factory()->forAssets()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->json();

    $model->refresh();
    expect($model->name)->toEqual('Test Model', 'Name was not updated');
});

test('cannot update asset model via patch with accessory category', function () {
    $category = Category::factory()->forAccessories()->create();
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.models.update', $model), [
            'name' => 'Test Model',
            'category_id' => $category->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    $category->refresh();
    $this->assertNotEquals('Test Model', $model->name, 'Name was not updated');
    $this->assertNotEquals('category_id', $category->id, 'Category ID was not updated');
});

test('cannot update asset model via patch with license category', function () {
    $category = Category::factory()->forLicenses()->create();
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.models.update', $model), [
            'name' => 'Test Model',
            'category_id' => $category->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    $category->refresh();
    $this->assertNotEquals('Test Model', $model->name, 'Name was not updated');
    $this->assertNotEquals('category_id', $category->id, 'Category ID was not updated');
});

test('cannot update asset model via patch with consumable category', function () {
    $category = Category::factory()->forConsumables()->create();
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.models.update', $model), [
            'name' => 'Test Model',
            'category_id' => $category->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    $category->refresh();
    $this->assertNotEquals('Test Model', $model->name, 'Name was not updated');
    $this->assertNotEquals('category_id', $category->id, 'Category ID was not updated');
});

test('cannot update asset model via patch with component category', function () {
    $category = Category::factory()->forComponents()->create();
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.models.update', $model), [
            'name' => 'Test Model',
            'category_id' => $category->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    $category->refresh();
    $this->assertNotEquals('Test Model', $model->name, 'Name was not updated');
    $this->assertNotEquals('category_id', $category->id, 'Category ID was not updated');
});
