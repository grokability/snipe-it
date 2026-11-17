<?php

namespace Tests\Feature\CustomFields;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Tests\TestCase;

class AssignedToInUseFieldsetsTest extends TestCase
{
    public function test_only_fields_in_fieldsets_assigned_to_non_deleted_models_are_selected(): void
    {
        CustomField::factory()->create();
        $unassigned = CustomField::factory()->create();
        $unassignedFieldset = CustomFieldset::factory()->create();
        $unassignedFieldset->fields()->attach($unassigned, ['order' => 1, 'required' => false]);

        $deleted = CustomField::factory()->create();
        $deletedModel = AssetModel::factory()->hasMultipleCustomFields([$deleted])->create();
        $deletedModel->delete();

        $used = CustomField::factory()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$used])->create();

        $this->assertSame(0, $owner->assets()->count());
        $this->assertSame([$used->id], CustomField::assignedToInUseFieldsets()->pluck('id')->all());
    }

    public function test_additional_filters_compose_without_duplicating_fields_used_by_multiple_models_or_fieldsets(): void
    {
        $visible = CustomField::factory()->create(['show_in_requestable_list' => true]);
        $hidden = CustomField::factory()->create(['show_in_requestable_list' => false]);
        $encrypted = CustomField::factory()->encrypt()->create(['show_in_requestable_list' => true]);
        $owner = AssetModel::factory()->hasMultipleCustomFields([$visible, $hidden, $encrypted])->create();
        AssetModel::factory()->create(['fieldset_id' => $owner->fieldset_id]);
        AssetModel::factory()->hasMultipleCustomFields([$visible])->create();

        $this->assertEqualsCanonicalizing(
            [$visible->id, $hidden->id, $encrypted->id],
            CustomField::assignedToInUseFieldsets()->pluck('id')->all(),
        );
        $this->assertSame(
            [$visible->id],
            CustomField::assignedToInUseFieldsets()
                ->where('field_encrypted', 0)
                ->where('show_in_requestable_list', 1)
                ->pluck('id')->all(),
        );
    }

    public function test_restoring_a_model_makes_its_fields_in_use_again(): void
    {
        $field = CustomField::factory()->create();
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->delete();

        $this->assertFalse(CustomField::assignedToInUseFieldsets()->whereKey($field->id)->exists());
        $this->assertTrue($owner->restore());
        $this->assertTrue(CustomField::assignedToInUseFieldsets()->whereKey($field->id)->exists());
    }
}
