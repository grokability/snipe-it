<?php

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;

test('invalid custom field defaults redirect back and preserve existing defaults', function () {
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
});

test('permission required to update asset model', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('models.update', ['model' => AssetModel::factory()->create()]), [
            'name' => 'Changed Name',
            'category_id' => Category::factory()->create()->id,
        ])
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('models.edit', AssetModel::factory()->create()))
        ->assertOk();
});

test('user can edit asset models', function () {
    $category = Category::factory()->forAssets()->create();
    $model = AssetModel::factory()->create(['name' => 'Test Model', 'category_id' => $category->id]);
    expect(AssetModel::where('name', 'Test Model')->exists())->toBeTrue();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->put(route('models.update', ['model' => $model]), [
            'name' => 'Test Model Edited',
            'category_id' => $model->category_id,
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('models.index'));

    $this->followRedirects($response)->assertSee('Success');
    expect(AssetModel::where('name', 'Test Model Edited')->exists())->toBeTrue();
});

test('user cannot change asset model category type', function () {
    $category = Category::factory()->forAssets()->create();
    $model = AssetModel::factory()->create(['name' => 'Test Model', 'category_id' => $category->id]);
    expect(AssetModel::where('name', 'Test Model')->exists())->toBeTrue();

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
    expect(AssetModel::where('name', 'Test Model Edited')->exists())->toBeFalse();
});

test('default values remain unchanged after validation error occurs', function () {
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
    expect($potentiallyChangedDefaultValues)->toHaveCount(2);
    expect($potentiallyChangedDefaultValues)->toContain('first default value');
    expect($potentiallyChangedDefaultValues)->toContain('second default value');
});

test('default values can be updated', function () {
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
    expect($potentiallyChangedDefaultValues)->toHaveCount(2);
    expect($potentiallyChangedDefaultValues)->toContain('first changed value');
    expect($potentiallyChangedDefaultValues)->toContain('second changed value');
});
