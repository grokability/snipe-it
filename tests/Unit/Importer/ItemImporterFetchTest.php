<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetImporter;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre los metodos createOrFetch* y fetchManager de ItemImporter, ejercitando
 * tanto la rama "ya existe" como la rama "crear nuevo" y los defaults de vacio.
 */
class ItemImporterFetchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function importer(): AssetImporter
    {
        return new AssetImporter("name\nfoo");
    }

    public function test_create_or_fetch_category_creates_and_reuses(): void
    {
        $importer = $this->importer();

        $id = $importer->createOrFetchCategory('Servidores');
        $this->assertNotNull($id);
        $this->assertDatabaseHas('categories', ['id' => $id, 'category_type' => 'asset']);

        // segunda llamada con el mismo nombre reutiliza la categoria existente.
        $this->assertEquals($id, $importer->createOrFetchCategory('Servidores'));
    }

    public function test_create_or_fetch_category_defaults_to_unnamed(): void
    {
        $id = $this->importer()->createOrFetchCategory('');
        $this->assertDatabaseHas('categories', ['id' => $id, 'name' => 'Unnamed Category']);
    }

    public function test_create_or_fetch_company_creates_and_reuses(): void
    {
        $importer = $this->importer();

        $id = $importer->createOrFetchCompany('Acme Corp');
        $this->assertDatabaseHas('companies', ['id' => $id, 'name' => 'Acme Corp']);
        $this->assertEquals($id, $importer->createOrFetchCompany('Acme Corp'));
    }

    public function test_create_or_fetch_location_creates_reuses_and_empty(): void
    {
        $importer = $this->importer();

        $this->assertNull($importer->createOrFetchLocation(''));

        $id = $importer->createOrFetchLocation('Bodega Central');
        $this->assertDatabaseHas('locations', ['id' => $id, 'name' => 'Bodega Central']);
        $this->assertEquals($id, $importer->createOrFetchLocation('Bodega Central'));
    }

    public function test_create_or_fetch_manufacturer_creates_reuses_and_unknown(): void
    {
        $importer = $this->importer();

        // vacio -> usa "Unknown".
        $unknownId = $importer->createOrFetchManufacturer('');
        $this->assertDatabaseHas('manufacturers', ['id' => $unknownId, 'name' => 'Unknown']);

        $id = $importer->createOrFetchManufacturer('Dell');
        $this->assertDatabaseHas('manufacturers', ['id' => $id, 'name' => 'Dell']);
        $this->assertEquals($id, $importer->createOrFetchManufacturer('Dell'));
    }

    public function test_create_or_fetch_status_label_creates_reuses_and_empty(): void
    {
        $importer = $this->importer();

        $this->assertNull($importer->createOrFetchStatusLabel(''));

        $id = $importer->createOrFetchStatusLabel('Listo para usar');
        $this->assertDatabaseHas('status_labels', ['id' => $id, 'name' => 'Listo para usar', 'deployable' => 1]);
        $this->assertEquals($id, $importer->createOrFetchStatusLabel('Listo para usar'));
    }

    public function test_create_or_fetch_supplier_creates_reuses_and_unknown(): void
    {
        $importer = $this->importer();

        $unknownId = $importer->createOrFetchSupplier('');
        $this->assertDatabaseHas('suppliers', ['id' => $unknownId, 'name' => 'Unknown']);

        $id = $importer->createOrFetchSupplier('Proveedor X');
        $this->assertDatabaseHas('suppliers', ['id' => $id, 'name' => 'Proveedor X']);
        $this->assertEquals($id, $importer->createOrFetchSupplier('Proveedor X'));
    }

    public function test_fetch_manager_by_username(): void
    {
        $manager = User::factory()->create(['username' => 'jefe1']);

        $this->assertEquals($manager->id, $this->importer()->fetchManager('jefe1'));
    }

    public function test_fetch_manager_by_employee_num(): void
    {
        $manager = User::factory()->create(['employee_num' => 'E-9001']);

        $this->assertEquals($manager->id, $this->importer()->fetchManager('', 'E-9001'));
    }

    public function test_fetch_manager_by_full_name(): void
    {
        $manager = User::factory()->create(['first_name' => 'Carla', 'last_name' => 'Mendez']);

        $this->assertEquals($manager->id, $this->importer()->fetchManager('', '', 'Carla', 'Mendez'));
    }

    public function test_fetch_manager_returns_null_when_not_found(): void
    {
        $this->assertNull($this->importer()->fetchManager('no-existe'));
    }

    public function test_create_or_fetch_asset_model_creates_and_reuses(): void
    {
        $importer = $this->importer();
        // Las claves del row deben ser los headers reales del field map.
        $row = ['model name' => 'OptiPlex 7090', 'category' => 'Desktops', 'model number' => 'OP7090'];

        $id = $importer->createOrFetchAssetModel($row);
        $this->assertDatabaseHas('models', ['id' => $id, 'name' => 'OptiPlex 7090']);

        // mismo nombre + numero -> reutiliza.
        $this->assertEquals($id, $importer->createOrFetchAssetModel($row));
    }
}
