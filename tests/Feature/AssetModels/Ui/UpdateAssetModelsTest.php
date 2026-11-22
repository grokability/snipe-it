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
    public function test_invalid_custom_field_defaults_redirect_back_and_preserve_existing_defaults(): void
    {
        $field = CustomField::factory()->create(['format' => 'email']);
        $model = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $model->defaultValues()->attach($field, ['default_value' => 'existing@example.org']);

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.edit', $model))
            ->put(route('models.update', $model), [
                'name' => $model->name,
                'category_id' => $model->category_id,
                'fieldset_id' => $model->fieldset_id,
                'add_default_values' => '1',
                'default_values' => [$field->id => 'invalid-email'],
            ])
            ->assertRedirect(route('models.edit', $model))
            ->assertSessionHasErrors([$field->db_column])
            ->assertSessionHasInput('default_values.'.$field->id, 'invalid-email');

        $this->assertDatabaseHas('models_custom_fields', [
            'asset_model_id' => $model->id,
            'custom_field_id' => $field->id,
            'default_value' => 'existing@example.org',
        ]);
    }

    public function test_permission_required_to_update_asset_model()
    {
        $this->actingAs(User::factory()->create())
            ->put(route('models.update', ['model' => AssetModel::factory()->create()]), [
                'name' => 'Changed Name',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.edit', AssetModel::factory()->create()))
            ->assertOk();
    }

    public function test_user_can_edit_asset_models()
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

    public function test_user_can_persist_manufacturer_fields_on_update()
    {
        // Regression for issue #20113. The web controller enumerates
        // each field explicitly on update() and used to skip
        // mfg_marketing_name, mfg_release_date, mfg_discontinued_date.
        // The three columns are in $fillable + have validation rules,
        // so the API path (which uses fill()) was fine, but every
        // save from the UI dropped the values silently.
        $category = Category::factory()->forAssets()->create();
        $model = AssetModel::factory()->create(['category_id' => $category->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('models.update', ['model' => $model]), [
                'name' => $model->name,
                'category_id' => $model->category_id,
                'mfg_marketing_name' => 'iPhone 20 Pro Max',
                'mfg_release_date' => '2027-09-15',
                'mfg_discontinued_date' => '2029-09-15',
            ])
            ->assertRedirect(route('models.index'))
            ->assertSessionHasNoErrors();

        $fresh = $model->fresh();
        $this->assertSame('iPhone 20 Pro Max', $fresh->mfg_marketing_name);
        $this->assertSame('2027-09-15', $fresh->mfg_release_date->toDateString());
        $this->assertSame('2029-09-15', $fresh->mfg_discontinued_date->toDateString());
    }

    public function test_user_cannot_change_asset_model_category_type()
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

        $assetModel->fieldset_id = $customFieldset->id;

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

        $assetModel->fieldset_id = $customFieldset->id;

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
