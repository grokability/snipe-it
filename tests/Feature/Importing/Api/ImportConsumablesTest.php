<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Actionlog as ActivityLog;
use App\Models\Consumable;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\ConsumablesImportFileBuilder as ImportFileBuilder;

class ImportConsumablesTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'consumable';
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
    public function userWithImportAssetsPermissionCanImportConsumables(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->consumable()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function importConsumables(): void
    {
        Notification::fake();

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('consumables.index')]
            ]);

        $newConsumable = Consumable::query()
            ->with(['location', 'category', 'company'])
            ->where('name', $row['itemName'])
            ->sole();

        $activityLog = ActivityLog::query()
            ->where('item_type', Consumable::class)
            ->where('item_id', $newConsumable->id)
            ->sole();

        $this->assertEquals('create', $activityLog->action_type);
        $this->assertEquals('importer', $activityLog->action_source);
        $this->assertEquals($newConsumable->company->id, $activityLog->company_id);

        $this->assertEquals($row['itemName'], $newConsumable->name);
        $this->assertEquals($row['category'], $newConsumable->category->name);
        $this->assertEquals($row['location'], $newConsumable->location->name);
        $this->assertEquals($row['companyName'], $newConsumable->company->name);
        $this->assertNotNull($newConsumable->supplier_id);
        $this->assertFalse($newConsumable->requestable);
        $this->assertNull($newConsumable->image);
        $this->assertEquals($row['orderNumber'], $newConsumable->order_number);
        $this->assertEquals($row['purchaseDate'], $newConsumable->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $newConsumable->purchase_cost);
        $this->assertNull($newConsumable->min_amt);
        $this->assertEquals('', $newConsumable->model_number);
        $this->assertNull($newConsumable->item_number);
        $this->assertNull($newConsumable->manufacturer_id);
        $this->assertNull($newConsumable->notes);
    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function willNotCreateNewConsumableWhenConsumableNameAlreadyExist(): void
    {
        $consumable = Consumable::factory()->create(['name' => Str::random()]);
        $importFileBuilder = ImportFileBuilder::new(['itemName' => $consumable->name]);
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $probablyNewConsumables = Consumable::query()
            ->where('name', $consumable->name)
            ->get();

        $this->assertCount(1, $probablyNewConsumables);
        $this->assertEquals($consumable->id, $probablyNewConsumables->sole()->id);
    }

    #[Test]
    public function willNotCreateNewCompanyWhenCompanyExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['companyName' => Str::random()]);
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newConsumables = Consumable::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get(['company_id']);

        $this->assertCount(1, $newConsumables->pluck('company_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewLocationWhenLocationExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['location' => Str::random()]);
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newConsumables = Consumable::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get(['location_id']);

        $this->assertCount(1, $newConsumables->pluck('location_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewCategoryWhenCategoryExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['category' => Str::random()]);
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newConsumables = Consumable::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get(['category_id']);

        $this->assertCount(1, $newConsumables->pluck('category_id')->unique()->all());
    }

    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['category' => ''])->forget(['quantity', 'name']);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    $row['itemName'] => [
                        'Consumable' => [
                            'category_id' => ['The category id field is required.']
                        ]
                    ]
                ]
            ]);

        $newConsumables = Consumable::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get(['id']);

        $this->assertCount(0, $newConsumables);
    }

    #[Test]
    public function updateConsumableFromImport(): void
    {
        $consumable = Consumable::factory()->create(['name' => Str::random()]);
        $importFileBuilder = ImportFileBuilder::new(['itemName' => $consumable->name]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedConsumable = Consumable::query()
            ->with(['location', 'category', 'company'])
            ->where('name', $importFileBuilder->firstRow()['itemName'])
            ->sole();

        $this->assertEquals($row['itemName'], $updatedConsumable->name);
        $this->assertEquals($row['category'], $updatedConsumable->category->name);
        $this->assertEquals($row['location'], $updatedConsumable->location->name);
        $this->assertEquals($row['companyName'], $updatedConsumable->company->name);
        $this->assertEquals($row['orderNumber'], $updatedConsumable->order_number);
        $this->assertEquals($row['purchaseDate'], $updatedConsumable->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $updatedConsumable->purchase_cost);

        $this->assertEquals($row['supplier'], $updatedConsumable->supplier->name);
        $this->assertEquals($consumable->requestable, $updatedConsumable->requestable);
        $this->assertEquals($consumable->min_amt, $updatedConsumable->min_amt);
        $this->assertEquals($consumable->model_number, $updatedConsumable->model_number);
        $this->assertEquals($consumable->item_number, $updatedConsumable->item_number);
        $this->assertEquals($consumable->manufacturer_id, $updatedConsumable->manufacturer_id);
        $this->assertEquals($consumable->notes, $updatedConsumable->notes);
        $this->assertEquals($consumable->item_number, $updatedConsumable->item_number);
    }

    #[Test]
    public function customColumnMapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'category'     => $faker['supplier'],
            'companyName'  => $faker['quantity'],
            'itemName'     => $faker['purchaseDate'],
            'location'     => $faker['purchaseCost'],
            'orderNumber'  => $faker['orderNumber'],
            'purchaseCost' => $faker['location'],
            'purchaseDate' => $faker['companyName'],
            'quantity'     => $faker['itemName'],
            'supplier'     => $faker['category']
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);

        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());


        // This mapping is incorrect on purpose
        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Category'      => 'supplier',
                'Company'       => 'quantity',
                'item Name'     => 'purchase_date',
                'Location'      => 'purchase_cost',
                'Order Number'  => 'order_number',
                'Purchase Cost' => 'location',
                'Purchase Date' => 'company',
                'Quantity'      => 'item_name',
                'Supplier'      => 'category',
            ]
        ])->assertOk();

        $newConsumable = Consumable::query()
            ->with(['location', 'category', 'company'])
            ->where('name', $importFileBuilder->firstRow()['quantity'])
            ->sole();

        $this->assertEquals($row['supplier'], $newConsumable->category->name);
        $this->assertEquals($row['purchaseCost'], $newConsumable->location->name);
        $this->assertEquals($row['purchaseDate'], $newConsumable->company->name);
        $this->assertEquals($row['companyName'], $newConsumable->qty);
        $this->assertEquals($row['quantity'], $newConsumable->name);
        $this->assertNotNull($newConsumable->supplier_id);
        $this->assertFalse($newConsumable->requestable);
        $this->assertNull($newConsumable->image);
        $this->assertEquals($row['orderNumber'], $newConsumable->order_number);
        $this->assertEquals($row['itemName'], $newConsumable->purchase_date->toDateString());
        $this->assertEquals($row['location'], $newConsumable->purchase_cost);
        $this->assertNull($newConsumable->min_amt);
        $this->assertEquals('', $newConsumable->model_number);
        $this->assertNull($newConsumable->item_number);
        $this->assertNull($newConsumable->manufacturer_id);
        $this->assertNull($newConsumable->notes);
    }
}
