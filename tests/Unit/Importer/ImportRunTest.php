<?php

namespace Tests\Unit\Importer;

use App\Importer\AccessoryImporter;
use App\Importer\AssetImporter;
use App\Importer\ComponentImporter;
use App\Importer\ConsumableImporter;
use App\Importer\LicenseImporter;
use App\Importer\LocationImporter;
use App\Importer\UserImporter;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Ejecuta importaciones CSV completas para recorrer Importer::import(),
 * los handle() concretos y la base ItemImporter (creacion de modelos,
 * categorias, fabricantes, ubicaciones, usuarios, etc.).
 */
class ImportRunTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_user_import_creates_users(): void
    {
        $csv = "First Name,Last Name,Email,Username,Department\n"
             . "Jane,Doe,jane.doe@example.com,jdoe,Engineering\n"
             . "Bob,Smith,bob.smith@example.com,bsmith,Sales\n";

        $before = User::count();

        $importer = new UserImporter($csv);
        $importer->setCreatedBy(auth()->id());
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();

        $this->assertGreaterThan($before, User::count());
        $this->assertDatabaseHas('users', ['username' => 'jdoe']);
    }

    public function test_asset_import_creates_assets(): void
    {
        Statuslabel::factory()->readyToDeploy()->create();

        $csv = "Asset Tag,Model Name,Category,Manufacturer,Status\n"
             . "IMP-001,MacBook Pro,Laptops,Apple,Ready to Deploy\n"
             . "IMP-002,MacBook Pro,Laptops,Apple,Ready to Deploy\n";

        $before = Asset::count();

        $importer = new AssetImporter($csv);
        $importer->setCreatedBy(auth()->id());
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();

        $this->assertGreaterThan($before, Asset::count());
        $this->assertDatabaseHas('assets', ['asset_tag' => 'IMP-001']);
    }

    private function runImport(string $importerClass, string $csv): void
    {
        $importer = new $importerClass($csv);
        $importer->setCreatedBy(auth()->id());
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();
    }

    public function test_accessory_import_creates_accessories(): void
    {
        $this->runImport(AccessoryImporter::class, "Item Name,Category,Quantity\nUSB Cable,Cables,10\n");

        $this->assertDatabaseHas('accessories', ['name' => 'USB Cable']);
    }

    public function test_consumable_import_creates_consumables(): void
    {
        $this->runImport(ConsumableImporter::class, "Item Name,Category,Quantity\nToner,Supplies,5\n");

        $this->assertDatabaseHas('consumables', ['name' => 'Toner']);
    }

    public function test_component_import_creates_components(): void
    {
        $this->runImport(ComponentImporter::class, "Item Name,Category,Quantity,Serial\nRAM 8GB,Memory,4,SN-RAM-1\n");

        $this->assertDatabaseHas('components', ['name' => 'RAM 8GB']);
    }

    public function test_license_import_creates_licenses(): void
    {
        $this->runImport(LicenseImporter::class, "Item Name,Seats,Category\nOffice 365,5,Software\n");

        $this->assertDatabaseHas('licenses', ['name' => 'Office 365']);
    }

    public function test_location_import_creates_locations(): void
    {
        $this->runImport(LocationImporter::class, "Name\nHeadquarters\n");

        $this->assertDatabaseHas('locations', ['name' => 'Headquarters']);
    }

    public function test_asset_model_import_creates_models(): void
    {
        $this->runImport(\App\Importer\AssetModelImporter::class, "Name,Category,Manufacturer,Model Number\nMacBook Air,Laptops,Apple,MBA-13\n");

        $this->assertDatabaseHas('models', ['name' => 'MacBook Air']);
    }
}
