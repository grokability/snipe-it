<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

test('permission needed to delete category', function () {
    $this->actingAs(User::factory()->create())
        ->delete(route('categories.destroy', Category::factory()->create()))
        ->assertForbidden();
});

test('can delete category', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->delete(route('categories.destroy', $category))
        ->assertRedirectToRoute('categories.index')
        ->assertSessionHas('success');

    $this->assertSoftDeleted($category);
});

test('cannot delete category that still has associated models', function () {
    $model = AssetModel::factory()->create();
    $category = $model->category;

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->delete(route('categories.destroy', $category))
        ->assertRedirectToRoute('categories.index')
        ->assertSessionHas('error');
    $this->assertNotSoftDeleted($category);
});

test('cannot delete category that still has associated assets', function () {
    $asset = Asset::factory()->create();
    $category = $asset->model->category;

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->delete(route('categories.destroy', $category))
        ->assertRedirectToRoute('categories.index')
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($category);
});
