<?php

namespace Tests\Feature\CustomFields;

use App\Http\Transformers\AssetModelsTransformer;
use App\Livewire\CustomFieldSetDefaultValuesForModel;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldDefault;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CustomFieldDefaultsTest extends TestCase
{
    public function test_defaults_are_eloquent_models_that_work_through_both_existing_relationships(): void
    {
        $default = CustomFieldDefault::factory()->create(['default_value' => '0']);
        $owner = $default->owner;
        $field = $default->field;

        $this->assertNotNull($default->id);
        $this->assertTrue($default->owner->is($owner));
        $this->assertTrue($default->field->is($field));
        $this->assertTrue(CustomFieldset::findOrFail($owner->fieldset_id)->fields->contains($field));
        $relatedField = $owner->defaultValues()->firstOrFail();
        $this->assertInstanceOf(CustomFieldDefault::class, $relatedField->pivot);
        $this->assertSame($default->id, $relatedField->pivot->id);
        $relatedField->pivot->default_value = 'updated';
        $this->assertTrue($relatedField->pivot->save());
        $this->assertSame('updated', $default->fresh()->default_value);
        $this->assertSame(1, CustomFieldDefault::query()->count());
        $inverse = $field->defaultValues()->firstOrFail();
        $this->assertInstanceOf(CustomFieldDefault::class, $inverse->pivot);
        $this->assertSame('updated', $inverse->pivot->default_value);
    }

    public function test_the_trait_discovers_the_owner_using_the_relationship_keys(): void
    {
        $field = CustomField::factory()->ram()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => '16']);
        $item = new DefaultsThroughAsset;
        $item->setRawAttributes(['alternate_model_id' => $owner->id]);

        $this->assertSame([$field->db_column => '16'], $item->customFieldDefaults());
        $item->alternate_model_id = null;
        $this->assertSame([], $item->customFieldDefaults());
    }

    public function test_defaults_follow_owner_changes_without_a_cache(): void
    {
        $field = CustomField::factory()->ram()->create();
        $first = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $second = AssetModel::factory()->create(['fieldset_id' => $first->fieldset_id]);
        $first->defaultValues()->attach($field, ['default_value' => '16']);
        $second->defaultValues()->attach($field, ['default_value' => '32']);
        $asset = new Asset(['model_id' => $first->id]);

        $this->assertSame([$field->db_column => '16'], $asset->customFieldDefaults());
        $asset->model_id = $second->id;
        $this->assertSame([$field->db_column => '32'], $asset->customFieldDefaults());
    }

    public function test_defaults_can_be_read_for_a_soft_deleted_through_owner(): void
    {
        $field = CustomField::factory()->ram()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => '16']);
        $owner->delete();
        $asset = new Asset(['model_id' => $owner->id]);

        $this->assertSame([$field->db_column => '16'], $asset->customFieldDefaults());
    }

    public function test_a_direct_relationship_uses_the_consuming_object_as_owner(): void
    {
        $item = new DefaultsDirectAsset;

        $this->assertSame($item, CustomFieldDefault::ownerFor($item, $item->custom_fieldset()));
        $this->assertSame([], $item->customFieldDefaults());
    }

    public function test_an_unknown_through_owner_returns_no_defaults_even_with_a_colliding_id(): void
    {
        $field = CustomField::factory()->ram()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => '16']);
        $otherOwner = new User;
        $otherOwner->id = $owner->id;
        $otherOwner->exists = true;

        $this->assertSame([], CustomFieldDefault::valuesFor($otherOwner));
    }

    public function test_saving_defaults_preserves_zero_and_flattens_checkbox_values(): void
    {
        $zero = CustomField::factory()->create();
        $checkbox = CustomField::factory()->create(['element' => 'checkbox', 'field_values' => "one\ntwo"]);
        $owner = AssetModel::factory()->hasMultipleCustomFields([$zero, $checkbox])->create();
        $fieldset = CustomFieldset::findOrFail($owner->fieldset_id);

        CustomFieldDefault::saveFor($owner, $fieldset, [$zero->id => '0', $checkbox->id => ['one', 'two']]);

        $this->assertSame([$zero->db_column => '0', $checkbox->db_column => 'one, two'], CustomFieldDefault::valuesFor($owner));
        CustomFieldDefault::saveFor($owner, $fieldset, [$zero->id => false]);
        $this->assertSame([$zero->db_column => '0'], CustomFieldDefault::valuesFor($owner));
    }

    public function test_invalid_defaults_do_not_replace_existing_values(): void
    {
        $field = CustomField::factory()->create(['format' => 'numeric']);
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => '16']);

        try {
            CustomFieldDefault::saveFor($owner, CustomFieldset::findOrFail($owner->fieldset_id), [$field->id => 'invalid']);
            $this->fail('Invalid defaults should fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field->db_column, $exception->errors());
        }

        $this->assertSame([$field->db_column => '16'], CustomFieldDefault::valuesFor($owner));
    }

    public function test_fields_outside_the_fieldset_are_rejected_without_losing_defaults(): void
    {
        [$field, $outside] = CustomField::factory()->count(2)->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => 'original']);

        try {
            CustomFieldDefault::saveFor($owner, CustomFieldset::findOrFail($owner->fieldset_id), [$outside->id => 'invalid']);
            $this->fail('Unattached fields should fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('default_values.'.$outside->id, $exception->errors());
        }

        $this->assertSame([$field->db_column => 'original'], CustomFieldDefault::valuesFor($owner));
    }

    public function test_required_fields_can_have_no_default_and_existing_defaults_can_be_cleared(): void
    {
        $field = CustomField::factory()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $fieldset = CustomFieldset::findOrFail($owner->fieldset_id);
        $fieldset->fields()->updateExistingPivot($field, ['required' => true]);
        $owner->defaultValues()->attach($field, ['default_value' => 'original']);

        CustomFieldDefault::saveFor($owner, $fieldset, [$field->id => null]);
        $this->assertSame([], CustomFieldDefault::valuesFor($owner));
    }

    public function test_zero_defaults_survive_a_model_update_and_appear_in_the_api_and_editor(): void
    {
        $field = CustomField::factory()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $user = User::factory()->superuser()->create();

        $this->actingAs($user)->put(route('models.update', $owner), [
            'name' => $owner->name,
            'category_id' => $owner->category_id,
            'fieldset_id' => $owner->fieldset_id,
            'add_default_values' => '1',
            'default_values' => [$field->id => '0'],
        ])->assertRedirect(route('models.index'))->assertSessionHasNoErrors();

        $owner->refresh();
        $this->assertSame([$field->db_column => '0'], CustomFieldDefault::valuesFor($owner));
        $data = (new AssetModelsTransformer)->transformAssetModel($owner);
        $this->assertSame('0', $data['default_fieldset_values'][0]['default_value']);
        Livewire::test(CustomFieldSetDefaultValuesForModel::class, ['model_id' => $owner->id])
            ->assertSet('add_default_values', true)
            ->assertSet('selectedValues.'.$field->db_column, '0');
    }

    public function test_a_model_update_with_invalid_defaults_preserves_the_previous_defaults(): void
    {
        $field = CustomField::factory()->create(['format' => 'numeric']);
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => '16']);

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('models.edit', $owner))
            ->put(route('models.update', $owner), [
                'name' => $owner->name,
                'category_id' => $owner->category_id,
                'fieldset_id' => $owner->fieldset_id,
                'add_default_values' => '1',
                'default_values' => [$field->id => 'invalid'],
            ])->assertRedirect(route('models.edit', $owner))->assertSessionHasErrors($field->db_column);

        $this->assertSame([$field->db_column => '16'], CustomFieldDefault::valuesFor($owner->fresh()));
    }

    public function test_disabling_defaults_on_a_model_removes_the_saved_values(): void
    {
        $field = CustomField::factory()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => 'original']);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('models.update', $owner), [
                'name' => $owner->name,
                'category_id' => $owner->category_id,
                'fieldset_id' => $owner->fieldset_id,
            ])->assertRedirect(route('models.index'))->assertSessionHasNoErrors();

        $this->assertSame([], CustomFieldDefault::valuesFor($owner->fresh()));
    }
}

class DefaultsThroughAsset extends Asset
{
    public function custom_fieldset(): Relation
    {
        return $this->hasOneThrough(CustomFieldset::class, AssetModel::class, 'id', 'id', 'alternate_model_id', 'fieldset_id');
    }
}

class DefaultsDirectAsset extends Asset
{
    public function custom_fieldset(): Relation
    {
        return $this->belongsTo(CustomFieldset::class, 'fieldset_id');
    }
}
