<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Actionlog as ActionLog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\ComponentsImportFileBuilder as ImportFileBuilder;

class ImportComponentsTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'component';
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
    public function user_with_import_assets_permission_can_import_components(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->component()->withStubFile()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function import_components(): void
    {
        Notification::fake();

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload' => ['tally' => ['created' => 1, 'updated' => 0, 'skipped' => 0, 'errored' => 0]],
                'status' => 'success',
                'messages' => ['redirect_url' => route('components.index')],
            ]);

        $newComponent = Component::query()
            ->with(['location', 'category', 'company'])
            ->where('name', $row['itemName'])
            ->sole();

        $activityLog = ActionLog::query()
            ->where('item_type', Component::class)
            ->where('item_id', $newComponent->id)
            ->sole();

        $this->assertEquals('create', $activityLog->action_type);
        $this->assertEquals('importer', $activityLog->action_source);
        $this->assertEquals($newComponent->company->id, $activityLog->company_id);

        $this->assertEquals($row['itemName'], $newComponent->name);
        $this->assertEquals($row['companyName'], $newComponent->company->name);
        $this->assertEquals($row['category'], $newComponent->category->name);
        $this->assertEquals($row['location'], $newComponent->location->name);
        $this->assertNull($newComponent->default_supplier_id);
        $this->assertEquals($row['quantity'], $newComponent->qty);
        // order_number / purchase_date / purchase_cost all live on the
        // Orders / OrderItems polymorphic pair now — the importer's
        // recordOrderForImportedRow helper writes them there.
        $orderItem = $newComponent->orderItems()->firstOrFail();
        $this->assertEquals($row['orderNumber'], $orderItem->order->order_number);
        $this->assertEquals($row['purchaseDate'], $orderItem->order->purchase_date->toDateString());
        $this->assertEquals((float) $row['purchaseCost'], (float) $orderItem->price);
        $this->assertNull($newComponent->min_amt);
        $this->assertEquals($row['serialNumber'], $newComponent->serial);
        $this->assertNull($newComponent->image);
        $this->assertEquals($row['notes'], $newComponent->notes);
    }

    #[Test]
    public function will_ignore_unknown_columns_when_file_contains_unknown_columns(): void
    {
        $row = ImportFileBuilder::new()->firstRow();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function will_not_create_new_component_when_component_with_name_and_serial_number_exists(): void
    {
        $component = Component::factory()->create();

        $importFileBuilder = ImportFileBuilder::times(4)->replace([
            'itemName' => $component->name,
            'serialNumber' => $component->serial,
        ]);

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $probablyNewComponents = Component::query()
            ->where('name', $component->name)
            ->where('serial', $component->serial)
            ->get(['id']);

        $this->assertCount(1, $probablyNewComponents);
        $this->assertEquals($component->id, $probablyNewComponents->sole()->id);
    }

    #[Test]
    public function will_not_create_new_company_when_company_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['companyName' => Str::random()]);
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponents = Component::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['company_id']);

        $this->assertCount(1, $newComponents->pluck('company_id')->unique()->all());
    }

    #[Test]
    public function will_not_create_new_location_when_location_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['location' => Str::random()]);
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponents = Component::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['location_id']);

        $this->assertCount(1, $newComponents->pluck('location_id')->unique()->all());
    }

    #[Test]
    public function will_not_create_new_category_when_category_exists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['category' => $this->faker->company]);
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponents = Component::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['category_id']);

        $this->assertCount(1, $newComponents->pluck('category_id')->unique()->all());
    }

    #[Test]
    public function when_required_columns_are_missing_in_import_file(): void
    {
        $importFileBuilder = ImportFileBuilder::new()
            ->replace(['category' => ''])
            ->forget(['quantity']);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => ['tally' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errored' => 1]],
                'messages' => [
                    $row['itemName'] => [
                        'Component' => [
                            'qty' => ['The qty field is required.'],
                            'category_id' => ['The category id field is required.'],
                        ],
                    ],
                ],
            ]);

        $newComponents = Component::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(0, $newComponents);
    }

    #[Test]
    public function update_component_from_import(): void
    {
        $component = Component::factory()->create();
        $importFileBuilder = ImportFileBuilder::new([
            'itemName' => $component->name,
            'serialNumber' => $component->serial,
        ]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedComponent = Component::query()
            ->with(['location', 'category'])
            ->where('serial', $row['serialNumber'])
            ->sole();

        $this->assertEquals($row['itemName'], $updatedComponent->name);
        $this->assertEquals($row['category'], $updatedComponent->category->name);
        $this->assertEquals($row['location'], $updatedComponent->location->name);
        $this->assertEquals($row['quantity'], $updatedComponent->qty);
        // Update mode does NOT rewrite historical Orders. purchase_cost
        // maps to the parent's default_purchase_cost template;
        // purchase_date has no parent equivalent and is dropped on
        // update.
        $this->assertEquals((float) $row['purchaseCost'], (float) $updatedComponent->default_purchase_cost);
        $this->assertEquals($component->min_amt, $updatedComponent->min_amt);
        $this->assertEquals($row['serialNumber'], $updatedComponent->serial);
        $this->assertEquals($component->image, $updatedComponent->image);
        // notes IS present in the CSV (see ComponentsImportFileBuilder
        // definition), so update mode overwrites the seeded value.
        $this->assertEquals($row['notes'], $updatedComponent->notes);
    }

    #[Test]
    public function update_mode_clears_field_when_csv_column_is_present_but_empty(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        // notes is the parent-column proxy for the generic
        // "empty CSV cell clears the DB column" behavior — see the
        // consumables import tests for the full rationale.
        $component = Component::factory()->create([
            'notes' => 'seeded note',
        ])->refresh();

        $this->assertEquals('seeded note', $component->notes);

        $row = ImportFileBuilder::new()->definition();
        $row['itemName'] = $component->name;
        $row['serialNumber'] = $component->serial;
        $row['notes'] = '';

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->component()->create([
            'file_path' => $importFileBuilder->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $import->id,
            'import-update' => true,
        ])->assertOk();

        $component->refresh();
        $this->assertNull($component->notes);
    }

    #[Test]
    public function update_mode_preserves_fields_when_csv_column_is_absent(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $component = Component::factory()->create([
            'notes' => 'seeded note',
        ])->refresh();

        $originalNotes = $component->notes;

        // Import a CSV that only has the identity fields (name+serial) plus
        // quantity (required by Component validation). All other Component
        // fields are absent from the CSV, so their DB values must be preserved.
        // notes is the proxy — see the sibling test above.
        $partialFile = new ImportFileBuilder([[
            'itemName' => $component->name,
            'serialNumber' => $component->serial,
            'quantity' => 42,
        ]]);
        $partialImport = Import::factory()->component()->create([
            'file_path' => $partialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $partialImport->id,
            'import-update' => true,
        ])->assertOk();

        $component->refresh();
        $this->assertEquals(42, $component->qty);
        $this->assertEquals($originalNotes, $component->notes);
    }

    #[Test]
    public function import_persists_the_requestable_flag_from_csv(): void
    {
        // Component gained the Requestable trait + a per-row column.
        // Pins that a CSV Requestable=TRUE on create lands as `1` in
        // the DB, matching the shape the accessory / consumable
        // importers ship.
        $this->actingAsForApi(User::factory()->superuser()->create());

        // Overriding via `new()` merges into the seeded definition
        // (unlike PHP's `+` union operator which keeps the definition's
        // empty-string default and silently drops the override).
        $importFileBuilder = ImportFileBuilder::new(['requestable' => 'TRUE']);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->component()->create([
            'file_path' => $importFileBuilder->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $import->id])->assertOk();

        $component = Component::query()
            ->where('name', $row['itemName'])
            ->sole();
        $this->assertTrue((bool) $component->requestable);
    }

    #[Test]
    public function import_persists_the_archived_flag_from_csv(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $importFileBuilder = ImportFileBuilder::new(['archived' => 'TRUE']);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->component()->create([
            'file_path' => $importFileBuilder->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $import->id])->assertOk();

        $component = Component::query()
            ->where('name', $row['itemName'])
            ->sole();
        $this->assertTrue((bool) $component->archived);
    }

    #[Test]
    public function update_mode_logs_component_update_in_actionlog(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();

        $initialImport = Import::factory()->component()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $component = Component::query()
            ->where('name', $initialRow['itemName'])
            ->where('serial', $initialRow['serialNumber'])
            ->sole();

        // Change `purchaseCost` (a plain fillable column that IS in the
        // ComponentsImportFileBuilder shape). orderNumber is intentionally
        // NOT the trigger because ItemImporter::applyUpdateWithQtyAdjust
        // strips order_number from the update payload, so a non-qty /
        // non-order-number diff is what proves the update-log path fires.
        $updatedRow = array_merge($initialRow, [
            'purchaseCost' => ((int) $initialRow['purchaseCost']) + 1,
        ]);

        $updateFile = new ImportFileBuilder([$updatedRow]);
        $updateImport = Import::factory()->component()->create([
            'file_path' => $updateFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $updateImport->id,
            'import-update' => true,
        ])->assertOk();

        $component->refresh();
        // Update path maps CSV purchase_cost to the parent's template
        // field (see update_component_from_import); historical Orders
        // aren't rewritten on update mode.
        $this->assertEquals((float) $updatedRow['purchaseCost'], (float) $component->default_purchase_cost);

        $updateLog = ActionLog::query()
            ->where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog, 'Expected an update action log entry after component importer update mode.');
    }

    #[Test]
    public function custom_column_mapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'category' => $faker['serialNumber'],
            'companyName' => $faker['quantity'],
            'itemName' => $faker['purchaseDate'],
            'location' => $faker['purchaseCost'],
            'orderNumber' => $faker['orderNumber'],
            'purchaseCost' => $faker['category'],
            'purchaseDate' => $faker['companyName'],
            'quantity' => $faker['itemName'],
            'serialNumber' => $faker['location'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Category' => 'serial',
                'Company' => 'quantity',
                'item Name' => 'purchase_date',
                'Location' => 'purchase_cost',
                'Order Number' => 'order_number',
                'Purchase Cost' => 'category',
                'Purchase Date' => 'company',
                'Quantity' => 'item_name',
                'Serial number' => 'location',
            ],
        ])->assertOk();

        $newComponent = Component::query()
            ->with(['location', 'category'])
            ->where('serial', $importFileBuilder->firstRow()['category'])
            ->sole();

        $this->assertEquals($row['quantity'], $newComponent->name);
        $this->assertEquals($row['purchaseCost'], $newComponent->category->name);
        $this->assertEquals($row['serialNumber'], $newComponent->location->name);
        $this->assertNull($newComponent->default_supplier_id);
        $this->assertEquals($row['companyName'], $newComponent->qty);
        // See the import_components test above for why order_number,
        // purchase_date, and purchase_cost live on Orders / OrderItems.
        $orderItem = $newComponent->orderItems()->firstOrFail();
        $this->assertEquals($row['orderNumber'], $orderItem->order->order_number);
        $this->assertEquals($row['itemName'], $orderItem->order->purchase_date->toDateString());
        $this->assertEquals((float) $row['location'], (float) $orderItem->price);
        $this->assertNull($newComponent->min_amt);
        $this->assertNull($newComponent->image);
        $this->assertNull($newComponent->notes);
    }

    #[Test]
    public function import_component_checkout_to_asset_is_blocked_when_fmcs_companies_differ(): void
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $asset = Asset::factory()->for($companyB)->create();
        $this->settings->enableMultipleFullCompanySupport();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $companyA->name,
            'assetTag' => $asset->asset_tag,
        ]);

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponent = Component::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $this->assertEquals(0, $newComponent->assets()->count(), 'Component should not be checked out when item and asset companies differ under FMCS');
    }

    #[Test]
    public function import_component_checkout_to_asset_is_allowed_when_fmcs_companies_match(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create();
        $this->settings->enableMultipleFullCompanySupport();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'assetTag' => $asset->asset_tag,
        ]);

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponent = Component::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $this->assertEquals(1, $newComponent->assets()->count(), 'Component should be checked out when companies match under FMCS');
    }

    #[Test]
    public function import_component_checkout_to_asset_is_blocked_when_floater_disabled_and_asset_has_no_company(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => null]);
        $this->settings->enableMultipleFullCompanySupport()->disableFloaterMode();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'assetTag' => $asset->asset_tag,
        ]);

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponent = Component::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $this->assertEquals(0, $newComponent->assets()->count(), 'Component should not be checked out to a no-company asset when floater mode is off');
    }

    #[Test]
    public function import_component_checkout_to_asset_is_allowed_when_floater_enabled_and_asset_has_no_company(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => null]);
        $this->settings->enableFloaterMode();

        $importFileBuilder = ImportFileBuilder::new([
            'companyName' => $company->name,
            'assetTag' => $asset->asset_tag,
        ]);

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newComponent = Component::where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $this->assertEquals(1, $newComponent->assets()->count(), 'Component should be checked out to a no-company asset when floater mode is on');
    }
}
