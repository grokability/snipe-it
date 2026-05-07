<?php

namespace Tests\Feature\Categories\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class DeleteCategoriesTest extends TestCase
{
    public function test_permission_needed_to_delete_category()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('categories.destroy', Category::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->deleteCategories()->create())
            ->delete(route('categories.destroy', $category))
            ->assertRedirectToRoute('categories.index')
            ->assertSessionHas('success');

        $this->assertSoftDeleted($category);
    }

    public function test_cannot_delete_category_that_still_has_associated_models()
    {
        $model = AssetModel::factory()->create();
        $category = $model->category;

        $this->actingAs(User::factory()->deleteCategories()->create())
            ->delete(route('categories.destroy', $category))
            ->assertRedirectToRoute('categories.index')
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($category);
    }

    public function test_cannot_delete_category_that_still_has_associated_assets()
    {
        $asset = Asset::factory()->create();
        $category = $asset->model->category;

        $this->actingAs(User::factory()->deleteCategories()->create())
            ->delete(route('categories.destroy', $category))
            ->assertRedirectToRoute('categories.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($category);
    }
}
