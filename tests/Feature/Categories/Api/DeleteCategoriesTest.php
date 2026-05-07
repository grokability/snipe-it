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
    public function test_requires_permission()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertForbidden();

        $this->assertNotSoftDeleted($category);
    }

    public function test_cannot_delete_category_that_still_has_associated_assets()
    {
        $asset = Asset::factory()->create();
        $category = $asset->model->category;

        $this->actingAsForApi(User::factory()->deleteCategories()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($category);
    }

    public function test_cannot_delete_category_that_still_has_associated_models()
    {
        $model = AssetModel::factory()->create();
        $category = $model->category;

        $this->actingAsForApi(User::factory()->deleteCategories()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($category);
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->deleteCategories()->create())
            ->deleteJson(route('api.categories.destroy', $category))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($category);
    }
}
