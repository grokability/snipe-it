<?php

namespace Tests\Feature\CustomFields;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomFieldValuesTest extends TestCase
{
    /** @return array<string, array{bool, mixed, bool, bool, mixed, mixed, bool}> */
    public static function fillCases(): array
    {
        $cases = [
            'omitted fields are preserved' => [false, null, false, true, 'original', 'original'],
            'explicit null clears a field' => [true, null, false, true, null, 'original'],
            'missing fields can be cleared' => [false, null, false, false, null, null],
            'omitted fields receive defaults' => [false, null, true, true, 'default', 'default'],
            'null receives a default' => [true, null, true, true, 'default', 'default'],
            'empty string overrides a default' => [true, '', true, true, '', 'default'],
            'zero overrides a default' => [true, '0', true, true, '0', 'default'],
            'false overrides a default' => [true, false, true, true, false, 'default'],
        ];

        $inputs = [];
        foreach ($cases as $name => $case) {
            $inputs[$name.' (array)'] = [...$case, false];
            $inputs[$name.' (request)'] = [...$case, true];
        }

        return $inputs;
    }

    #[DataProvider('fillCases')]
    public function test_filling_preserves_missing_null_and_default_semantics(bool $present, mixed $value, bool $defaults, bool $onlyPresent, mixed $expectedFirst, mixed $expectedSecond, bool $asRequest): void
    {
        [$first, $second] = CustomField::factory()->count(2)->create();
        $model = AssetModel::factory()->hasMultipleCustomFields([$first, $second])->create();
        $model->defaultValues()->attach([$first->id => ['default_value' => 'default'], $second->id => ['default_value' => 'default']]);
        $asset = new Asset(['model_id' => $model->id]);
        $asset->{$first->db_column} = 'original';
        $asset->{$second->db_column} = 'original';
        $input = $present ? [$first->db_column => $value] : [];

        $data = $asRequest ? new Request($input) : $input;
        $result = $asset->custom_fill($data, with_defaults: $defaults, only_present: $onlyPresent);

        $this->assertSame($expectedFirst, $asset->{$first->db_column});
        $this->assertSame($expectedSecond, $asset->{$second->db_column});
        if (! $present && ! $defaults && $onlyPresent) {
            $this->assertSame([], $result);
        } else {
            $this->assertSame($expectedFirst, $result[$first->db_column]);
        }
    }

    public function test_requests_and_arrays_apply_the_same_field_filters_and_casts(): void
    {
        $field = CustomField::factory()->create(['display_audit' => true]);
        $model = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $fromArray = new Asset(['model_id' => $model->id]);
        $fromRequest = new Asset(['model_id' => $model->id]);
        $input = [$field->db_column => ['one', 'two'], 'name' => 'not a custom field'];

        $this->assertSame(
            $fromArray->custom_fill($input, filter_field_expression: 'display_audit'),
            $fromRequest->custom_fill(new Request($input), filter_field_expression: 'display_audit'),
        );
        $this->assertSame('one, two', $fromArray->{$field->db_column});
        $this->assertSame($fromArray->{$field->db_column}, $fromRequest->{$field->db_column});
        $this->assertNull($fromArray->name);
    }

}
