<?php

namespace Tests\Feature\Categories\Ui;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class BulkDeleteCategoriesTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('categories.bulk.delete'), [
                'ids' => [1, 2, 3]
            ])
            ->assertForbidden();
    }

    public function test_category_cannot_be_bulk_deleted_if_models_still_associated()
    {
        $category = Category::factory()->create();
        AssetModel::factory()->create(['category_id' => $category->id]);
        
        $this->actingAs(User::factory()->deleteCategories()->create())
            ->post(route('categories.bulk.delete'), [
                'ids' => [$category->id]
            ]);
            
        $this->assertModelExists($category);
        $this->assertNotSoftDeleted($category);
    }

    public function test_category_can_be_bulk_deleted_if_no_models_associated()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category3 = Category::factory()->create();

        $this->actingAs(User::factory()->deleteCategories()->create())
            ->post(route('categories.bulk.delete'), [
                'ids' => [$category1->id, $category2->id, $category3->id]
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertSoftDeleted($category1);
        $this->assertSoftDeleted($category2);
        $this->assertSoftDeleted($category3);
    }


}