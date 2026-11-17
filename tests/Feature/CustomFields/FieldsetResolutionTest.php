<?php

namespace Tests\Feature\CustomFields;

use App\Http\Transformers\AssetModelsTransformer;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldDefault;
use App\Models\CustomFieldset;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FieldsetResolutionTest extends TestCase
{
    public function test_assets_and_models_resolve_their_custom_fieldset_relationships(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $asset = Asset::factory()->create(['model_id' => $model->id]);

        $this->assertFalse(method_exists(AssetModel::class, 'fieldset'));
        $this->assertSame($fieldset->id, $model->fresh()->fieldset_id);
        $this->assertTrue($model->fresh()->custom_fieldset->is($fieldset));
        $this->assertTrue(AssetModel::with('custom_fieldset')->findOrFail($model->id)->relationLoaded('custom_fieldset'));
        $this->assertTrue($asset->custom_fieldset->is($fieldset));
    }

    public function test_the_shared_form_accepts_a_fieldset_and_defaults_without_an_asset_model(): void
    {
        $field = CustomField::factory()->ram()->create();
        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        $this->view('models.custom_fields_form', [
            'fieldset' => $fieldset,
            'defaultValues' => [$field->db_column_name() => '32'],
            'item' => null,
            'errors' => new \Illuminate\Support\ViewErrorBag,
        ])->assertSee($field->db_column_name(), false)->assertSee('value="32"', false);
    }

    public function test_the_shared_bulk_form_only_needs_fieldsets_and_a_selection_count(): void
    {
        $field = CustomField::factory()->ram()->create();
        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        $this->view('models.custom_fields_form_bulk_edit', [
            'fieldsets' => collect([$fieldset]),
            'selectionCount' => 2,
            'errors' => new \Illuminate\Support\ViewErrorBag,
        ])->assertSee($field->db_column_name(), false)->assertSee('null'.$field->db_column_name(), false);
    }

    public function test_asset_defaults_are_resolved_as_a_column_value_map(): void
    {
        $field = CustomField::factory()->ram()->create();
        $model = AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $model->defaultValues()->attach($field, ['default_value' => '0']);
        $asset = Asset::factory()->create(['model_id' => $model->id]);

        $this->assertSame([$field->db_column_name() => '0'], CustomFieldDefault::valuesFor($asset));
    }

    public function test_model_search_still_matches_fieldset_names_and_structured_filters(): void
    {
        $fieldset = CustomFieldset::factory()->create(['name' => 'UniqueFieldsetSearchMarker']);
        $other = CustomFieldset::factory()->create(['name' => 'OtherFieldsetSearchMarker']);
        $matching = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $different = AssetModel::factory()->create(['fieldset_id' => $other->id]);
        $unassigned = AssetModel::factory()->create(['fieldset_id' => null]);
        $missingFieldset = CustomFieldset::factory()->create();
        $orphaned = AssetModel::factory()->create(['fieldset_id' => $missingFieldset->id]);
        $missingFieldset->delete();
        $ids = [$matching->id, $different->id, $unassigned->id, $orphaned->id];

        $this->assertSame([$matching->id], AssetModel::whereKey($ids)->TextSearch($fieldset->name)->pluck('id')->all());
        $this->assertSame([$matching->id], AssetModel::whereKey($ids)->TextSearch(['fieldset' => 'is:'.$fieldset->name])->pluck('id')->all());
        $this->assertSame([$matching->id], AssetModel::whereKey($ids)->TextSearch(['custom_fieldset' => 'is:'.$fieldset->name])->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$different->id, $unassigned->id, $orphaned->id], AssetModel::whereKey($ids)->TextSearch(['fieldset' => 'not:Unique'])->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$unassigned->id, $orphaned->id], AssetModel::whereKey($ids)->TextSearch(['fieldset' => 'is:null'])->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$matching->id, $different->id], AssetModel::whereKey($ids)->TextSearch(['fieldset' => 'is:not_null'])->pluck('id')->all());
    }

    public function test_model_list_preserves_fieldset_metadata_and_defaults_with_one_fieldset_lookup(): void
    {
        $field = CustomField::factory()->ram()->create();
        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);
        $models = AssetModel::factory()->count(3)->create(['fieldset_id' => $fieldset->id]);
        foreach ($models as $model) {
            $model->defaultValues()->attach($field, ['default_value' => '32']);
        }

        $this->app->instance('api_current_page', 1);
        $this->app->instance('api_limit_value', 50);
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $data = (new AssetModelsTransformer)->transformAssetModels($models, $models->count());
            $queries = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains($query['query'], 'custom_fieldsets'));
            $this->assertCount(1, $queries);
            $this->assertCount(3, $data['rows']);
            foreach ($data['rows'] as $row) {
                $this->assertSame(['id' => $fieldset->id, 'name' => e($fieldset->name)], $row['fieldset']);
                $this->assertSame('32', $row['default_fieldset_values'][0]['default_value']);
            }
        } finally {
            DB::disableQueryLog();
        }
    }
}
