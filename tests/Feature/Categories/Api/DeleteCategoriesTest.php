<?php

namespace Tests\Feature\Categories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteCategoriesTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertForbidden();

        $this->assertNotSoftDeleted($category);
    }

    public function testCannotDeleteCategoryThatStillHasAssociatedAssets()
    {
        $asset = Asset::factory()->create();
        $category = $asset->model->category;

        $this->actingAsForApi(User::factory()->deleteCategories()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($category);
    }

    public function testCannotDeleteCategoryThatStillHasAssociatedModels()
    {
        $model = AssetModel::factory()->create();
        $category = $model->category;

        $this->actingAsForApi(User::factory()->deleteCategories()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($category);
    }

    public function testCanDeleteCategory()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->deleteCategories()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($category);
    }
}
