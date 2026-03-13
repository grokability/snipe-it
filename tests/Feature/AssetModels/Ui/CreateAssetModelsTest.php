<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CreateAssetModelsTest extends TestCase
{
    public function testPermissionRequiredToCreateAssetModel()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'category_id' => Category::factory()->create()->id
            ])
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.create'))
            ->assertOk();
    }

    public function testUserCanCreateAssetModels()
    {
        $this->assertFalse(AssetModel::where('name', 'Test Model')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'category_id' => Category::factory()->create()->id
            ])
            ->assertRedirect(route('models.index'));

        $this->assertTrue(AssetModel::where('name', 'Test Model')->exists());
    }

    public function testUserCannotUseAccessoryCategoryTypeAsAssetModelCategoryType()
    {

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Invalid Model Category',
                'category_id' => Category::factory()->forAccessories()->create()->id
            ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('models.create'));
        $response->assertInvalid(['category_type']);
        $response->assertSessionHasErrors(['category_type']);
        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(AssetModel::where('name', 'Test Invalid Model Category')->exists());

    }

    public function testUniquenessAcrossModelNameAndModelNumber()
    {

        AssetModel::factory()->create(['name' => 'Test Model', 'model_number'=>'1234']);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'model_number' => '1234',
                'category_id' => Category::factory()->create()->id
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['name','model_number'])
            ->assertRedirect(route('models.create'))
            ->assertInvalid(['name','model_number']);

        $this->followRedirects($response)->assertSee(trans('general.error'));

    }

    public function testUniquenessAcrossModelNameAndModelNumberWithoutModelNumber()
    {

        AssetModel::factory()->create(['name' => 'Test Model', 'model_number'=> null]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.create'))
            ->post(route('models.store'), [
                'name' => 'Test Model',
                'model_number' => null,
                'category_id' => Category::factory()->create()->id
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['name'])
            ->assertRedirect(route('models.create'))
            ->assertInvalid(['name']);

        $this->followRedirects($response)->assertSee(trans('general.error'));

    }

}
