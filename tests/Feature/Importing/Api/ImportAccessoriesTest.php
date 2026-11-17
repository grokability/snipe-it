<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\AccessoriesImportFileBuilder as ImportFileBuilder;
use Tests\Support\Importing\CleansUpImportFiles;

class ImportAccessoriesTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'accessory';
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
    public function user_with_import_accessory_permission_can_import_accessories(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->accessory()->withStubFile()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function import_accessory(): void
    {
        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload' => ['tally' => ['created' => 1, 'updated' => 0, 'skipped' => 0, 'errored' => 0]],
                'status' => 'success',
                'messages' => [
                    'redirect_url' => route('accessories.index'),
                ],
            ]);

        $newAccessory = Accessory::query()
            ->with(['location', 'category', 'manufacturer', 'defaultSupplier', 'company'])
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
        // supplier + order_number + purchase_date + purchase_cost all
        // live on the Orders / OrderItems polymorphic pair now — the
        // importer's recordOrderForImportedRow helper writes them there.
        $orderItem = $newAccessory->orderItems()->firstOrFail();
        $this->assertEquals($row['orderNumber'], $orderItem->order->order_number);
        $this->assertEquals($row['purchaseDate'], $orderItem->order->purchase_date->toDateString());
        $this->assertEquals((float) $row['purchaseCost'], (float) $orderItem->price);
        $this->assertEquals($row['supplierName'], $orderItem->order->supplier->name);
        $this->assertEquals($row['notes'], $newAccessory->notes);
        $this->assertEquals($row['category'], $newAccessory->category->name);
        $this->assertEquals('accessory', $newAccessory->category->category_type);
        $this->assertEquals($row['manufacturerName'], $newAccessory->manufacturer->name);
        $this->assertEquals($row['location'], $newAccessory->location->name);
        $this->assertEquals($row['companyName'], $newAccessory->company->name);
        $this->assertEquals($row['modelNumber'], $newAccessory->model_number);
        $this->assertFalse($newAccessory->requestable);
        $this->assertNull($newAccessory->min_amt);
        $this->assertNull($newAccessory->user_id);
    }

    #[Test]
    public function when_import_file_contains_unknown_columns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumn'] = $this->faker->word;

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function will_format_date(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['purchaseDate' => '2022/10/10']);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        // purchase_date landed on the OrderItem's Order rather than the
        // parent Accessory column — assert that the importer's date
        // parser normalized the slashed CSV input to a Y-m-d value.
        $accessory = Accessory::query()
            ->where('name', $importFileBuilder->firstRow()['itemName'])
            ->sole();

        $order = $accessory->orderItems()->latest('id')->firstOrFail()->order;
        $this->assertEquals('2022-10-10', $order->purchase_date->toDateString());
    }

    #[Test]
    public function will_not_create_new_category_when_category_exists(): void
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
    public function will_not_create_new_accessory_when_accessory_with_name_exists(): void
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
    public function will_not_create_new_company_when_company_already_exists(): void
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
    public function will_not_create_new_location_when_location_already_exists(): void
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
    public function will_not_create_new_manufacturer_when_manufacturer_already_exists(): void
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
    public function will_not_create_new_supplier_when_supplier_already_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['supplierName' => $this->faker->company]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        // Supplier reuse across imported rows is observable on the
        // OrderItem's Order.supplier_id (parent's default_supplier_id
        // gets seeded from the first-row's supplier for these but the
        // per-row dedupe rule lives on the Order path).
        $newAccessories = Accessory::query()
            ->whereIn('name', $importFileBuilder->pluck('itemName'))
            ->get();

        $supplierIds = $newAccessories->map(
            fn ($accessory) => $accessory->orderItems()->latest('id')->first()?->order?->supplier_id,
        )->filter()->unique()->all();

        $this->assertCount(1, $supplierIds);
    }

    #[Test]
    public function when_columns_are_missing_in_import_file(): void
    {
        $importFileBuilder = ImportFileBuilder::new()->forget(['minimumAmount', 'purchaseCost', 'purchaseDate']);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAccessory = Accessory::query()
            ->where('name', $importFileBuilder->firstRow()['itemName'])
            ->sole();

        $this->assertNull($newAccessory->min_amt);
        // purchase_date / purchase_cost columns are gone from the parent;
        // when the CSV omits them, the observer-written OrderItem has
        // null price and its Order has null purchase_date.
        $orderItem = $newAccessory->orderItems()->latest('id')->firstOrFail();
        $this->assertNull($orderItem->order->purchase_date);
        $this->assertNull($orderItem->price);
    }

    #[Test]
    public function when_required_columns_are_missing_in_import_file(): void
    {
        $importFileBuilder = ImportFileBuilder::new()->forget(['itemName', 'category']);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => ['tally' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errored' => 1]],
                'messages' => [
                    '' => [
                        'Accessory' => [
                            'category_id' => ['The category id field is required.'],
                            'name' => ['The name field is required.'],
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function update_accessory_from_import(): void
    {
        $accessory = Accessory::factory()->create(['name' => Str::random()])->refresh();
        $importFileBuilder = ImportFileBuilder::new(['itemName' => $accessory->name]);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedAccessory = Accessory::query()->find($accessory->id);
        $updatedAttributes = [
            'name', 'company_id', 'qty', 'default_purchase_cost', 'default_supplier_id',
            'notes', 'category_id', 'manufacturer_id',
            'location_id', 'model_number', 'updated_at',
        ];

        $this->assertEquals($row['itemName'], $updatedAccessory->name);
        $this->assertEquals($row['companyName'], $updatedAccessory->company->name);
        $this->assertEquals($row['quantity'], $updatedAccessory->qty);
        // Update mode does NOT rewrite historical Orders — the CSV's
        // purchase_cost / supplier map to the parent's default_*
        // template fields; purchase_date has no forward-use equivalent
        // and is silently dropped. When qty differs the value rides on
        // the QuantityAdjust log (see importer_qty_change_creates_...).
        $this->assertEquals((float) $row['purchaseCost'], (float) $updatedAccessory->default_purchase_cost);
        $this->assertEquals($row['supplierName'], $updatedAccessory->defaultSupplier->name);
        $this->assertEquals($row['notes'], $updatedAccessory->notes);
        $this->assertEquals($row['category'], $updatedAccessory->category->name);
        $this->assertEquals('accessory', $updatedAccessory->category->category_type);
        $this->assertEquals($row['manufacturerName'], $updatedAccessory->manufacturer->name);
        $this->assertEquals($row['location'], $updatedAccessory->location->name);
        $this->assertEquals($row['modelNumber'], $updatedAccessory->model_number);

        $this->assertEquals(
            Arr::except($accessory->attributesToArray(), $updatedAttributes),
            Arr::except($updatedAccessory->attributesToArray(), $updatedAttributes),
        );
    }

    #[Test]
    public function update_mode_logs_accessory_update_in_actionlog(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();

        $initialImport = Import::factory()->accessory()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $accessory = Accessory::query()->where('name', $initialRow['itemName'])->sole();

        // Change `notes` (a plain fillable field). orderNumber is
        // intentionally NOT the trigger here because
        // ItemImporter::applyUpdateWithQtyAdjust strips order_number from
        // the update payload, so a notes-only diff is what proves the
        // update-log path still fires.
        $updatedRow = array_merge($initialRow, [
            'notes' => (string) ($initialRow['notes'] ?? '').' updated',
        ]);

        $updateFile = new ImportFileBuilder([$updatedRow]);
        $updateImport = Import::factory()->accessory()->create([
            'file_path' => $updateFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $updateImport->id,
            'import-update' => true,
        ])->assertOk();

        $accessory->refresh();
        $this->assertEquals($updatedRow['notes'], $accessory->notes);

        $updateLog = Actionlog::query()
            ->where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog, 'Expected an update action log entry after accessory importer update mode.');
    }

    #[Test]
    public function update_mode_clears_field_when_csv_column_is_present_but_empty(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $accessory = Accessory::factory()->create([
            'notes' => 'Some pre-existing notes',
        ])->refresh();

        $this->assertNotEmpty($accessory->notes);

        // purchase_date moved off the parent post-Orders — the
        // "empty CSV cell clears the DB column" behavior for the
        // parent-owned columns is covered here by notes alone.
        $row = ImportFileBuilder::new()->definition();
        $row['itemName'] = $accessory->name;
        $row['notes'] = '';

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->accessory()->create([
            'file_path' => $importFileBuilder->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $import->id,
            'import-update' => true,
        ])->assertOk();

        $accessory->refresh();
        $this->assertNull($accessory->notes);
    }

    #[Test]
    public function update_mode_preserves_fields_when_csv_column_is_absent(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $accessory = Accessory::factory()->create([
            'notes' => 'Do not lose this',
        ])->refresh();

        $originalNotes = $accessory->notes;

        // Import a CSV that only has the identity field (name) plus one
        // updated column. All other parent columns absent from the CSV
        // must be preserved on update. model_number is the "changed
        // field" proxy; notes is the "preserved field" proxy.
        $partialFile = new ImportFileBuilder([[
            'itemName' => $accessory->name,
            'modelNumber' => 'UPDATED-MODEL-NUMBER',
        ]]);
        $partialImport = Import::factory()->accessory()->create([
            'file_path' => $partialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $partialImport->id,
            'import-update' => true,
        ])->assertOk();

        $accessory->refresh();
        $this->assertEquals('UPDATED-MODEL-NUMBER', $accessory->model_number);
        $this->assertEquals($originalNotes, $accessory->notes);
    }

    #[Test]
    public function when_import_file_contains_empty_values(): void
    {
        $accessory = Accessory::factory()->create(['name' => Str::random()]);
        $accessory->refresh();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => ' ',
            'purchaseDate' => '  ',
            'purchaseCost' => '',
            'location' => '',
            'orderNumber' => '',
            'category' => '',
            'quantity' => '',
            'manufacturerName' => '',
            'supplierName' => '',
            'notes' => '',
            'requestAble' => '',
            'minimumAmount' => '',
            'modelNumber' => '',
        ]);

        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => ['tally' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errored' => 1]],
                'messages' => [
                    $importFileBuilder->firstRow()['itemName'] => [
                        'Accessory' => [
                            'category_id' => ['The category id field is required.'],
                        ],
                    ],
                ],
            ]);

        $importFileBuilder->replace(['itemName' => $accessory->name]);

        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedAccessory = clone $accessory;
        $updatedAccessory->refresh();

        $this->assertEquals($accessory->toArray(), $updatedAccessory->toArray());
    }

    #[Test]
    public function custom_column_mapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'itemName' => $faker['modelNumber'],
            'purchaseDate' => $faker['notes'],
            'purchaseCost' => $faker['location'],
            'location' => $faker['purchaseCost'],
            'companyName' => $faker['orderNumber'],
            'orderNumber' => $faker['companyName'],
            'category' => $faker['manufacturerName'],
            'manufacturerName' => $faker['category'],
            'notes' => $faker['purchaseDate'],
            'minimumAmount' => $faker['supplierName'],
            'modelNumber' => $faker['itemName'],
            'quantity' => $faker['quantity'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->accessory()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Item Name' => 'model_number',
                'Purchase Date' => 'notes',
                'Purchase Cost' => 'location',
                'Location' => 'purchase_cost',
                'Company' => 'order_number',
                'Order Number' => 'company',
                'Category' => 'manufacturer',
                'Manufacturer' => 'category',
                'Supplier' => 'min_amt',
                'Notes' => 'purchase_date',
                'Min QTY' => 'supplier',
                'Model Number' => 'item_name',
                'Quantity' => 'quantity',
            ],
        ])->assertOk();

        $newAccessory = Accessory::query()
            ->with(['location', 'category', 'manufacturer'])
            ->where('name', $row['modelNumber'])
            ->sole();

        // purchase_date, purchase_cost, and supplier moved off the
        // parent to the Orders / OrderItems polymorphic pair —
        // recordOrderForImportedRow persists them there. Assertions
        // walk through orderItems.order for those three fields.
        $this->assertEquals($row['modelNumber'], $newAccessory->name);
        $this->assertEquals($row['itemName'], $newAccessory->model_number);
        $this->assertEquals($row['quantity'], $newAccessory->qty);

        $orderItem = $newAccessory->orderItems()->firstOrFail();
        $this->assertEquals($row['notes'], $orderItem->order->purchase_date->toDateString());
        $this->assertEquals($row['location'], (float) $orderItem->price);
        // See the import_accessory test above for why order_number now
        // lives on Orders / OrderItems rather than the parent column.
        // Note this custom-mapping test intentionally maps companyName
        // to the orderNumber CSV column, verifying that whatever value
        // that column carries lands on the OrderItem's Order regardless
        // of what the source column was called.
        $this->assertEquals($row['companyName'], $orderItem->order->order_number);
        $this->assertEquals($row['purchaseDate'], $newAccessory->notes);
        $this->assertEquals($row['manufacturerName'], $newAccessory->category->name);
        $this->assertEquals($row['category'], $newAccessory->manufacturer->name);
        $this->assertEquals($row['purchaseCost'], $newAccessory->location->name);
    }

    #[Test]
    public function accessory_import_checks_out_to_user_when_username_matches(): void
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['username' => 'accessorytarget']);

        // Hand-crafted CSV: needs the checkout columns
        // (checkout_class + username) alongside the standard accessory
        // columns, which the AccessoriesImportFileBuilder doesn't include.
        $csv = "Item Name,Category,Quantity,Company,Checkout Type,Username\n"
            .'CSV-Checked-Accessory,Cables,5,CSVCo,user,'.$target->username."\n";
        $filename = 'accessory-checkout-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = Import::factory()->accessory()->create(['file_path' => $filename]);

            $this->actingAsForApi($actor);
            $this->importFileResponse(['import' => $import->id])->assertOk();

            $accessory = Accessory::query()->where('name', 'CSV-Checked-Accessory')->sole();
            $this->assertEquals(5, $accessory->qty);

            $this->assertDatabaseHas('accessories_checkout', [
                'accessory_id' => $accessory->id,
                'assigned_to' => $target->id,
                'assigned_type' => User::class,
            ]);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }

    #[Test]
    public function accessory_import_without_checkout_columns_does_not_create_checkout(): void
    {
        $actor = User::factory()->superuser()->create();

        $csv = "Item Name,Category,Quantity,Company\n"
            ."CSV-Uncheckedout-Accessory,Cables,3,CSVCo\n";
        $filename = 'accessory-nocheckout-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = Import::factory()->accessory()->create(['file_path' => $filename]);

            $this->actingAsForApi($actor);
            $this->importFileResponse(['import' => $import->id])->assertOk();

            $accessory = Accessory::query()->where('name', 'CSV-Uncheckedout-Accessory')->sole();
            $this->assertDatabaseMissing('accessories_checkout', [
                'accessory_id' => $accessory->id,
            ]);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }
}
