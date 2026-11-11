<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\CustomFieldsTransformer;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression for #19965. The /fields API's field_values_array split on
 * CRLF only, so options saved by a browser that sends LF or CR line
 * endings (macOS, Linux, some Chrome configurations) came back as a
 * single bunched-together string with literal newline characters.
 * Mirrors #19429's fix for the Livewire default-values wizard, which
 * handled the same bug class in a different code path.
 */
class CustomFieldsTransformerLineEndingsTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function lineEndingProvider(): array
    {
        return [
            'LF' => ["Red\nGreen\nBlue"],
            'CRLF' => ["Red\r\nGreen\r\nBlue"],
            'CR' => ["Red\rGreen\rBlue"],
        ];
    }

    #[DataProvider('lineEndingProvider')]
    public function test_transform_custom_field_splits_values_regardless_of_line_endings(string $fieldValues): void
    {
        $field = CustomField::factory()->create([
            'element' => 'listbox',
            'field_values' => $fieldValues,
        ]);

        $result = (new CustomFieldsTransformer)->transformCustomField($field);

        $this->assertSame(['Red', 'Green', 'Blue'], $result['field_values_array']);
    }

    #[DataProvider('lineEndingProvider')]
    public function test_transform_custom_field_with_default_value_splits_values_regardless_of_line_endings(string $fieldValues): void
    {
        $field = CustomField::factory()->create([
            'element' => 'listbox',
            'field_values' => $fieldValues,
        ]);

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

        $result = (new CustomFieldsTransformer)->transformCustomField($field, $model);

        $this->assertSame(['Red', 'Green', 'Blue'], $result['field_values_array']);
    }

    public function test_transform_custom_field_returns_null_values_array_when_field_values_is_empty(): void
    {
        $field = CustomField::factory()->create([
            'element' => 'text',
            'field_values' => null,
        ]);

        $result = (new CustomFieldsTransformer)->transformCustomField($field);

        $this->assertNull($result['field_values_array']);
    }

    public function test_field_metadata_is_preserved_when_an_owner_adds_a_default_value(): void
    {
        $field = CustomField::factory()->create(['name' => 'Support <email>', 'field_values' => null]);
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $owner->defaultValues()->attach($field, ['default_value' => 'reply@example.org']);
        $transformer = new CustomFieldsTransformer;

        $withoutDefault = $transformer->transformCustomField($field);
        $withDefault = $transformer->transformCustomField($field, $owner);

        $this->assertSame('Support &lt;email&gt;', $withoutDefault['name']);
        $this->assertArrayNotHasKey('default_value', $withoutDefault);
        $this->assertSame($withoutDefault + ['default_value' => 'reply@example.org'], $withDefault);
    }

    public function test_lists_with_and_without_defaults_return_sequential_rows_for_keyed_collections(): void
    {
        $this->app->instance('api_current_page', 1);
        $this->app->instance('api_limit_value', 50);
        $fields = CustomField::factory()->count(2)->create()->keyBy('id');
        $owner = AssetModel::factory()->hasMultipleCustomFields($fields->all())->create();
        $transformer = new CustomFieldsTransformer;

        $withoutDefaults = $transformer->transformCustomFields($fields, 2);
        $withDefaults = $transformer->transformCustomFields($fields, 2, $owner);

        $this->assertSame([0, 1], array_keys($withoutDefaults['rows']));
        $this->assertSame([0, 1], array_keys($withDefaults['rows']));
        $this->assertArrayNotHasKey('default_value', $withoutDefaults['rows'][0]);
        $this->assertArrayHasKey('available_actions', $withDefaults['rows'][0]);
        $this->assertArrayHasKey('default_value', $withDefaults['rows'][0]);
        $this->assertNull($withDefaults['rows'][0]['default_value']);
    }
}
