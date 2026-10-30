<?php

namespace Tests\Feature\CustomFields;

use App\Models\CustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Server-side validation of the format/element/encryption/field_values
 * matrix on CustomField. These rules live on the model (getRules() +
 * static helpers) so every entry point — UI, API, factories, seeders,
 * tinker — sees the same enforcement.
 */
class CustomFieldValidationTest extends TestCase
{
    use WithFaker;

    protected function newField(array $attrs = []): CustomField
    {
        $field = new CustomField(array_merge([
            'name' => 'validation_test_'.$this->faker->uuid(),
            'element' => 'text',
        ], $attrs));
        $field->created_by = User::factory()->superuser()->create()->id;

        return $field;
    }

    public static function formatElementMatrix(): array
    {
        return [
            // [format, element, expectedToSave, hint]
            'DATE + date_picker is allowed' => ['DATE', 'date_picker', true],
            'DATE + text is rejected' => ['DATE', 'text', false],
            'DATE + listbox is rejected' => ['DATE', 'listbox', false],
            'DATETIME + datetime_picker is allowed' => ['DATETIME', 'datetime_picker', true],
            'DATETIME + text is rejected' => ['DATETIME', 'text', false],
            'EMAIL + text is allowed' => ['EMAIL', 'text', true],
            'EMAIL + listbox is rejected' => ['EMAIL', 'listbox', false],
            'IP + text is allowed' => ['IP', 'text', true],
            'IP + textarea is rejected' => ['IP', 'textarea', false],
            'BOOLEAN + text is allowed' => ['BOOLEAN', 'text', true],
            'BOOLEAN + checkbox is allowed' => ['BOOLEAN', 'checkbox', true],
            'BOOLEAN + radio is allowed' => ['BOOLEAN', 'radio', true],
            'ANY + text is allowed' => ['ANY', 'text', true],
            'ANY + listbox is allowed' => ['ANY', 'listbox', true],
            'ANY + textarea is allowed' => ['ANY', 'textarea', true],
        ];
    }

    #[DataProvider('formatElementMatrix')]
    public function test_format_element_matrix_is_enforced_on_save(string $format, string $element, bool $expectedToSave): void
    {
        $field = $this->newField(['element' => $element]);
        $field->format = $format;

        // checkbox/radio need field_values to pass the field_values requirement,
        // so give them one when we expect the save to succeed.
        if (in_array($element, ['checkbox', 'radio', 'listbox'], true)) {
            $field->field_values = "one\ntwo";
        }

        $saved = $field->save();

        if ($expectedToSave) {
            $this->assertTrue($saved, 'Expected save to succeed. Errors: '.json_encode($field->getErrors()->toArray()));
            $this->assertDatabaseHas('custom_fields', ['id' => $field->id]);
        } else {
            $this->assertFalse($saved, 'Expected save to fail due to incompatible element/format combo.');
            $this->assertArrayHasKey('element', $field->getErrors()->toArray());
            $this->assertDatabaseMissing('custom_fields', ['name' => $field->name]);
        }
    }

    /**
     * Regression for issue 19498. The BOOLEAN format's allowed-element list
     * dropped `radio` when the format/element matrix was introduced, which
     * broke the pre-existing "yes/no radio group" pattern for boolean custom
     * fields. Radio has always been consistent with checkbox everywhere else
     * in the code (encryption gate, field_values requirement, editor's
     * property-change handler), so this row belongs in the allowed set too.
     */
    public function test_boolean_format_allows_radio_element(): void
    {
        $field = $this->newField([
            'element' => 'radio',
            'field_values' => "Yes\nNo",
        ]);
        $field->format = 'BOOLEAN';

        $this->assertTrue($field->save(), 'Errors: '.json_encode($field->getErrors()->toArray()));
        // format persists as the lowercase validation-rule string ('boolean');
        // 'BOOLEAN' is the reverse-mapped UI label.
        $this->assertDatabaseHas('custom_fields', [
            'id' => $field->id,
            'element' => 'radio',
            'format' => 'boolean',
        ]);
    }

    public function test_date_format_rejects_encryption(): void
    {
        $field = $this->newField(['element' => 'date_picker', 'field_encrypted' => 1]);
        $field->format = 'DATE';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_encrypted', $field->getErrors()->toArray());
    }

    public function test_datetime_format_rejects_encryption(): void
    {
        $field = $this->newField(['element' => 'datetime_picker', 'field_encrypted' => 1]);
        $field->format = 'DATETIME';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_encrypted', $field->getErrors()->toArray());
    }

    public function test_checkbox_element_rejects_encryption(): void
    {
        $field = $this->newField([
            'element' => 'checkbox',
            'field_encrypted' => 1,
            'field_values' => "one\ntwo",
        ]);
        $field->format = 'ANY';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_encrypted', $field->getErrors()->toArray());
    }

    public function test_radio_element_rejects_encryption(): void
    {
        $field = $this->newField([
            'element' => 'radio',
            'field_encrypted' => 1,
            'field_values' => "one\ntwo",
        ]);
        $field->format = 'ANY';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_encrypted', $field->getErrors()->toArray());
    }

    public function test_listbox_element_requires_field_values(): void
    {
        $field = $this->newField(['element' => 'listbox']);
        $field->format = 'ANY';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_values', $field->getErrors()->toArray());
    }

    public function test_checkbox_element_requires_field_values(): void
    {
        $field = $this->newField(['element' => 'checkbox']);
        $field->format = 'ANY';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_values', $field->getErrors()->toArray());
    }

    public function test_radio_element_requires_field_values(): void
    {
        $field = $this->newField(['element' => 'radio']);
        $field->format = 'ANY';

        $this->assertFalse($field->save());
        $this->assertArrayHasKey('field_values', $field->getErrors()->toArray());
    }

    public function test_text_element_does_not_require_field_values(): void
    {
        $field = $this->newField(['element' => 'text']);
        $field->format = 'ANY';

        $this->assertTrue($field->save(), 'Errors: '.json_encode($field->getErrors()->toArray()));
    }

    /**
     * Regression test for the wasRecentlyCreated bypass in the `updating` boot
     * hook. The `created` hook does a second save on the same instance to
     * persist db_column. Without the bypass, that save gets rejected by the
     * format-lock check on DATE/DATETIME fields, leaving db_column NULL.
     */
    public function test_date_field_creation_persists_db_column(): void
    {
        $field = $this->newField(['element' => 'date_picker']);
        $field->format = 'DATE';

        $this->assertTrue($field->save(), 'Errors: '.json_encode($field->getErrors()->toArray()));

        $fresh = $field->fresh();
        $this->assertNotNull($fresh->db_column, 'db_column must be populated after DATE field creation');
        $this->assertStringStartsWith('_snipeit_', $fresh->db_column);
    }

    public function test_datetime_field_creation_persists_db_column(): void
    {
        $field = $this->newField(['element' => 'datetime_picker']);
        $field->format = 'DATETIME';

        $this->assertTrue($field->save(), 'Errors: '.json_encode($field->getErrors()->toArray()));

        $fresh = $field->fresh();
        $this->assertNotNull($fresh->db_column);
        $this->assertStringStartsWith('_snipeit_', $fresh->db_column);
    }

    /**
     * A previous bug could leave db_column NULL on a custom_fields row. The
     * deleting hook tried to `ALTER TABLE assets DROP `` ` which errors out
     * and blocks the delete. Verify the guard lets orphan rows be cleaned up.
     */
    public function test_can_delete_orphan_field_with_empty_db_column(): void
    {
        $field = CustomField::factory()->create();
        // Simulate the orphan state without going through the model
        CustomField::where('id', $field->id)->update(['db_column' => null]);

        $field = $field->fresh();
        $this->assertNull($field->db_column);

        $this->assertTrue($field->delete());
        $this->assertDatabaseMissing('custom_fields', ['id' => $field->id]);
    }

    /**
     * Static-helper coverage. These methods are the single source of truth
     * for both getRules() and the Livewire editor UI; if they drift the
     * matrix above should catch it, but a direct test pins them down.
     */
    public function test_allowed_element_keys_for_format(): void
    {
        $this->assertSame(['date_picker'], CustomField::allowedElementKeysForFormat('DATE'));
        $this->assertSame(['datetime_picker'], CustomField::allowedElementKeysForFormat('DATETIME'));
        $this->assertSame(['text'], CustomField::allowedElementKeysForFormat('EMAIL'));
        $this->assertSame(['text'], CustomField::allowedElementKeysForFormat('CUSTOM REGEX'));
        $this->assertSame(['text'], CustomField::allowedElementKeysForFormat('regex:/^\d+$/'));
        $this->assertSame(['text', 'checkbox', 'radio'], CustomField::allowedElementKeysForFormat('BOOLEAN'));
        $this->assertSame(CustomField::ELEMENT_KEYS, CustomField::allowedElementKeysForFormat('ANY'));
        $this->assertSame(CustomField::ELEMENT_KEYS, CustomField::allowedElementKeysForFormat(null));
    }

    public function test_can_encrypt_for(): void
    {
        $this->assertFalse(CustomField::canEncryptFor('date_picker', 'DATE'));
        $this->assertFalse(CustomField::canEncryptFor('datetime_picker', 'DATETIME'));
        $this->assertFalse(CustomField::canEncryptFor('checkbox', 'ANY'));
        $this->assertFalse(CustomField::canEncryptFor('radio', 'ANY'));
        $this->assertTrue(CustomField::canEncryptFor('text', 'ANY'));
        $this->assertTrue(CustomField::canEncryptFor('text', 'EMAIL'));
        $this->assertTrue(CustomField::canEncryptFor('textarea', 'ANY'));
    }

    public function test_element_requires_field_values(): void
    {
        $this->assertTrue(CustomField::elementRequiresFieldValues('listbox'));
        $this->assertTrue(CustomField::elementRequiresFieldValues('checkbox'));
        $this->assertTrue(CustomField::elementRequiresFieldValues('radio'));
        $this->assertFalse(CustomField::elementRequiresFieldValues('text'));
        $this->assertFalse(CustomField::elementRequiresFieldValues('textarea'));
        $this->assertFalse(CustomField::elementRequiresFieldValues('date_picker'));
        $this->assertFalse(CustomField::elementRequiresFieldValues(null));
    }

    public function test_icon_for_format(): void
    {
        $this->assertSame('email', CustomField::iconForFormat('EMAIL'));
        $this->assertSame('link', CustomField::iconForFormat('URL'));
        $this->assertSame('phone', CustomField::iconForFormat('PHONE'));
        $this->assertSame('fax', CustomField::iconForFormat('FAX'));
        $this->assertNull(CustomField::iconForFormat('NUMERIC'));
        $this->assertNull(CustomField::iconForFormat('ANY'));
        $this->assertNull(CustomField::iconForFormat('DATE'));
        $this->assertNull(CustomField::iconForFormat('CUSTOM REGEX'));
        $this->assertNull(CustomField::iconForFormat(null));
    }

    /**
     * DATE / DATETIME are backed by native date columns on the assets
     * table, so changing to or from them would need an ALTER TABLE and
     * risk unconvertable data. Those changes are hard-blocked.
     * Other format changes (all text-backed) are allowed at the DB level;
     * the UI warns the user about the validation-rule change.
     */
    public function test_cannot_change_format_from_text_to_date(): void
    {
        $field = $this->newField(['element' => 'text']);
        $field->format = 'PHONE';
        $this->assertTrue($field->save());

        // Set element to date_picker too so we don't trip element/format
        // matrix validation first — we want the updating hook's format-lock
        // check to be the specific rejection under test.
        $field->format = 'DATE';
        $field->element = 'date_picker';
        $this->assertFalse($field->save(), 'PHONE → DATE conversion must be blocked');
        $this->assertArrayHasKey('format', $field->getErrors()->toArray());

        // Confirm the persisted format didn't change
        $this->assertSame('PHONE', $field->fresh()->format);
    }

    public function test_cannot_change_format_from_date_to_text(): void
    {
        $field = $this->newField(['element' => 'date_picker']);
        $field->format = 'DATE';
        $this->assertTrue($field->save());

        $field->format = 'EMAIL';
        $field->element = 'text';
        $this->assertFalse($field->save(), 'DATE → EMAIL conversion must be blocked');
        $this->assertArrayHasKey('format', $field->getErrors()->toArray());
        $this->assertSame('DATE', $field->fresh()->format);
    }

    public function test_can_change_format_between_text_backed_types(): void
    {
        $field = $this->newField(['element' => 'text']);
        $field->format = 'PHONE';
        $this->assertTrue($field->save());

        $field->format = 'EMAIL';
        $this->assertTrue(
            $field->save(),
            'PHONE → EMAIL is allowed (text-backed, UI warns about validation risk). Errors: '.json_encode($field->getErrors()->toArray())
        );
        $this->assertSame('EMAIL', $field->fresh()->format);
    }

    public function test_phone_and_fax_formats_reverse_map_correctly(): void
    {
        $field = $this->newField();
        $field->format = 'PHONE';
        $this->assertTrue($field->save(), 'Errors: '.json_encode($field->getErrors()->toArray()));
        $this->assertSame('PHONE', $field->fresh()->format, 'PHONE must reverse-map to its label, not ANY');

        $field2 = $this->newField();
        $field2->format = 'FAX';
        $this->assertTrue($field2->save(), 'Errors: '.json_encode($field2->getErrors()->toArray()));
        $this->assertSame('FAX', $field2->fresh()->format, 'FAX must reverse-map to its label, not ANY');
    }

    public function test_valid_link_template_is_saved(): void
    {
        $field = $this->newField(['link_template' => 'anydesk:{VALUE}']);
        $field->format = 'ANY';

        $this->assertTrue($field->save(), 'Errors: '.json_encode($field->getErrors()->toArray()));
        $this->assertDatabaseHas('custom_fields', ['id' => $field->id, 'link_template' => 'anydesk:{VALUE}']);
    }

    public static function invalidLinkTemplates(): array
    {
        return [
            'missing placeholder' => ['anydesk:'],
            'missing scheme' => ['{VALUE}'],
            'javascript scheme' => ['javascript:alert({VALUE})'],
            'data scheme' => ['data:text/html,{VALUE}'],
        ];
    }

    #[DataProvider('invalidLinkTemplates')]
    public function test_invalid_link_template_is_rejected(string $template): void
    {
        $field = $this->newField(['link_template' => $template]);
        $field->format = 'ANY';

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('link_template', $field->getErrors()->toArray());
    }

    public function test_link_template_is_rejected_for_unsupported_element(): void
    {
        $field = $this->newField(['element' => 'textarea', 'link_template' => 'anydesk:{VALUE}']);
        $field->format = 'ANY';

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('link_template', $field->getErrors()->toArray());
    }
}
