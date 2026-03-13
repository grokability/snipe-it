<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateAssetModelsTest extends TestCase
{

    public function testRequiresPermissionToEditAssetModel()
    {
        $model = AssetModel::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.models.update', $model))
            ->assertForbidden();
    }

    public function testCanUpdateAssetModelViaPatch()
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

    public function testCannotUpdateAssetModelViaPatchWithAccessoryCategory()
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

    public function testCannotUpdateAssetModelViaPatchWithLicenseCategory()
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

    public function testCannotUpdateAssetModelViaPatchWithConsumableCategory()
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

    public function testCannotUpdateAssetModelViaPatchWithComponentCategory()
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
