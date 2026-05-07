<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CreateAssetModelsTest extends TestCase
{
    public function test_permission_required_to_create_asset_model()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.create'))
            ->assertOk();
    }

    public function test_user_can_create_asset_models()
    {
        $this->assertFalse(AssetModel::where('name', 'Test Model')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertRedirect(route('models.index'));

        $this->assertTrue(AssetModel::where('name', 'Test Model')->exists());
    }

    public function test_user_cannot_use_accessory_category_type_as_asset_model_category_type()
    {

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Invalid Model Category',
                'category_id' => Category::factory()->forAccessories()->create()->id,
            ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('models.create'));
        $response->assertInvalid(['category_type']);
        $response->assertSessionHasErrors(['category_type']);
        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(AssetModel::where('name', 'Test Invalid Model Category')->exists());

    }

    public function test_uniqueness_across_model_name_and_model_number()
    {

        AssetModel::factory()->create(['name' => 'Test Model', 'model_number' => '1234']);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'model_number' => '1234',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['name', 'model_number'])
            ->assertRedirect(route('models.create'))
            ->assertInvalid(['name', 'model_number']);

        $this->followRedirects($response)->assertSee(trans('general.error'));

    }

    public function test_uniqueness_across_model_name_and_model_number_without_model_number()
    {

        AssetModel::factory()->create(['name' => 'Test Model', 'model_number' => null]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'model_number' => null,
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['name'])
            ->assertRedirect(route('models.create'))
            ->assertInvalid(['name']);

        $this->followRedirects($response)->assertSee(trans('general.error'));

    }
}
