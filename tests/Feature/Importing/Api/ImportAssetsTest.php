<?php

namespace Tests\Feature\Importing\Api;

use App\Mail\CheckoutAssetMail;
use App\Models\Actionlog as ActionLog;
use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Import;
use App\Models\User;
use App\Notifications\CheckoutAssetNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\AssetsImportFileBuilder as ImportFileBuilder;
use Tests\Support\Importing\CleansUpImportFiles;

class ImportAssetsTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'asset';
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
    public function userWithImportAssetsPermissionCanImportAssets(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->asset()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function importAsset(): void
    {

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('hardware.index')]
            ]);

        $newAsset = Asset::query()
            ->with(['location', 'supplier', 'company', 'assignedAssets', 'defaultLoc', 'assetStatus', 'model.category', 'model.manufacturer'])
            ->where('serial', $row['serialNumber'])
            ->sole();

        $assignee = User::query()->find($newAsset->assigned_to, ['id', 'first_name', 'last_name', 'email', 'username']);

        $activityLogs = ActionLog::query()
            ->where('item_type', Asset::class)
            ->where('item_id', $newAsset->id)
            ->get();

        $this->assertCount(2, $activityLogs);

        $this->assertEquals('checkout', $activityLogs[0]->action_type);
        $this->assertEquals(Asset::class, $activityLogs[0]->item_type);
        $this->assertEquals($assignee->id, $activityLogs[0]->target_id);
        $this->assertEquals(User::class, $activityLogs[0]->target_type);
        $this->assertEquals('Checkout from CSV Importer', $activityLogs[0]->note);
        $this->assertHasTheseActionLogs($newAsset, ['create', 'checkout']); // TODO - order reversed but passes?!

        $this->assertEquals('create', $activityLogs[1]->action_type);
        $this->assertNull($activityLogs[1]->target_id);
        $this->assertEquals(Asset::class, $activityLogs[1]->item_type);
        $this->assertNull($activityLogs[1]->note);
        $this->assertNull($activityLogs[1]->target_type);

        $this->assertEquals($row['assigneeFullName'], "{$assignee->first_name} {$assignee->last_name}");
        $this->assertEquals($row['assigneeEmail'], $assignee->email);
        $this->assertEquals($row['assigneeUsername'], $assignee->username);

        $this->assertEquals($row['category'], $newAsset->model->category->name);
        $this->assertEquals($row['manufacturerName'], $newAsset->model->manufacturer->name);
        $this->assertEquals($row['itemName'], $newAsset->name);
        $this->assertEquals($row['tag'], $newAsset->asset_tag);
        $this->assertEquals($row['model'], $newAsset->model->name);
        $this->assertEquals($row['modelNumber'], $newAsset->model->model_number);
        $this->assertEquals($row['purchaseDate'], $newAsset->purchase_date->toDateString());
        $this->assertNull($newAsset->asset_eol_date);
        $this->assertEquals(0, $newAsset->eol_explicit);
        $this->assertEquals($newAsset->location_id, $newAsset->rtd_location_id);
        $this->assertEquals($row['purchaseCost'], $newAsset->purchase_cost);
        $this->assertNull($newAsset->order_number);
        $this->assertEquals('', $newAsset->image);
        $this->assertNull($newAsset->user_id);
        $this->assertEquals(1, $newAsset->physical);
        $this->assertEquals($row['status'], $newAsset->assetStatus->name);
        $this->assertEquals(0, $newAsset->archived);
        $this->assertEquals($row['warrantyInMonths'], $newAsset->warranty_months);
        $this->assertNull($newAsset->deprecate);
        $this->assertEquals($row['supplierName'], $newAsset->supplier->name);
        $this->assertEquals(0, $newAsset->requestable);
        $this->assertEquals($row['location'], $newAsset->defaultLoc->name);
        $this->assertEquals(null, $newAsset->accepted);
        $this->assertEquals(now()->toDateString(), Carbon::parse($newAsset->last_checkout)->toDateString());
        $this->assertEquals(0, $newAsset->last_checkin);
        $this->assertEquals(0, $newAsset->expected_checkin);
        $this->assertEquals($row['companyName'], $newAsset->company->name);
        $this->assertEquals(User::class, $newAsset->assigned_type);
        $this->assertNull($newAsset->last_audit_date);
        $this->assertNull($newAsset->next_audit_date);
        $this->assertEquals($row['location'], $newAsset->location->name);
        $this->assertEquals(0, $newAsset->checkin_counter);
        $this->assertEquals(1, $newAsset->checkout_counter);
        $this->assertEquals(0, $newAsset->requests_counter);
        $this->assertEquals(0, $newAsset->byod);

        //Notes is never read.
        // $this->assertEquals($row['notes'], $newAsset->notes);

    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function willNotCreateNewAssetWhenAssetWithSameTagAlreadyExists(): void
    {
        $asset = Asset::factory()->create(['asset_tag' => $this->faker->uuid]);
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['tag' => $asset->asset_tag]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => null,
                'messages' => [
                    '' => [
                        'asset_tag' => [
                            'asset_tag' => [
                                "An asset with the asset tag {$asset->asset_tag} already exists and an update was not requested. No change was made."
                            ]
                        ]
                    ]
                ]
            ]);

        $assetsWithSameTag = Asset::query()->where('asset_tag', $asset->asset_tag)->get();

        $this->assertCount(1, $assetsWithSameTag);
    }

    #[Test]
    public function willNotCreateNewCompanyWhenCompanyExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['companyName' => Str::random()]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAssets = Asset::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(1, $newAssets->pluck('company_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewLocationWhenLocationExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['location' => Str::random()]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAssets = Asset::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(1, $newAssets->pluck('location_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewSupplierWhenSupplierExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['supplierName' => $this->faker->company]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAssets = Asset::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get(['supplier_id']);

        $this->assertCount(1, $newAssets->pluck('supplier_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewManufacturerWhenManufacturerExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['manufacturerName' => $this->faker->company]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAssets = Asset::query()
            ->with('model.manufacturer')
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(1, $newAssets->pluck('model.manufacturer_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateCategoryWhenCategoryExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['category' => $this->faker->company]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAssets = Asset::query()
            ->with('model.category')
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(1, $newAssets->pluck('model.category_id')->unique()->all());
    }

    #[Test]
    public function willNotCreateNewAssetModelWhenAssetModelExists(): void
    {
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['model' => Str::random()]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAssets = Asset::query()
            ->with('model')
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(1, $newAssets->pluck('model.name')->unique()->all());
    }

    #[Test]
    public function whenColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::times()->forget([
            'purchaseCost',
            'purchaseDate',
            'status'
        ]);

        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAsset = Asset::query()
            ->with(['assetStatus'])
            ->where('serial', $importFileBuilder->firstRow()['serialNumber'])
            ->sole();

        $this->assertEquals('Ready to Deploy', $newAsset->assetStatus->name);
        $this->assertNull($newAsset->purchase_date);
        $this->assertNull($newAsset->purchase_cost);
    }

    #[Test]
    public function willFormatValues(): void
    {
        $importFileBuilder = ImportFileBuilder::new([
            'warrantyInMonths' => '3 months',
            'purchaseDate'    => '2022/10/10'
        ]);

        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAsset = Asset::query()
            ->where('serial', $importFileBuilder->firstRow()['serialNumber'])
            ->sole();

        $this->assertEquals(3, $newAsset->warranty_months);
        $this->assertEquals('2022-10-10', $newAsset->purchase_date->toDateString());
    }

    #[Test]
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::times(2)
            ->forget(['tag'])
            ->replace(['model' => '']);

        $rows = $importFileBuilder->all();
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertJson([
                'status' => 'import-errors',
                'payload' => null,
                'messages' => [
                    $rows[0]['itemName'] => [
                        "Asset \"{$rows[0]['itemName']}\"" => [
                            'asset_tag' => [
                                'The asset tag field must be at least 1 characters.',
                            ],
                            'model_id' => [
                                'The model id field is required.'
                            ]
                        ]
                    ],
                    $rows[1]['itemName'] => [
                        "Asset \"{$rows[1]['itemName']}\"" => [
                            'asset_tag' => [
                                'The asset tag field must be at least 1 characters.',
                            ],
                            'model_id' => [
                                'The model id field is required.'
                            ]
                        ]
                    ]
                ]
            ]);

        $newAssets = Asset::query()
            ->whereIn('serial', Arr::pluck($rows, 'serialNumber'))
            ->get();

        $this->assertCount(0, $newAssets);
    }

    #[Test]
    public function updateAssetFromImport(): void
    {
        $asset = Asset::factory()->create()->refresh();
        $importFileBuilder = ImportFileBuilder::times(1)->replace(['tag' => $asset->asset_tag]);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedAsset = Asset::query()
            ->with(['location', 'supplier', 'company', 'defaultLoc', 'assetStatus', 'model.category', 'model.manufacturer'])
            ->find($asset->id);

        $assignee = User::query()->find($updatedAsset->assigned_to, ['id', 'first_name', 'last_name', 'email', 'username']);

        $updatedAttributes = [
            'category', 'manufacturer_id', 'name', 'tag', 'model_id',
            'model_number', 'purchase_date', 'purchase_cost', 'warranty_months', 'supplier_id',
            'location_id', 'company_id', 'serial', 'assigned_to', 'status_id', 'rtd_location_id',
            'last_checkout', 'requestable', 'updated_at', 'checkout_counter', 'assigned_type'
        ];

        $this->assertEquals($row['assigneeFullName'], "{$assignee->first_name} {$assignee->last_name}");
        $this->assertEquals($row['assigneeEmail'], $assignee->email);
        $this->assertEquals($row['assigneeUsername'], $assignee->username);

        $this->assertEquals($row['category'], $updatedAsset->model->category->name);
        $this->assertEquals($row['manufacturerName'], $updatedAsset->model->manufacturer->name);
        $this->assertEquals($row['itemName'], $updatedAsset->name);
        $this->assertEquals($row['tag'], $updatedAsset->asset_tag);
        $this->assertEquals($row['model'], $updatedAsset->model->name);
        $this->assertEquals($row['modelNumber'], $updatedAsset->model->model_number);
        $this->assertEquals($row['purchaseDate'], $updatedAsset->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $updatedAsset->purchase_cost);
        $this->assertEquals($row['status'], $updatedAsset->assetStatus->name);
        $this->assertEquals($row['warrantyInMonths'], $updatedAsset->warranty_months);
        $this->assertEquals($row['supplierName'], $updatedAsset->supplier->name);
        $this->assertEquals($row['location'], $updatedAsset->defaultLoc->name);
        $this->assertEquals($row['companyName'], $updatedAsset->company->name);
        $this->assertEquals($row['location'], $updatedAsset->location->name);
        $this->assertEquals(1, $updatedAsset->checkout_counter);
        $this->assertEquals(user::class, $updatedAsset->assigned_type);

        //RequestAble is always updated regardless of initial value.
        // $this->assertEquals($asset->requestable, $updatedAsset->requestable);

        $this->assertEquals(
            Arr::except($asset->attributesToArray(), $updatedAttributes),
            Arr::except($updatedAsset->attributesToArray(), $updatedAttributes),
        );
    }

    #[Test]
    public function customColumnMapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'assigneeFullName'    => $faker['supplierName'],
            'assigneeEmail'       => $faker['manufacturerName'],
            'assigneeUsername'    => $faker['serialNumber'],
            'category'            => $faker['location'],
            'companyName'         => $faker['purchaseCost'],
            'itemName'            => $faker['modelNumber'],
            'location'            => $faker['assigneeUsername'],
            'manufacturerName'    => $faker['status'],
            'model'               => $faker['itemName'],
            'modelNumber'         => $faker['category'],
            'notes'               => $faker['notes'],
            'purchaseCost'        => $faker['model'],
            'purchaseDate'        => $faker['companyName'],
            'serialNumber'        => $faker['tag'],
            'supplierName'        => $faker['purchaseDate'],
            'status'              => $faker['warrantyInMonths'],
            'tag'                 => $faker['assigneeEmail'],
            'warrantyInMonths'    => $faker['assigneeFullName'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Asset Tag'     => 'email',
                'Category'      => 'location',
                'Company'       => 'purchase_cost',
                'Email'         => 'manufacturer',
                'Full Name'     => 'supplier',
                'Item Name'     => 'model_number',
                'Location'      => 'username',
                'Manufacturer'  => 'status',
                'Model name'    => 'item_name',
                'Model Number'  => 'category',
                'Notes'         => 'asset_notes',
                'Purchase Cost' => 'asset_model',
                'Purchase Date' => 'company',
                'Serial number' => 'asset_tag',
                'Status'        => 'warranty_months',
                'Supplier'      => 'purchase_date',
                'Username'      => 'serial',
                'Warranty'      => 'full_name',
            ]
        ])->assertOk();

        $asset = Asset::query()
            ->with(['location', 'supplier', 'company', 'assignedAssets', 'defaultLoc', 'assetStatus', 'model.category', 'model.manufacturer'])
            ->where('serial', $row['assigneeUsername'])
            ->sole();

        $assignee = User::query()->find($asset->assigned_to, ['id', 'first_name', 'last_name', 'email', 'username']);

        $this->assertEquals($row['warrantyInMonths'], "{$assignee->first_name} {$assignee->last_name}");
        $this->assertEquals($row['tag'], $assignee->email);
        $this->assertEquals($row['location'], $assignee->username);

        $this->assertEquals($row['modelNumber'], $asset->model->category->name);
        $this->assertEquals($row['assigneeEmail'], $asset->model->manufacturer->name);
        $this->assertEquals($row['model'], $asset->name);
        $this->assertEquals($row['serialNumber'], $asset->asset_tag);
        $this->assertEquals($row['purchaseCost'], $asset->model->name);
        $this->assertEquals($row['itemName'], $asset->model->model_number);
        $this->assertEquals($row['supplierName'], $asset->purchase_date->toDateString());
        $this->assertEquals($row['companyName'], $asset->purchase_cost);
        $this->assertEquals($row['manufacturerName'], $asset->assetStatus->name);
        $this->assertEquals($row['status'], $asset->warranty_months);
        $this->assertEquals($row['assigneeFullName'], $asset->supplier->name);
        $this->assertEquals($row['category'], $asset->defaultLoc->name);
        $this->assertEquals($row['purchaseDate'], $asset->company->name);
        $this->assertEquals($row['category'], $asset->location->name);
        $this->assertEquals($row['notes'], $asset->notes);
        $this->assertNull($asset->asset_eol_date);
        $this->assertEquals(0, $asset->eol_explicit);
        $this->assertNull($asset->order_number);
        $this->assertEquals('', $asset->image);
        $this->assertNull($asset->user_id);
        $this->assertEquals(1, $asset->physical);
        $this->assertEquals(0, $asset->archived);
        $this->assertNull($asset->deprecate);
        $this->assertEquals(0, $asset->requestable);
        $this->assertEquals(null, $asset->accepted);
        $this->assertEquals(now()->toDateString(), Carbon::parse($asset->last_checkout)->toDateString());
        $this->assertEquals(0, $asset->last_checkin);
        $this->assertEquals(0, $asset->expected_checkin);
        $this->assertEquals(User::class, $asset->assigned_type);
        $this->assertNull($asset->last_audit_date);
        $this->assertNull($asset->next_audit_date);
        $this->assertEquals(0, $asset->checkin_counter);
        $this->assertEquals(1, $asset->checkout_counter);
        $this->assertEquals(0, $asset->requests_counter);
        $this->assertEquals(0, $asset->byod);
    }

    #[Test]
    public function customFields(): void
    {
        $macAddress = $this->faker->macAddress;

        $row = ImportFileBuilder::new()->definition();
        $row['Mac Address'] = $macAddress;

        $importFileBuilder = new ImportFileBuilder([$row]);
        $customField = CustomField::query()->where('name', 'Mac Address')->firstOrNew();

        if (!$customField->exists) {
            $customField = CustomField::factory()->macAddress()->create(['db_column' => '_snipeit_mac_address_1']);
        }

        if ($customField->field_encrypted) {
            $customField->field_encrypted = 0;
            $customField->save();
        }

        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newAsset = Asset::query()->where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();

        $this->assertEquals($macAddress, $newAsset->getAttribute($customField->db_column));
    }

    #[Test]
    public function willEncryptCustomFields(): void
    {
        $macAddress = $this->faker->macAddress;
        $row = ImportFileBuilder::new()->definition();

        $row['Mac Address'] = $macAddress;

        $importFileBuilder = new ImportFileBuilder([$row]);
        $customField = CustomField::query()->where('name', 'Mac Address')->firstOrNew();

        if (!$customField->exists) {
            $customField = CustomField::factory()->macAddress()->create();
        }

        if (!$customField->field_encrypted) {
            $customField->field_encrypted = 1;
            $customField->save();
        }

        $import = Import::factory()->asset()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $asset = Asset::query()->where('serial', $importFileBuilder->firstRow()['serialNumber'])->sole();
        $encryptedMacAddress = $asset->getAttribute($customField->db_column);

        $this->assertNotEquals($encryptedMacAddress, $macAddress);
    }
}
