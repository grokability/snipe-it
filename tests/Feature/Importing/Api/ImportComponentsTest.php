<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Actionlog as ActionLog;
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
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'component';
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
    public function userWithImportAssetsPermissionCanImportComponents(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->component()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function importComponents(): void
    {
        Notification::fake();

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('components.index')]
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
        $this->assertNull($newComponent->supplier_id);
        $this->assertEquals($row['quantity'], $newComponent->qty);
        $this->assertEquals($row['orderNumber'], $newComponent->order_number);
        $this->assertEquals($row['purchaseDate'], $newComponent->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $newComponent->purchase_cost);
        $this->assertNull($newComponent->min_amt);
        $this->assertEquals($row['serialNumber'], $newComponent->serial);
        $this->assertNull($newComponent->image);
        $this->assertNull($newComponent->notes);
    }

    #[Test]
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->firstRow();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function willNotCreateNewComponentWhenComponentWithNameAndSerialNumberExists(): void
    {
        $component = Component::factory()->create();

        $importFileBuilder = ImportFileBuilder::times(4)->replace([
            'itemName'     => $component->name,
            'serialNumber' => $component->serial
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
    public function willNotCreateNewCompanyWhenCompanyExists(): void
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
    public function willNotCreateNewLocationWhenLocationExists(): void
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
    public function willNotCreateNewCategoryWhenCategoryExists(): void
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
    public function whenRequiredColumnsAreMissingInImportFile(): void
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
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    $row['itemName'] => [
                        'Component' => [
                            'qty' => ['The qty field must be at least 1.'],
                            'category_id' => ['The category id field is required.']
                        ]
                    ]
                ]
            ]);

        $newComponents = Component::query()
            ->whereIn('serial', $importFileBuilder->pluck('serialNumber'))
            ->get();

        $this->assertCount(0, $newComponents);
    }

    #[Test]
    public function updateComponentFromImport(): void
    {
        $component = Component::factory()->create();
        $importFileBuilder = ImportFileBuilder::new([
            'itemName'     => $component->name,
            'serialNumber' => $component->serial
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
        $this->assertEquals($component->supplier_id, $updatedComponent->supplier_id);
        $this->assertEquals($row['quantity'], $updatedComponent->qty);
        $this->assertEquals($row['orderNumber'], $updatedComponent->order_number);
        $this->assertEquals($row['purchaseDate'], $updatedComponent->purchase_date->toDateString());
        $this->assertEquals($row['purchaseCost'], $updatedComponent->purchase_cost);
        $this->assertEquals($component->min_amt, $updatedComponent->min_amt);
        $this->assertEquals($row['serialNumber'], $updatedComponent->serial);
        $this->assertEquals($component->image, $updatedComponent->image);
        $this->assertEquals($component->notes, $updatedComponent->notes);
    }

    #[Test]
    public function customColumnMapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'category'     => $faker['serialNumber'],
            'companyName'  => $faker['quantity'],
            'itemName'     => $faker['purchaseDate'],
            'location'     => $faker['purchaseCost'],
            'orderNumber'  => $faker['orderNumber'],
            'purchaseCost' => $faker['category'],
            'purchaseDate' => $faker['companyName'],
            'quantity'     => $faker['itemName'],
            'serialNumber' => $faker['location']
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->component()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Category'      => 'serial',
                'Company'       => 'quantity',
                'item Name'     => 'purchase_date',
                'Location'      => 'purchase_cost',
                'Order Number'  => 'order_number',
                'Purchase Cost' => 'category',
                'Purchase Date' => 'company',
                'Quantity'      => 'item_name',
                'Serial number' => 'location',
            ]
        ])->assertOk();

        $newComponent = Component::query()
            ->with(['location', 'category'])
            ->where('serial', $importFileBuilder->firstRow()['category'])
            ->sole();

        $this->assertEquals($row['quantity'], $newComponent->name);
        $this->assertEquals($row['purchaseCost'], $newComponent->category->name);
        $this->assertEquals($row['serialNumber'], $newComponent->location->name);
        $this->assertNull($newComponent->supplier_id);
        $this->assertEquals($row['companyName'], $newComponent->qty);
        $this->assertEquals($row['orderNumber'], $newComponent->order_number);
        $this->assertEquals($row['itemName'], $newComponent->purchase_date->toDateString());
        $this->assertEquals($row['location'], $newComponent->purchase_cost);
        $this->assertNull($newComponent->min_amt);
        $this->assertNull($newComponent->image);
        $this->assertNull($newComponent->notes);
    }
}
