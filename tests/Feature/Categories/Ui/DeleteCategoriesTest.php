<?php

namespace Tests\Feature\Categories\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteCategoriesTest extends TestCase
{
    public function testPermissionNeededToDeleteCategory()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('categories.destroy', Category::factory()->create()))
            ->assertForbidden();
    }

    public function testCanDeleteCategory()
    {
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->deleteCategories()->create())
            ->delete(route('categories.destroy', $category))
            ->assertRedirectToRoute('categories.index')
            ->assertSessionHas('success');

        $this->assertSoftDeleted($category);
    }

    public function testCannotDeleteCategoryThatStillHasAssociatedModels()
    {
        $model = AssetModel::factory()->create();
        $category = $model->category;

        $this->actingAs(User::factory()->deleteCategories()->create())
            ->delete(route('categories.destroy', $category))
            ->assertRedirectToRoute('categories.index')
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($category);
    }

    public function testCannotDeleteCategoryThatStillHasAssociatedAssets()
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
