<?php

namespace Tests\Feature\CustomFields;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldDefault;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EncryptedCustomFieldValidationTest extends TestCase
{
    /** @return array<string, array{string, mixed, mixed}> */
    public static function formats(): array
    {
        return [
            'alpha' => ['ALPHA', 'Alphabetic', '123'],
            'alpha dash' => ['ALPHA-DASH', 'asset-tag_1', 'has spaces'],
            'alpha numeric' => ['ALPHA-NUMERIC', 'asset123', 'has spaces'],
            'numeric' => ['NUMERIC', '123.45', 'not numeric'],
            'email' => ['EMAIL', 'person@example.org', 'not an email'],
            'url' => ['URL', 'https://example.org', 'not a url'],
            'ip' => ['IP', '192.0.2.1', 'not an ip'],
            'ipv4' => ['IPV4', '192.0.2.1', '2001:db8::1'],
            'ipv6' => ['IPV6', '2001:db8::1', '192.0.2.1'],
            'mac' => ['MAC', 'aa:bb:cc:dd:ee:ff', 'not a mac'],
            'boolean' => ['BOOLEAN', '1', 'yes'],
            'regex' => ['regex:/^asset-\d{3}$/', 'asset-123', 'other-123'],
            'regex with alternation' => ['regex:/^(foo|bar)$/', 'foo', 'baz'],
        ];
    }

    #[DataProvider('formats')]
    public function test_standard_rules_validate_plaintext_while_storage_stays_encrypted(string $format, mixed $valid, mixed $invalid): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $field = CustomField::factory()->create(['format' => $format, 'field_encrypted' => true]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        $column = $field->db_column;

        $asset->{$column} = $valid;
        $this->assertTrue($asset->save(), (string) $asset->getErrors());
        $ciphertext = $asset->getRawOriginal($column);
        $this->assertNotSame($valid, $ciphertext);
        $this->assertSame($valid, Crypt::decrypt($ciphertext));

        $asset->{$column} = $invalid;
        $this->assertFalse($asset->save());
        $this->assertArrayHasKey($column, $asset->getErrors()->toArray());
        $this->assertSame($ciphertext, $asset->fresh()->getRawOriginal($column));
    }

    public function test_validation_attributes_are_decrypted_once_by_the_cast(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create([$field->db_column => 'secret']);
        $ciphertext = $asset->getRawOriginal($field->db_column);
        $encrypter = Crypt::getFacadeRoot();
        Crypt::shouldReceive('decrypt')->once()->with($ciphertext)
            ->andReturnUsing(fn (string $value): mixed => $encrypter->decrypt($value));

        $this->assertSame('secret', $asset->getModelAttributes()[$field->db_column]);
    }

    /** @return array<string, array{bool, ?string}> */
    public static function fieldLabels(): array
    {
        return [
            'encrypted field name' => [true, null],
            'unencrypted field name' => [false, null],
            'encrypted explicit label' => [true, 'Contact email'],
            'unencrypted explicit label' => [false, 'Contact email'],
        ];
    }

    #[DataProvider('fieldLabels')]
    public function test_all_custom_fields_use_readable_validation_labels_and_preserve_explicit_overrides(bool $encrypted, ?string $override): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $field = CustomField::factory()->create(['name' => 'Support Email 2', 'format' => 'EMAIL', 'field_encrypted' => $encrypted]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        if ($override !== null) {
            $asset->setValidationAttributeNames([$field->db_column => $override]);
        }
        $asset->{$field->db_column} = 'invalid';

        $this->assertFalse($asset->save());
        $this->assertSame(
            [trans('validation.email', ['attribute' => $override ?? $field->name])],
            $asset->getErrors()->get($field->db_column),
        );
    }

    public function test_validation_can_check_existing_encrypted_values_without_read_permission(): void
    {
        $field = CustomField::factory()->create(['format' => 'EMAIL', 'field_encrypted' => true]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create([$field->db_column => 'secret@example.org']);
        $ciphertext = $asset->getRawOriginal($field->db_column);
        $this->actingAs(User::factory()->editAssets()->create());

        $this->assertSame('secret@example.org', $asset->{$field->db_column});
        $asset->name = 'Updated name';
        $this->assertTrue($asset->save(), (string) $asset->getErrors());
        $this->assertSame($ciphertext, $asset->fresh()->getRawOriginal($field->db_column));
        $this->assertSame('secret@example.org', $asset->{$field->db_column});
    }

    public function test_unreadable_ciphertext_fails_validation_even_without_a_format_rule_and_can_be_repaired(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        DB::table('assets')->where('id', $asset->id)->update([$field->db_column => 'unreadable ciphertext']);
        $asset->refresh();
        $asset->name = 'Must not be saved';

        $this->assertStringStartsWith('Error Decrypting:', $asset->{$field->db_column});
        $this->assertFalse($asset->save());
        $this->assertContains(trans('general.something_went_wrong'), $asset->getErrors()->get($field->db_column));
        $this->assertNotSame('Must not be saved', $asset->fresh()->name);
        $this->assertSame('unreadable ciphertext', $asset->fresh()->getRawOriginal($field->db_column));

        $asset->{$field->db_column} = 'repaired';
        $this->assertTrue($asset->save(), (string) $asset->getErrors());
        $this->assertSame('repaired', Crypt::decrypt($asset->getRawOriginal($field->db_column)));
    }

    public function test_integrity_rules_check_current_ciphertext_when_reused(): void
    {
        $field = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $column = $field->db_column;
        $rules = [$column => $asset->getRules()[$column]];
        $asset->setRawAttributes(array_replace($asset->getAttributes(), [$column => 'unreadable ciphertext']));

        $validator = Validator::make($asset->getModelAttributes(), $rules);
        $this->assertFalse($validator->passes());
        $this->assertContains(trans('general.something_went_wrong'), $validator->errors()->get($column));

        $asset->{$column} = 'repaired';
        $this->assertTrue(Validator::make($asset->getModelAttributes(), $rules)->passes());
    }

    public function test_fieldset_rules_take_precedence_over_model_rules_for_custom_fields(): void
    {
        $field = CustomField::factory()->create(['format' => 'EMAIL', 'field_encrypted' => true]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        $column = $field->db_column;
        $asset->setRules(['serial' => ['required'], $column => 'nullable']);
        $this->assertSame(['required'], $asset->getRules()['serial']);
        $asset->{$column} = 'not an email';

        $this->assertFalse($asset->save());
        $this->assertSame(
            [trans('validation.email', ['attribute' => $field->name])],
            $asset->getErrors()->get($column),
        );

        $asset->{$column} = 'valid@example.org';
        $this->assertTrue($asset->save(), (string) $asset->getErrors());

        $asset->setRawAttributes(array_replace($asset->getAttributes(), [$column => 'unreadable ciphertext']));
        $this->assertFalse($asset->save());
        $this->assertContains(trans('general.something_went_wrong'), $asset->getErrors()->get($column));
    }

    public function test_encrypted_field_defaults_are_validated_as_plaintext_and_encrypted_when_applied(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $field = CustomField::factory()->create(['format' => 'EMAIL', 'field_encrypted' => true]);
        $model = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $fieldset = CustomFieldset::findOrFail($model->fieldset_id);
        CustomFieldDefault::saveFor($model, $fieldset, [$field->id => 'default@example.org']);

        try {
            CustomFieldDefault::saveFor($model, $fieldset, [$field->id => 'invalid']);
            $this->fail('Invalid defaults should fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field->db_column, $exception->errors());
        }
        $this->assertSame([$field->db_column => 'default@example.org'], CustomFieldDefault::valuesFor($model));
        $asset = Asset::factory()->create(['model_id' => $model->id]);
        $asset->custom_fill([], with_defaults: true);
        $this->assertTrue($asset->save(), (string) $asset->getErrors());
        $this->assertSame('default@example.org', Crypt::decrypt($asset->getRawOriginal($field->db_column)));
    }

    /** @return array<string, array{mixed}> */
    public static function nullableBooleans(): array
    {
        return ['null' => [null], 'empty' => [''], 'false' => [false], 'zero' => ['0']];
    }

    #[DataProvider('nullableBooleans')]
    public function test_nullable_and_falsey_encrypted_values_keep_their_existing_behavior(mixed $value): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $field = CustomField::factory()->create(['format' => 'BOOLEAN', 'field_encrypted' => true]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        $asset->{$field->db_column} = $value;

        $this->assertTrue($asset->save(), (string) $asset->getErrors());
        $this->assertSame($value, $asset->fresh()->{$field->db_column});
    }
}
