<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateAssetModelsTest extends TestCase
{
    public function test_requires_permission_to_edit_asset_model()
    {
        $model = AssetModel::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.models.update', $model))
            ->assertForbidden();
    }

    public function test_can_update_asset_model_via_patch()
    {
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
        $this->assertEquals('Test Model', $model->name, 'Name was not updated');

    }

    public function test_cannot_update_asset_model_via_patch_with_accessory_category()
    {
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
    }

    public function test_cannot_update_asset_model_via_patch_with_license_category()
    {
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
    }

    public function test_cannot_update_asset_model_via_patch_with_consumable_category()
    {
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
    }

    public function test_cannot_update_asset_model_via_patch_with_component_category()
    {
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
    }
}
