<?php

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('categories.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('category cannot be bulk deleted if models still associated', function () {
    $category = Category::factory()->create();
    AssetModel::factory()->create(['category_id' => $category->id]);

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->post(route('categories.bulk.delete'), [
            'ids' => [$category->id],
        ]);

    $this->assertModelExists($category);
    $this->assertNotSoftDeleted($category);
});

test('category can be bulk deleted if no models associated', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $category3 = Category::factory()->create();

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->post(route('categories.bulk.delete'), [
            'ids' => [$category1->id, $category2->id, $category3->id],
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertSoftDeleted($category1);
    $this->assertSoftDeleted($category2);
    $this->assertSoftDeleted($category3);
});

test('bulk success message pluralizes by count', function () {
    $solo = Category::factory()->create();

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->post(route('categories.bulk.delete'), [
            'ids' => [$solo->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/categories/message.delete.bulk_success', 1, ['count' => 1]));

    $a = Category::factory()->create();
    $b = Category::factory()->create();
    $c = Category::factory()->create();

    $this->actingAs(User::factory()->deleteCategories()->create())
        ->post(route('categories.bulk.delete'), [
            'ids' => [$a->id, $b->id, $c->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/categories/message.delete.bulk_success', 3, ['count' => 3]));
});
