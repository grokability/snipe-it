<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateMaintenanceTest extends TestCase
{
    public function testPageRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('maintenances.create'))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.create'))
            ->assertOk();
    }


    public function testCanCreateMaintenance()
    {
        Storage::fake('public');
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($actor)
            ->post(route('maintenances.store'), [
                'name' => 'Test Maintenance',
                'selected_assets' => [$asset->id],
                'supplier_id' => $supplier->id,
                'asset_maintenance_type' => 'Maintenance',
                'start_date' => '2021-01-01',
                'completion_date' => '2021-01-10',
                'is_warranty' => '1',
                'cost' => '100.00',
                'image' => UploadedFile::fake()->image('test_image.png'),
                'notes' => 'A note',
                'url' => 'https://snipeitapp.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('maintenances.index'));

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
            'asset_maintenance_time' => '9',
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
            'cost' => '100.00',
            'image' => $maintenance->image,
            'created_by' => $actor->id,
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create']);
    }
}
