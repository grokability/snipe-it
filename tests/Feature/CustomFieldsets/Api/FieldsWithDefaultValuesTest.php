<?php

namespace Tests\Feature\CustomFieldsets\Api;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FieldsWithDefaultValuesTest extends TestCase
{
    public function test_returns_fields_with_resolved_default_values_for_the_given_model()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $fieldA = CustomField::factory()->macAddress()->create();
        $fieldB = CustomField::factory()->ram()->create();

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($fieldA->id, ['order' => 1, 'required' => false]);
        $fieldset->fields()->attach($fieldB->id, ['order' => 2, 'required' => false]);

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $model->defaultValues()->attach($fieldA->id, ['default_value' => 'aa:bb:cc:dd:ee:ff']);
        $model->defaultValues()->attach($fieldB->id, ['default_value' => '16GB']);

        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($superuser)
            ->postJson(route('api.fieldsets.fields-with-default-value', [
                'fieldset' => $fieldset->id,
                'model' => $model->id,
            ]))
            ->assertOk()
            ->json();

        $byId = [];
        foreach ($response['rows'] as $row) {
            $byId[$row['id']] = $row;
        }
        $this->assertSame('aa:bb:cc:dd:ee:ff', $byId[$fieldA->id]['default_value']);
        $this->assertSame('16GB', $byId[$fieldB->id]['default_value']);

        $this->assertNotNull($fieldA->id, 'macAddress() factory should persist');
        $this->assertNotNull($fieldB->id, 'ram() factory should persist');
    }

    public function test_does_not_lazy_load_default_values_per_field()
    {
        // Regression test for the N+1 on CustomField::defaultValue($modelId)
        // on the fieldset-with-defaults endpoint. The controller used to
        // read $set->fields (lazy-loaded, no relation hint) and the
        // transformer called $field->defaultValue($modelId) per field,
        // each firing a `SELECT models.* INNER JOIN models_custom_fields`
        // against the full set of AssetModel rows with any default for
        // that field. Defaults are now loaded once from the owner and
        // shared across the per-field transformer calls.
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $fieldA = CustomField::factory()->macAddress()->create();
        $fieldB = CustomField::factory()->ram()->create();
        $fieldC = CustomField::factory()->cpu()->create();

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($fieldA->id, ['order' => 1, 'required' => false]);
        $fieldset->fields()->attach($fieldB->id, ['order' => 2, 'required' => false]);
        $fieldset->fields()->attach($fieldC->id, ['order' => 3, 'required' => false]);

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $model->defaultValues()->attach($fieldA->id, ['default_value' => 'default-mac']);
        $model->defaultValues()->attach($fieldB->id, ['default_value' => 'default-ram']);
        $model->defaultValues()->attach($fieldC->id, ['default_value' => 'default-cpu']);

        $superuser = User::factory()->superuser()->create();

        DB::enableQueryLog();

        $this->actingAsForApi($superuser)
            ->postJson(route('api.fieldsets.fields-with-default-value', [
                'fieldset' => $fieldset->id,
                'model' => $model->id,
            ]))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $pivotWalks = collect($queries)
            ->filter(fn ($q) => str_contains($q['query'], 'models_custom_fields'))
            ->count();

        $this->assertLessThanOrEqual(
            1,
            $pivotWalks,
            'Endpoint fired '.$pivotWalks.' default-value pivot queries. Expected at most 1.'
        );
    }
}
