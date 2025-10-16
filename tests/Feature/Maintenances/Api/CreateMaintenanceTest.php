<?php

namespace Tests\Feature\Maintenances\Api;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateMaintenanceTest extends TestCase
{


    public function testRequiresPermissionToCreateMaintenance()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.maintenances.store'))
            ->assertForbidden();
    }
    public function testCanCreateMaintenance()
    {

        Storage::fake('public');
        $actor = User::factory()->superuser()->create();

        $asset = Asset::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'Test Maintenance',
                'asset_id' => $asset->id,
                'supplier_id' => $supplier->id,
                'asset_maintenance_type' => 'Maintenance',
                'start_date' => '2021-01-01',
                'completion_date' => '2021-01-10',
                'is_warranty' => '1',
                'cost' => '100.00',
                'url' => 'https://snipeitapp.com',
                'image' => UploadedFile::fake()->image('test_image.png'),
                'notes' => 'A note',
            ])
            ->assertOk()
            ->assertStatus(200);

        // Since we rename the file in the ImageUploadRequest, we have to fetch the record from the database
        $maintenance = Maintenance::where('name', 'Test Maintenance')->first();

        // Assert file was stored...
        Storage::disk('public')->assertExists(app('maintenances_path').$maintenance->image);

        $this->assertDatabaseHas('maintenances', [
            'asset_id' => $asset->id,
            'supplier_id' => $supplier->id,
            'asset_maintenance_type' => 'Maintenance',
            'name' => 'Test Maintenance',
            'is_warranty' => 1,
            'start_date' => '2021-01-01',
            'completion_date' => '2021-01-10',
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
            'image' => $maintenance->image,
            'created_by' => $actor->id,
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create']);
    }



}
