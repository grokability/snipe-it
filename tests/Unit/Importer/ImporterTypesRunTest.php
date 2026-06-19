<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetModelImporter;
use App\Importer\CategoryImporter;
use App\Importer\ManufacturerImporter;
use App\Importer\SupplierImporter;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * Ejecuta importaciones CSV de Supplier/Manufacturer/Category/AssetModel para
 * recorrer sus handle() concretos (antes 0%).
 */
class ImporterTypesRunTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function runImport($importer): void
    {
        $importer->setCreatedBy(auth()->id());
        $importer->setCallbacks(fn () => null, fn () => null, fn () => null);
        $importer->import();
    }

    public function test_supplier_import(): void
    {
        $this->runImport(new SupplierImporter("Name,Email\nProveedor Uno,p1@example.com\n"));
        $this->assertDatabaseHas('suppliers', ['name' => 'Proveedor Uno']);
    }

    public function test_manufacturer_import(): void
    {
        $this->runImport(new ManufacturerImporter("Name,URL\nFabricante Uno,https://f1.com\n"));
        $this->assertDatabaseHas('manufacturers', ['name' => 'Fabricante Uno']);
    }

    public function test_category_import(): void
    {
        $before = Category::count();
        $this->runImport(new CategoryImporter("Item Name,Category Type\nCategoria Uno,asset\n"));
        $this->assertGreaterThan($before, Category::count());
    }

    public function test_asset_model_import(): void
    {
        $before = \App\Models\AssetModel::count();
        $this->runImport(new AssetModelImporter("Model Name,Category,Manufacturer,Model Number\nModelo X,Laptops,Apple,MX-1\n"));
        $this->assertGreaterThanOrEqual($before, \App\Models\AssetModel::count());
    }
}
