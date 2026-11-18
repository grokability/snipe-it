<?php

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;

test('invalid custom field defaults redirect back with errors and input', function () {
    $field = CustomField::factory()->create(['format' => 'email']);
    $fieldset = CustomFieldset::factory()->create();
    $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('models.create'))
        ->post(route('models.store'), [
            'name' => 'Model With Invalid Default',
            'category_id' => Category::factory()->create()->id,
            'fieldset_id' => $fieldset->id,
            'add_default_values' => '1',
            'default_values' => [$field->id => 'invalid-email'],
        ])
        ->assertRedirect(route('models.create'))
        ->assertSessionHasErrors([$field->db_column])
        ->assertSessionHasInput('default_values.'.$field->id, 'invalid-email');

    $this->assertDatabaseMissing('models_custom_fields', ['custom_field_id' => $field->id]);
});

test('permission required to create asset model', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('models.store'), [
            'name' => 'Test Model',
            'category_id' => Category::factory()->create()->id,
        ])
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('models.create'))
        ->assertOk();
});

test('user can create asset models', function () {
    expect(AssetModel::where('name', 'Test Model')->exists())->toBeFalse();

    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('models.create'))
        ->post(route('models.store'), [
            'name' => 'Test Model',
            'category_id' => Category::factory()->create()->id,
        ])
        ->assertRedirect(route('models.index'));

    expect(AssetModel::where('name', 'Test Model')->exists())->toBeTrue();
});

test('user cannot use accessory category type as asset model category type', function () {
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
    expect(AssetModel::where('name', 'Test Invalid Model Category')->exists())->toBeFalse();
});

test('uniqueness across model name and model number', function () {
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
});

test('uniqueness across model name and model number without model number', function () {
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
});
