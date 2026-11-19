<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;

test('blanks orphaned field but preserves field in fieldset', function () {
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
    expect($asset->{$inFieldsetColumn})->toBe('KEEP-ME', 'Value for a field that is in the model\'s fieldset should be preserved');
    expect($asset->{$orphanedColumn})->toBeNull('Value for a field that is not in the model\'s fieldset should be blanked');
});

test('blanks all custom field values for model with no fieldset', function () {
    $field = CustomField::factory()->create();
    $column = $field->db_column_name();

    $modelWithoutFieldset = AssetModel::factory()->create(['fieldset_id' => null]);

    $asset = Asset::factory()->for($modelWithoutFieldset, 'model')->create();
    $asset->{$column} = 'SHOULD-BE-BLANKED';
    $asset->save();

    $this->artisan('snipeit:clean-custom-fields', ['--force' => true])->assertExitCode(0);

    $asset->refresh();
    expect($asset->{$column})->toBeNull('A field value on an asset whose model has no fieldset at all should be blanked');
});
