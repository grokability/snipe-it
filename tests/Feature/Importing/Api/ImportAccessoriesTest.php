<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Import;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\AccessoriesImportFileBuilder as ImportFileBuilder;
use Tests\Support\Importing\CleansUpImportFiles;

class ImportAccessoriesTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'accessory';
        }

        return parent::importFileResponse($parameters);
    }

    #[Test]
    public function testRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create());

        $this->importFileResponse(['import' => 44])->assertForbidden();
    }

    #[Test]
    public function userWithImportAccessoryPermissionCanImportAccessories(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->accessory()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function importAccessory(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => [
                    'redirect_url' => route('accessories.index')
                ]
            ]);

        $newAccessory = Accessory::query()
            ->with(['location', 'category', 'manufacturer', 'supplier', 'company'])
            ->where('name', $row['itemName'])
            ->sole();

        $activityLog = Actionlog::query()
            ->where('item_type', Accessory::class)
            ->where('item_id', $newAccessory->id)
            ->sole();

        $this->assertEquals('create', $activityLog->action_type);
        $this->assertEquals('importer', $activityLog->action_source);
        $this->assertEquals($newAccessory->company->id, $activityLog->company_id);

        $this->assertEquals($row['itemName'], $newAccessory->name);
        $this->assertEquals($row['quantity'], $newAccessory->qty);
        $this->assertEquals($row['purchaseDate'], $newAccessory->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $newAccessory->purchase_cost);
        $this->assertEquals($row['orderNumber'], $newAccessory->order_number);
        $this->assertEquals($row['notes'], $newAccessory->notes);
        $this->assertEquals($row['category'], $newAccessory->category->name);
        $this->assertEquals('accessory', $newAccessory->category->category_type);
        $this->assertEquals($row['manufacturerName'], $newAccessory->manufacturer->name);
        $this->assertEquals($row['supplierName'], $newAccessory->supplier->name);
        $this->assertEquals($row['location'], $newAccessory->location->name);
        $this->assertEquals($row['companyName'], $newAccessory->company->name);
        $this->assertEquals($row['modelNumber'], $newAccessory->model_number);
        $this->assertFalse($newAccessory->requestable);
        $this->assertNull($newAccessory->min_amt);
        $this->assertNull($newAccessory->user_id);
    }

    #[Test]
    public function whenImportFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumn'] = $this->faker->word;

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function willFormatDate(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['purchaseDate' => '2022/10/10']);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $accessory = Accessory::query()
            ->where('name', $importFileBuilder->firstRow()['itemName'])
            ->sole(['purchase_date']);

        $this->assertEquals('2022-10-10', $accessory->purchase_date->toDateString());
    }

    #[Test]
    public function willNotCreateNewCategoryWhenCategoryExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['category' => Str::random()]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessories = Accessory::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get();

        $this->assertCount(1, $newAccessories->pluck('category_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewAccessoryWhenAccessoryWithNameExists(): void
    {
        $accessory = Accessory::factory()->create(['name' => Str::random()]);
        $importFileBuilder = ImportFileBuilder::times(2)->replace(['itemName' => $accessory->name]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $probablyNewAccessories = Accessory::query()
            ->where('name', $importFileBuilder->pluck('itemName'))
            ->get(['name']);

        $this->assertCount(1, $probablyNewAccessories);
        $this->assertEquals($accessory->name, $probablyNewAccessories->first()->name);
    }

    #[Test]
    public function willNotCreateNewCompanyWhenCompanyAlreadyExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['companyName' => Str::random()]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessories = Accessory::query()
            ->where('name', $importFileBuilder->pluck('itemName'))
            ->get(['company_id']);

        $this->assertCount(1, $newAccessories->pluck('company_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewLocationWhenLocationAlreadyExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['location' => Str::random()]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessories = Accessory::query()
            ->where('name', $importFileBuilder->pluck('itemName'))
            ->get(['location_id']);

        $this->assertCount(1, $newAccessories->pluck('location_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewManufacturerWhenManufacturerAlreadyExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['manufacturerName' => $this->faker->company]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessories = Accessory::query()
            ->where('name', $importFileBuilder->pluck('itemName'))
            ->get(['manufacturer_id']);

        $this->assertCount(1, $newAccessories->pluck('manufacturer_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewSupplierWhenSupplierAlreadyExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['supplierName' => $this->faker->company]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessories = Accessory::query()
            ->where('name', $importFileBuilder->pluck('itemName'))
            ->get(['supplier_id']);

        $this->assertCount(1, $newAccessories->pluck('supplier_id')->unique()->all());
    }

    #[Test]
    public function whenColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new()->forget(['minimumAmount', 'purchaseCost', 'purchaseDate']);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessory = Accessory::query()
            ->where('name', $importFileBuilder->firstRow()['itemName'])
            ->sole();

        $this->assertNull($newAccessory->min_amt);
        $this->assertNull($newAccessory->purchase_date);
        $this->assertNull($newAccessory->purchase_cost);
    }

    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new()->forget(['itemName', 'quantity', 'category']);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => null,
                'messages' => [
                    '' => [
                        'Accessory' => [
                            'name' => ['The name field is required.'],
                            'qty' => ['The qty field must be at least 1.'],
                            'category_id' => ['The category id field is required.']
                        ]
                    ]
                ]
            ]);
    }

    #[Test]
    public function updateAccessoryFromImport(): void
    {
        $accessory = Accessory::factory()->create(['name' => Str::random()])->refresh();
        $importFileBuilder = ImportFileBuilder::new(['itemName' => $accessory->name]);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedAccessory = Accessory::query()->find($accessory->id);
        $updatedAttributes = [
            'name', 'company_id', 'qty', 'purchase_date', 'purchase_cost',
            'order_number', 'notes', 'category_id', 'manufacturer_id', 'supplier_id',
            'location_id', 'model_number', 'updated_at'
        ];

        $this->assertEquals($row['itemName'], $updatedAccessory->name);
        $this->assertEquals($row['companyName'], $updatedAccessory->company->name);
        $this->assertEquals($row['quantity'], $updatedAccessory->qty);
        $this->assertEquals($row['purchaseDate'], $updatedAccessory->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $updatedAccessory->purchase_cost);
        $this->assertEquals($row['orderNumber'], $updatedAccessory->order_number);
        $this->assertEquals($row['notes'], $updatedAccessory->notes);
        $this->assertEquals($row['category'], $updatedAccessory->category->name);
        $this->assertEquals('accessory', $updatedAccessory->category->category_type);
        $this->assertEquals($row['manufacturerName'], $updatedAccessory->manufacturer->name);
        $this->assertEquals($row['supplierName'], $updatedAccessory->supplier->name);
        $this->assertEquals($row['location'], $updatedAccessory->location->name);
        $this->assertEquals($row['modelNumber'], $updatedAccessory->model_number);

        $this->assertEquals(
            Arr::except($accessory->attributesToArray(), $updatedAttributes),
            Arr::except($updatedAccessory->attributesToArray(), $updatedAttributes),
        );
    }

    #[Test]
    public function whenImportFileContainsEmptyValues(): void
    {
        $accessory = Accessory::factory()->create(['name' => Str::random()]);
        $accessory->refresh();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName'      => ' ',
            'purchaseDate'     => '  ',
            'purchaseCost'     => '',
            'location'         => '',
            'companyName'      => '',
            'orderNumber'      => '',
            'category'         => '',
            'quantity'         => '',
            'manufacturerName' => '',
            'supplierName'     => '',
            'notes'            => '',
            'requestAble'      => '',
            'minimumAmount'    => '',
            'modelNumber'      => ''
        ]);

        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => null,
                'messages' => [
                    $importFileBuilder->firstRow()['itemName'] => [
                        'Accessory' => [
                            'qty' => ['The qty field must be at least 1.'],
                            'category_id' => ['The category id field is required.']
                        ]
                    ]
                ]
            ]);

        $importFileBuilder->replace(['itemName' => $accessory->name]);

        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedAccessory = clone $accessory;
        $updatedAccessory->refresh();

        $this->assertEquals($accessory->toArray(), $updatedAccessory->toArray());
    }

    #[Test]
    public function customColumnMapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'itemName'         => $faker['modelNumber'],
            'purchaseDate'     => $faker['notes'],
            'purchaseCost'     => $faker['location'],
            'location'         => $faker['purchaseCost'],
            'companyName'      => $faker['orderNumber'],
            'orderNumber'      => $faker['companyName'],
            'category'         => $faker['manufacturerName'],
            'manufacturerName' => $faker['category'],
            'notes'            => $faker['purchaseDate'],
            'minimumAmount'    => $faker['supplierName'],
            'modelNumber'      => $faker['itemName'],
            'quantity'         => $faker['quantity']
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Item Name'     => 'model_number',
                'Purchase Date' => 'notes',
                'Purchase Cost' => 'location',
                'Location'      => 'purchase_cost',
                'Company'       => 'order_number',
                'Order Number'  => 'company',
                'Category'      => 'manufacturer',
                'Manufacturer'  => 'category',
                'Supplier'      => 'min_amt',
                'Notes'         => 'purchase_date',
                'Min QTY'       => 'supplier',
                'Model Number'  => 'item_name',
                'Quantity'      => 'quantity'
            ]
        ])->assertOk();

        $newAccessory = Accessory::query()
            ->with(['location', 'category', 'manufacturer', 'supplier'])
            ->where('name', $row['modelNumber'])
            ->sole();

        $this->assertEquals($row['modelNumber'], $newAccessory->name);
        $this->assertEquals($row['itemName'], $newAccessory->model_number);
        $this->assertEquals($row['quantity'], $newAccessory->qty);
        $this->assertEquals($row['notes'], $newAccessory->purchase_date->toDateString());
        $this->assertEquals($row['location'], $newAccessory->purchase_cost);
        $this->assertEquals($row['companyName'], $newAccessory->order_number);
        $this->assertEquals($row['purchaseDate'], $newAccessory->notes);
        $this->assertEquals($row['manufacturerName'], $newAccessory->category->name);
        $this->assertEquals($row['category'], $newAccessory->manufacturer->name);
        $this->assertEquals($row['purchaseCost'], $newAccessory->location->name);
    }
}
