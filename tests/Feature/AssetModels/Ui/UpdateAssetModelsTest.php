<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class UpdateAssetModelsTest extends TestCase
{
    public function testPermissionRequiredToStoreAssetModel()
    {
        $this->actingAs(User::factory()->create())
            ->put(route('models.update', ['model' => AssetModel::factory()->create()]), [
                'name' => 'Changed Name',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.edit', AssetModel::factory()->create()))
            ->assertOk();
    }

    public function testUserCanEditAssetModels()
    {
        $category = Category::factory()->forAssets()->create();
        $model = AssetModel::factory()->create(['name' => 'Test Model', 'category_id' => $category->id]);
        $this->assertTrue(AssetModel::where('name', 'Test Model')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('models.update', ['model' => $model]), [
                'name' => 'Test Model Edited',
                'category_id' => $model->category_id,
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('models.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertTrue(AssetModel::where('name', 'Test Model Edited')->exists());

    }

    public function testUserCannotChangeAssetModelCategoryType()
    {
        $category = Category::factory()->forAssets()->create();
        $model = AssetModel::factory()->create(['name' => 'Test Model', 'category_id' => $category->id]);
        $this->assertTrue(AssetModel::where('name', 'Test Model')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.edit', $model))
            ->put(route('models.update', $model), [
                'name' => 'Test Model Edited',
                'category_id' => Category::factory()->forAccessories()->create()->id,
            ])
            ->assertSessionHasErrors(['category_type'])
            ->assertInvalid(['category_type'])
            ->assertStatus(302)
            ->assertRedirect(route('models.edit', $model));

        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(AssetModel::where('name', 'Test Model Edited')->exists());

    }

    public function test_default_values_remain_unchanged_after_validation_error_occurs()
    {
        $this->markIncompleteIfMySQL('Custom Field Tests do not work in MySQL');

        $assetModel = AssetModel::factory()->create();
        $customFieldset = CustomFieldset::factory()->create();
        [$customFieldOne, $customFieldTwo] = CustomField::factory()->count(2)->create();

        $customFieldset->fields()->attach($customFieldOne, ['order' => 1, 'required' => false]);
        $customFieldset->fields()->attach($customFieldTwo, ['order' => 2, 'required' => false]);

        $assetModel->fieldset()->associate($customFieldset);

        $assetModel->defaultValues()->attach($customFieldOne, ['default_value' => 'first default value']);
        $assetModel->defaultValues()->attach($customFieldTwo, ['default_value' => 'second default value']);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('models.update', ['model' => $assetModel]), [
                // should trigger validation error without name, etc, and NOT remove or change default values
                'add_default_values' => '1',
                'fieldset_id' => $customFieldset->id,
                'default_values' => [
                    $customFieldOne->id => 'first changed value',
                    $customFieldTwo->id => 'second changed value',
                ],
            ]);

        $potentiallyChangedDefaultValues = $assetModel->defaultValues->pluck('pivot.default_value');
        $this->assertCount(2, $potentiallyChangedDefaultValues);
        $this->assertContains('first default value', $potentiallyChangedDefaultValues);
        $this->assertContains('second default value', $potentiallyChangedDefaultValues);
    }

    public function test_default_values_can_be_updated()
    {
        $this->markIncompleteIfMySQL('Custom Field Tests do not work in MySQL');

        $assetModel = AssetModel::factory()->create();
        $customFieldset = CustomFieldset::factory()->create();
        [$customFieldOne, $customFieldTwo] = CustomField::factory()->count(2)->create();

        $customFieldset->fields()->attach($customFieldOne, ['order' => 1, 'required' => false]);
        $customFieldset->fields()->attach($customFieldTwo, ['order' => 2, 'required' => false]);

        $assetModel->fieldset()->associate($customFieldset);

        $assetModel->defaultValues()->attach($customFieldOne, ['default_value' => 'first default value']);
        $assetModel->defaultValues()->attach($customFieldTwo, ['default_value' => 'second default value']);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('models.update', ['model' => $assetModel]), [
                // should trigger validation error without name, etc, and NOT remove or change default values
                'name' => 'Test Model Edited',
                'category_id' => $assetModel->category_id,
                'add_default_values' => '1',
                'fieldset_id' => $customFieldset->id,
                'default_values' => [
                    $customFieldOne->id => 'first changed value',
                    $customFieldTwo->id => 'second changed value',
                ],
            ]);

        $potentiallyChangedDefaultValues = $assetModel->defaultValues->pluck('pivot.default_value');
        $this->assertCount(2, $potentiallyChangedDefaultValues);
        $this->assertContains('first changed value', $potentiallyChangedDefaultValues);
        $this->assertContains('second changed value', $potentiallyChangedDefaultValues);
    }
}
