<?php

namespace Tests\Unit\Models;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre validation_rules() de CustomFieldset, recorriendo el switch de formatos
 * encriptados y las ramas not_array/checkboxes/radio_buttons, mas displayAnyFieldsInForm.
 */
class CustomFieldsetValidationRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function attach(CustomFieldset $fieldset, CustomField $field, bool $required = false): void
    {
        $fieldset->fields()->attach($field->id, ['required' => $required ? 1 : 0, 'order' => 1]);
    }

    public function test_validation_rules_for_encrypted_formats(): void
    {
        $fieldset = CustomFieldset::factory()->create();

        $formats = ['NUMERIC', 'ALPHA', 'EMAIL', 'DATE', 'URL', 'IP', 'IPV4', 'IPV6', 'MAC', 'BOOLEAN'];
        foreach ($formats as $i => $format) {
            $field = CustomField::factory()->create([
                'name' => 'Enc '.$format.' '.$i,
                'format' => $format,
                'field_encrypted' => '1',
            ]);
            $this->attach($fieldset, $field, $i % 2 === 0);
        }

        $fieldset->load('fields');
        $rules = $fieldset->validation_rules();

        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);

        // Cada campo encriptado debe incluir una regla de objeto Rule (encrypted) y not_array.
        foreach ($rules as $columnRules) {
            $this->assertContains('not_array', $columnRules);
        }
    }

    public function test_validation_rules_with_unique_and_required_field(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create([
            'name' => 'Unique Numeric',
            'format' => 'NUMERIC',
            'is_unique' => '1',
        ]);
        $this->attach($fieldset, $field, true);

        $fieldset->load('fields');
        $rules = $fieldset->validation_rules();
        $col = $field->db_column_name();

        $this->assertContains('required', $rules[$col]);
        $this->assertContains('unique_undeleted', $rules[$col]);
    }

    public function test_validation_rules_for_checkbox_and_radio(): void
    {
        $fieldset = CustomFieldset::factory()->create();

        $checkbox = CustomField::factory()->testCheckbox()->create();
        $radio = CustomField::factory()->testRadio()->create();
        $this->attach($fieldset, $checkbox);
        $this->attach($fieldset, $radio);

        $fieldset->load('fields');
        $rules = $fieldset->validation_rules();

        $this->assertContains('checkboxes', $rules[$checkbox->db_column_name()]);
        $this->assertContains('radio_buttons', $rules[$radio->db_column_name()]);
        // checkbox NO debe llevar not_array.
        $this->assertNotContains('not_array', $rules[$checkbox->db_column_name()]);
    }

    public function test_display_any_fields_in_form_branches(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create([
            'display_audit' => '1',
            'display_checkin' => '0',
            'display_checkout' => '1',
        ]);
        $this->attach($fieldset, $field);
        $fieldset->load('fields');

        $this->assertTrue($fieldset->displayAnyFieldsInForm('audit'));
        $this->assertFalse($fieldset->displayAnyFieldsInForm('checkin'));
        $this->assertTrue($fieldset->displayAnyFieldsInForm('checkout'));
        $this->assertTrue($fieldset->displayAnyFieldsInForm());
    }
}
