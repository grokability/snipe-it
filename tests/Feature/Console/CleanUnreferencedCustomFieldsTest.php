<?php

namespace Tests\Feature\Console;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Tests\TestCase;

class CleanUnreferencedCustomFieldsTest extends TestCase
{
    public function test_blanks_orphaned_field_but_preserves_field_in_fieldset(): void
    {
        // One asset carries values for two custom fields: one is in its
        // model's fieldset, the other is not (e.g. removed from the
        // fieldset after the value was written). A single asset is used
        // for both fields so the test can't pass by the command doing
        // nothing at all.
        $inFieldset = CustomField::factory()->create();
        $orphaned = CustomField::factory()->create();

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($inFieldset, ['order' => 1, 'required' => false]);

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

        $inFieldsetColumn = $inFieldset->db_column_name();
        $orphanedColumn = $orphaned->db_column_name();

        $asset = Asset::factory()->for($model, 'model')->create();
        $asset->{$inFieldsetColumn} = 'KEEP-ME';
        $asset->{$orphanedColumn} = 'ORPHANED-VALUE';
        $asset->save();

        $this->artisan('snipeit:clean-custom-fields', ['--force' => true])->assertExitCode(0);

        $asset->refresh();
        $this->assertSame('KEEP-ME', $asset->{$inFieldsetColumn},
            'Value for a field that is in the model\'s fieldset should be preserved');
        $this->assertNull($asset->{$orphanedColumn},
            'Value for a field that is not in the model\'s fieldset should be blanked');
    }

    public function test_blanks_all_custom_field_values_for_model_with_no_fieldset(): void
    {
        $field = CustomField::factory()->create();
        $column = $field->db_column_name();

        $modelWithoutFieldset = AssetModel::factory()->create(['fieldset_id' => null]);

        $asset = Asset::factory()->for($modelWithoutFieldset, 'model')->create();
        $asset->{$column} = 'SHOULD-BE-BLANKED';
        $asset->save();

        $this->artisan('snipeit:clean-custom-fields', ['--force' => true])->assertExitCode(0);

        $asset->refresh();
        $this->assertNull($asset->{$column},
            'A field value on an asset whose model has no fieldset at all should be blanked');
    }
}
