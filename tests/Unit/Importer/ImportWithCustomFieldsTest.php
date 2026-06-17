<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetImporter;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Importa un activo cuyo modelo tiene un fieldset con un custom field cuyo
 * nombre coincide con una columna del CSV, recorriendo populateCustomFields()
 * y la asignacion de valores de custom field en ItemImporter.
 */
class ImportWithCustomFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_asset_import_maps_custom_field(): void
    {
        Statuslabel::factory()->readyToDeploy()->create();

        $field = CustomField::factory()->create(['name' => 'RAM']);
        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field->id, ['required' => false, 'order' => 1]);

        // Modelo pre-creado con el fieldset; el importador lo reutilizara por nombre.
        AssetModel::factory()->create(['name' => 'CustomModel', 'fieldset_id' => $fieldset->id]);

        $csv = "Asset Tag,Model Name,Category,Status,RAM\nCF-1,CustomModel,Laptops,Ready to Deploy,16GB\n";

        $importer = new AssetImporter($csv);
        $importer->setCreatedBy(auth()->id());
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();

        $this->assertDatabaseHas('assets', ['asset_tag' => 'CF-1']);

        $asset = \App\Models\Asset::where('asset_tag', 'CF-1')->first();
        $this->assertEquals('16GB', $asset->{$field->db_column});
    }
}
