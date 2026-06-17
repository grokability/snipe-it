<?php

namespace Tests\Unit\Models;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CustomFieldsetLogicTest extends TestCase
{
    public function test_validation_rules_returns_array(): void
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->assertIsArray($fieldset->validation_rules());
    }

    public function test_fields_relation_returns_collection(): void
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->assertInstanceOf(Collection::class, $fieldset->fields()->get());
    }

    public function test_display_any_fields_in_form_returns_collection(): void
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->assertNotNull($fieldset->displayAnyFieldsInForm());
    }

    public function test_validation_rules_with_attached_field(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create();
        $fieldset->fields()->attach($field->id, ['required' => true, 'order' => 1]);

        $rules = $fieldset->validation_rules();

        $this->assertIsArray($rules);
    }
}
