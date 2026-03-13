<?php

namespace Tests\Unit\Models\ReportTemplates;

use App\Models\Department;
use App\Models\Location;
use App\Models\ReportTemplate;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('custom-reporting')]
class ReportTemplateParsingValuesTest extends TestCase
{
    public function testParsingValuesOnNonExistentReportTemplate()
    {
        $unsavedTemplate = new ReportTemplate;

        // checkmarkValue() should be "checked" (1) by default
        $this->assertEquals('1', $unsavedTemplate->checkmarkValue('is_a_checkbox_field'));

        // radioValue() defaults to false but can be overridden
        $this->assertFalse($unsavedTemplate->radioValue('value_on_unsaved_template', 'can_be_anything'));
        $this->assertTrue($unsavedTemplate->radioValue('value_on_unsaved_template', 'can_be_anything', true));

        // selectValue() should be null by default
        $this->assertNull($unsavedTemplate->selectValue('value_on_unsaved_template'));
        $this->assertNull($unsavedTemplate->selectValue('value_on_unsaved_template'), Location::class);

        // selectValues() should be an empty array by default
        $this->assertIsArray($unsavedTemplate->selectValues('value_on_unsaved_template'));
        $this->assertEmpty($unsavedTemplate->selectValues('value_on_unsaved_template'));
        $this->assertEmpty($unsavedTemplate->selectValues('value_on_unsaved_template'), Location::class);

        // textValue() should be an empty string by default
        $this->assertEquals('', $unsavedTemplate->selectValue('value_on_unsaved_template'));
    }

    public function testParsingCheckmarkValue()
    {
        $template = ReportTemplate::factory()->create([
            'options' => [
                'is_a_checkbox_field' => '1',
                // This shouldn't happen since unchecked inputs are
                // not submitted, but we should handle it anyway
                'is_checkbox_field_with_zero' => '0',
            ],
        ]);

        $this->assertEquals('1', $template->checkmarkValue('is_a_checkbox_field'));
        $this->assertEquals('0', $template->checkmarkValue('non_existent_key'));
        $this->assertEquals('0', $template->checkmarkValue('is_checkbox_field_with_zero'));
        $this->assertEquals('0', (new ReportTemplate)->checkmarkValue('non_existent_key_that_is_overwritten_to_default_to_zero', '0'));
    }

    public function testParsingTextValue()
    {
        $template = ReportTemplate::factory()->create([
            'options' => [
                'is_a_text_field' => 'some text',
            ],
        ]);

        $this->assertEquals('some text', $template->textValue('is_a_text_field'));
        $this->assertEquals('', $template->textValue('non_existent_key'));

        $this->assertEquals('', (new ReportTemplate)->textValue('is_a_text_field'));
        $this->assertEquals('my fallback', (new ReportTemplate)->textValue('non_existent_key', 'my fallback'));
    }

    public function testParsingRadioValue()
    {
        $template = ReportTemplate::factory()->create([
            'options' => ['property_that_exists' => '1'],
        ]);

        $this->assertTrue($template->radioValue('property_that_exists', '1'));

        // check non-existent key returns false
        $this->assertFalse($template->radioValue('non_existent_property', 'doesnt_matter'));

        // check can return fallback value
        $this->assertTrue($template->radioValue('non_existent_property', 'doesnt_matter', true));
    }

    public function testParsingSelectValue()
    {
        $template = ReportTemplate::factory()->create([
            'options' => [
                'is_a_text_field_as_well' => '4',
                'contains_a_null_value' => null,
            ],
        ]);

        $this->assertEquals('4', $template->selectValue('is_a_text_field_as_well'));
        $this->assertEquals('', $template->selectValue('non_existent_key'));
        $this->assertNull($template->selectValue('contains_a_null_value'));
    }

    public function testParsingSelectValues()
    {
        $template = ReportTemplate::factory()->create([
            'options' => [
                'an_array' => ['2', '3', '4'],
                'an_empty_array' => [],
                'an_array_containing_null' => [null],
            ],
        ]);

        $this->assertEquals(['2', '3', '4'], $template->selectValues('an_array'));
        $this->assertEquals([], $template->selectValues('an_empty_array'));
        $this->assertEquals([null], $template->selectValues('an_array_containing_null'));
        $this->assertEquals([], $template->selectValues('non_existent_key'));
    }

    public function testSelectValueDoesNotIncludeDeletedOrNonExistentModels()
    {
        [$locationA, $locationB] = Location::factory()->count(2)->create();
        $invalidId = 10000;

        $templateWithValidId = ReportTemplate::factory()->create([
            'options' => ['single_value' => $locationA->id],
        ]);

        $templateWithDeletedId = ReportTemplate::factory()->create([
            'options' => ['single_value' => $locationB->id],
        ]);
        $locationB->delete();

        $templateWithInvalidId = ReportTemplate::factory()->create([
            'options' => ['single_value' => $invalidId],
        ]);

        $this->assertEquals(
            $locationA->id,
            $templateWithValidId->selectValue('single_value', Location::class)
        );

        $this->assertNull($templateWithDeletedId->selectValue('single_value', Location::class));
        $this->assertNull($templateWithInvalidId->selectValue('single_value', Location::class));
        $this->assertNull((new ReportTemplate)->selectValue('value_on_unsaved_template', Location::class));
    }

    public function testSelectValuesDoNotIncludeDeletedOrNonExistentModels()
    {
        [$locationA, $locationB] = Location::factory()->count(2)->create();
        $invalidId = 10000;

        $template = ReportTemplate::factory()->create([
            'options' => [
                'array_of_ids' => [
                    $locationA->id,
                    $locationB->id,
                    $invalidId,
                ],
            ],
        ]);

        $locationB->delete();

        $parsedValues = $template->selectValues('array_of_ids', Location::class);

        $this->assertContains($locationA->id, $parsedValues);
        $this->assertNotContains($locationB->id, $parsedValues);
        $this->assertNotContains($invalidId, $parsedValues);
    }

    public function testGracefullyHandlesSingleSelectBecomingMultiSelect()
    {
        $department = Department::factory()->create();

        $templateWithValue = ReportTemplate::factory()->create([
            'options' => ['single_value' => 'a string'],
        ]);

        $templateWithModelId = ReportTemplate::factory()->create([
            'options' => ['by_dept_id' => $department->id],
        ]);

        // If nothing is selected for a single select then it is stored
        // as null and should be returned as an empty array.
        $templateWithNull = ReportTemplate::factory()->create([
            'options' => ['by_dept_id' => null],
        ]);

        $this->assertEquals(['a string'], $templateWithValue->selectValues('single_value'));
        $this->assertContains($department->id, $templateWithModelId->selectValues('by_dept_id', Department::class));
        $this->assertEquals([], $templateWithNull->selectValues('by_dept_id'));
    }

    public function testGracefullyHandlesMultiSelectBecomingSingleSelectBySelectingTheFirstValue()
    {
        [$departmentA, $departmentB] = Department::factory()->count(2)->create();

        // Given report templates saved with a property that is an array of values
        $templateWithValuesInArray = ReportTemplate::factory()->create([
            'options' => ['array_of_values' => [3, 'a string']],
        ]);

        $templateWithModelIdsInArray = ReportTemplate::factory()->create([
            'options' => ['array_of_model_ids' => [$departmentA->id, $departmentB->id]],
        ]);

        $this->assertEquals(3, $templateWithValuesInArray->selectValue('array_of_values'));
        $this->assertEquals(
            $departmentA->id,
            $templateWithModelIdsInArray->selectValue('array_of_model_ids', Department::class)
        );
    }
}
