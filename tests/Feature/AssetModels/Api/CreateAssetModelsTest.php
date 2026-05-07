<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CreateAssetModelsTest extends TestCase
{
    public function test_requires_permission_to_create_asset_model()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.models.store'))
            ->assertForbidden();
    }

    public function test_can_create_asset_model_with_asset_model_type()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.models.store'), [
                'name' => 'Test AssetModel',
                'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $this->assertTrue(AssetModel::where('name', 'Test AssetModel')->exists());

        $model = AssetModel::find($response['payload']['id']);
        $this->assertEquals('Test AssetModel', $model->name);
    }

    public function test_cannot_create_asset_model_without_category()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.models.store'), [
                'name' => 'Test AssetModel',
            ])
            ->assertStatus(200)
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertJson([
                'messages' => [
                    'category_id' => ['The category id field is required.'],
                ],
            ])
            ->json();

        $this->assertFalse(AssetModel::where('name', 'Test AssetModel')->exists());

    }

    public function test_uniqueness_across_model_name_and_model_number()
    {
        AssetModel::factory()->create(['name' => 'Test Model', 'model_number' => '1234']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.models.store'), [
                'name' => 'Test Model',
                'model_number' => '1234',
                'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
            ])
            ->assertStatus(200)
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertJson([
                'messages' => [
                    'name' => ['The name must be unique across models and model number. '],
                    'model_number' => ['The model number must be unique across models and name. '],
                ],
            ])
            ->json();

    }

    public function test_uniqueness_across_model_name_and_model_number_with_blank_model_number()
    {
        AssetModel::factory()->create(['name' => 'Test Model']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.models.store'), [
                'name' => 'Test Model',
                'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
            ])
            ->assertStatus(200)
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertJson([
                'messages' => [
                    'name' => ['The name must be unique across models and model number. '],
                ],
            ])
            ->json();

    }
}
