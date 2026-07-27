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
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'consumable';
        }

        return parent::importFileResponse($parameters);
    }

    #[Test]
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create());

        $this->importFileResponse(['import' => 44])->assertForbidden();
    }

    #[Test]
    public function user_with_import_assets_permission_can_import_consumables(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->consumable()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function import_consumables(): void
    {
        Notification::fake();

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload' => null,
                'status' => 'success',
                'messages' => ['redirect_url' => route('consumables.index')],
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
    public function will_ignore_unknown_columns_when_file_contains_unknown_columns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function will_not_create_new_consumable_when_consumable_name_already_exist(): void
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
    public function will_not_create_new_company_when_company_exists(): void
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
    public function will_not_create_new_location_when_location_exists(): void
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
    public function will_not_create_new_category_when_category_exists(): void
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
    public function when_required_columns_are_missing_in_import_file(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['category' => ''])->forget(['quantity', 'name']);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => null,
                'messages' => [
                    $row['itemName'] => [
                        'Consumable' => [
                            'category_id' => ['The category id field is required.'],
                        ],
                    ],
                ],
            ]);

        $newConsumables = Consumable::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get(['id']);

        $this->assertCount(0, $newConsumables);
    }

    #[Test]
    public function update_consumable_from_import(): void
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
    public function update_mode_logs_consumable_update_in_actionlog(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();

        $initialImport = Import::factory()->consumable()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $consumable = Consumable::query()->where('name', $initialRow['itemName'])->sole();

        $updatedRow = array_merge($initialRow, [
            'orderNumber' => (string) $initialRow['orderNumber'].'-UPD',
        ]);

        $updateFile = new ImportFileBuilder([$updatedRow]);
        $updateImport = Import::factory()->consumable()->create([
            'file_path' => $updateFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $updateImport->id,
            'import-update' => true,
        ])->assertOk();

        $consumable->refresh();
        $this->assertEquals($updatedRow['orderNumber'], $consumable->order_number);

        $updateLog = ActivityLog::query()
            ->where('item_type', Consumable::class)
            ->where('item_id', $consumable->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog, 'Expected an update action log entry after consumable importer update mode.');
    }

    #[Test]
    public function custom_column_mapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'category' => $faker['supplier'],
            'companyName' => $faker['quantity'],
            'itemName' => $faker['purchaseDate'],
            'location' => $faker['purchaseCost'],
            'orderNumber' => $faker['orderNumber'],
            'purchaseCost' => $faker['location'],
            'purchaseDate' => $faker['companyName'],
            'quantity' => $faker['itemName'],
            'supplier' => $faker['category'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);

        $import = Import::factory()->consumable()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        // This mapping is incorrect on purpose
        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Category' => 'supplier',
                'Company' => 'quantity',
                'item Name' => 'purchase_date',
                'Location' => 'purchase_cost',
                'Order Number' => 'order_number',
                'Purchase Cost' => 'location',
                'Purchase Date' => 'company',
                'Quantity' => 'item_name',
                'Supplier' => 'category',
            ],
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

    #[Test]
    public function consumable_import_checks_out_to_user_when_username_matches(): void
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['username' => 'consumabletarget']);

        $csv = "Item Name,Category,Quantity,Company,Checkout Type,Username\n"
            .'CSV-Checked-Consumable,Toner,10,CSVCo,user,'.$target->username."\n";
        $filename = 'consumable-checkout-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = Import::factory()->consumable()->create(['file_path' => $filename]);

            $this->actingAsForApi($actor);
            $this->importFileResponse(['import' => $import->id])->assertOk();

            $consumable = Consumable::query()->where('name', 'CSV-Checked-Consumable')->sole();
            $this->assertEquals(10, $consumable->qty);

            $this->assertDatabaseHas('consumables_users', [
                'consumable_id' => $consumable->id,
                'assigned_to' => $target->id,
            ]);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }

    #[Test]
    public function consumable_import_ignores_location_checkout_target(): void
    {
        // consumables_users only supports user targets - a location
        // target in the CSV should be silently skipped rather than
        // producing an invalid pivot row.
        $actor = User::factory()->superuser()->create();

        $csv = "Item Name,Category,Quantity,Company,Checkout Type,Checkout Location\n"
            ."CSV-Loc-Consumable,Toner,4,CSVCo,location,HQ\n";
        $filename = 'consumable-loc-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = Import::factory()->consumable()->create(['file_path' => $filename]);

            $this->actingAsForApi($actor);
            $this->importFileResponse(['import' => $import->id])->assertOk();

            $consumable = Consumable::query()->where('name', 'CSV-Loc-Consumable')->sole();
            $this->assertDatabaseMissing('consumables_users', [
                'consumable_id' => $consumable->id,
            ]);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }
}
