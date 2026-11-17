<?php

namespace Tests\Feature\CustomFields;

use App\Http\Transformers\ComponentsAssetsTransformer;
use App\Http\Transformers\CustomFieldValuesTransformer;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Contracts\HasCustomFields;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\SnipeModel;
use App\Models\Traits\HasAndValidatesCustomFields;
use App\Models\Traits\Loggable;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
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

    public function test_transformer_filters_custom_fields_by_display_flags(): void
    {
        $encrypted = CustomField::factory()->encrypt()->create(['display_audit' => true]);
        $hidden = CustomField::factory()->create(['display_audit' => false]);
        $asset = Asset::factory()->hasMultipleCustomFields([$encrypted, $hidden])->create();
        $this->actingAs(User::factory()->superuser()->create());
        $asset->custom_fill([$encrypted->db_column => 'secret', $hidden->db_column => 'hidden']);
        $this->assertTrue($asset->save());

        $fields = (new CustomFieldValuesTransformer)->transform($asset, filterFieldExpression: 'display_audit');
        $this->assertCount(1, $fields);
        $this->assertSame($encrypted->db_column, $fields[$encrypted->name]['field']);
        $this->assertSame('secret', $fields[$encrypted->name]['value']);
        $this->assertSame([], $asset->custom_fill([$hidden->db_column => 'replacement'], filter_field_expression: 'display_audit'));
        $this->assertSame('hidden', $asset->{$hidden->db_column});
    }

    public function test_extraction_and_filling_are_empty_without_a_fieldset(): void
    {
        $model = AssetModel::factory()->create(['fieldset_id' => null]);
        $asset = new Asset(['model_id' => $model->id]);

        $this->assertSame([], $asset->custom_fill(['name' => 'ignored']));
        $this->assertSame([], (new CustomFieldValuesTransformer)->transform($asset));
        $this->assertSame([], (new CustomFieldValuesTransformer)->transform($asset, valuesOnly: true));
    }

    public function test_shared_transformer_preserves_escaping_markdown_dates_and_encrypted_visibility(): void
    {
        $text = CustomField::factory()->create(['show_in_requestable_list' => true]);
        $markdown = CustomField::factory()->create(['element' => 'markdown-textarea', 'show_in_requestable_list' => true]);
        $date = CustomField::factory()->create(['format' => 'DATE', 'element' => 'date_picker', 'show_in_requestable_list' => true]);
        $encrypted = CustomField::factory()->encrypt()->create(['show_in_requestable_list' => true]);
        $hidden = CustomField::factory()->create(['show_in_requestable_list' => false]);
        $asset = Asset::factory()->hasMultipleCustomFields([$text, $markdown, $date, $encrypted, $hidden])->create();
        $this->actingAs(User::factory()->superuser()->create());
        $asset->custom_fill([$text->db_column => '<b>plain</b>', $markdown->db_column => '**bold**', $date->db_column => '2025-02-03', $encrypted->db_column => 'secret']);
        $transformer = new CustomFieldValuesTransformer;

        $result = $transformer->transform($asset);
        $this->assertSame('&lt;b&gt;plain&lt;/b&gt;', $result[$text->name]['value']);
        $this->assertStringContainsString('<strong>bold</strong>', $result[$markdown->name]['value']);
        $this->assertStringContainsString('2025', $result[$date->name]['value']);
        $this->assertSame('secret', $result[$encrypted->name]['value']);
        $plain = $transformer->transform($asset, includeEncrypted: false, renderMarkdown: false);
        $this->assertSame('**bold**', $plain[$markdown->name]['value']);
        $this->assertArrayNotHasKey($encrypted->name, $plain);
        $requestable = $transformer->transform(
            $asset,
            includeEncrypted: false,
            filterFieldExpression: 'show_in_requestable_list',
            valuesOnly: true,
        );
        $this->assertArrayNotHasKey($encrypted->db_column, $requestable);
        $this->assertArrayNotHasKey($hidden->db_column, $requestable);
        $this->assertSame($result[$text->name]['value'], $requestable[$text->db_column]);
        $this->assertSame($result[$markdown->name]['value'], $requestable[$markdown->db_column]);
        $this->assertSame($result[$date->name]['value'], $requestable[$date->db_column]);
        $values = $transformer->transform($asset, valuesOnly: true);
        $this->assertArrayHasKey($hidden->db_column, $values);
        $this->assertSame('secret', $values[$encrypted->db_column]);
        $plainValues = $transformer->transform($asset, renderMarkdown: false, valuesOnly: true);
        $this->assertSame('**bold**', $plainValues[$markdown->db_column]);

        $this->actingAs(User::factory()->viewAssets()->create());
        $masked = $transformer->transform($asset);
        $this->assertSame(strtoupper(trans('admin/custom_fields/general.encrypted')), $masked[$encrypted->name]['value']);
        $this->assertSame($result[$text->name]['value'], $masked[$text->name]['value']);
        $maskedValues = $transformer->transform($asset, valuesOnly: true);
        $this->assertSame($masked[$encrypted->name]['value'], $maskedValues[$encrypted->db_column]);
    }

    public function test_transformer_returns_no_fields_for_models_without_custom_field_support(): void
    {
        $field = CustomField::factory()->create(['show_in_requestable_list' => true]);
        $owner = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $transformer = new CustomFieldValuesTransformer;

        foreach ([new User, $owner] as $item) {
            $this->assertSame([], $transformer->transform($item));
            $this->assertSame([], $transformer->transform($item, valuesOnly: true));
        }
    }

    public function test_component_asset_rows_keep_flat_custom_fields_and_mask_encrypted_values(): void
    {
        $field = CustomField::factory()->create();
        $encrypted = CustomField::factory()->encrypt()->create();
        $asset = Asset::factory()->hasMultipleCustomFields([$field, $encrypted])->create();
        $this->actingAs(User::factory()->superuser()->create());
        $asset->custom_fill([$field->db_column => '<b>plain</b>', $encrypted->db_column => 'secret']);
        $transformer = new ComponentsAssetsTransformer;

        $visible = $transformer->transformAsset($asset);
        $this->assertSame('&lt;b&gt;plain&lt;/b&gt;', $visible[$field->name]);
        $this->assertSame('secret', $visible[$encrypted->name]);

        $this->actingAs(User::factory()->viewAssets()->create());
        $masked = $transformer->transformAsset($asset);
        $this->assertSame(strtoupper(trans('admin/custom_fields/general.encrypted')), $masked[$encrypted->name]);
    }

    public function test_audit_logging_extracts_raw_values_for_an_implementer_that_is_not_an_asset(): void
    {
        Notification::fake();
        $field = CustomField::factory()->encrypt()->create(['display_audit' => true]);
        $hidden = CustomField::factory()->create(['display_audit' => false]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field, $hidden])->create();
        $this->actingAs(User::factory()->superuser()->create());
        $asset->custom_fill([$field->db_column => 'secret', $hidden->db_column => 'excluded from audit']);
        $this->assertTrue($asset->save());
        $item = new GenericCustomFieldItem;
        $item->setRawAttributes($asset->getAttributes(), true);
        $item->exists = true;
        $item->setRelation('custom_fieldset', $asset->custom_fieldset);

        $log = $item->logAudit('generic custom field audit', null);

        $metadata = json_decode($log->log_meta, true);
        $this->assertSame(GenericCustomFieldItem::class, $log->item_type);
        $this->assertSame([$field->db_column => $asset->getRawOriginal($field->db_column)], $metadata['_audit_snapshot']);
        $this->assertSame('secret', Crypt::decrypt($metadata['_audit_snapshot'][$field->db_column]));
    }

    public function test_checkin_and_checkout_logs_extract_raw_changes_for_an_implementer_that_is_not_an_asset(): void
    {
        $field = CustomField::factory()->encrypt()->create(['display_checkin' => true, 'display_checkout' => true]);
        $asset = Asset::factory()->hasMultipleCustomFields([$field])->create();
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);
        $item = GenericCustomFieldItem::findOrFail($asset->id);
        $item->custom_fill([$field->db_column => 'before']);
        $this->assertTrue($item->save());
        $original = $item->getAttributes();
        $item->custom_fill([$field->db_column => 'after']);
        $this->assertTrue($item->save());

        $checkout = $item->logCheckout(null, $user, originalValues: $original);
        $checkin = $item->logCheckin($user, null, originalValues: $original);

        foreach ([$checkout, $checkin] as $log) {
            $metadata = json_decode($log->log_meta, true);
            $this->assertSame('before', Crypt::decrypt($metadata[$field->db_column]['old']));
            $this->assertSame('after', Crypt::decrypt($metadata[$field->db_column]['new']));
        }
    }
}

/** Exercise the traits on an independent model using an existing table without schema changes. */
class GenericCustomFieldItem extends SnipeModel implements HasCustomFields
{
    use HasAndValidatesCustomFields;
    use Loggable;

    protected $table = 'assets';

    protected $rules = [];

    public function custom_fieldset(): Relation
    {
        return $this->hasOneThrough(CustomFieldset::class, AssetModel::class, 'id', 'id', 'model_id', 'fieldset_id');
    }

    protected static function ensureCustomFieldRegistration(string $modelClass): void {}
}
