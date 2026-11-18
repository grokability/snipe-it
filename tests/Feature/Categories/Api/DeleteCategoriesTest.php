<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

test('requires permission', function () {
    $category = Category::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.categories.destroy', $category))
        ->assertForbidden();

    $this->assertNotSoftDeleted($category);
});

test('cannot delete category that still has associated assets', function () {
    $asset = Asset::factory()->create();
    $category = $asset->model->category;

    $this->actingAsForApi(User::factory()->deleteCategories()->create())
        ->deleteJson(route('api.categories.destroy', $category))
        ->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($category);
});

test('cannot delete category that still has associated models', function () {
    $model = AssetModel::factory()->create();
    $category = $model->category;

    $this->actingAsForApi(User::factory()->deleteCategories()->create())
        ->deleteJson(route('api.categories.destroy', $category))
        ->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($category);
});

test('can delete category', function () {
    $category = Category::factory()->create();

    $this->actingAsForApi(User::factory()->deleteCategories()->create())
        ->deleteJson(route('api.categories.destroy', $category))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($category);
});
