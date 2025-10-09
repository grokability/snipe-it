<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditMaintenanceTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.edit', Maintenance::factory()->create()->id))
            ->assertOk();
    }

    public function testCanUpdateMaintenance()
    {
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $maintenance = Maintenance::factory()->create(['asset_id' => $asset]);
        $supplier = Supplier::factory()->create();

        $this->actingAs($actor)
            ->put(route('maintenances.update', $maintenance), [
                'name' => 'Test Maintenance',
                'asset_id' => $asset->id,
                'supplier_id' => $supplier->id,
                'asset_maintenance_type' => 'Maintenance',
                'start_date' => '2021-01-01',
                'completion_date' => '2021-01-10',
                'is_warranty' => 1,
                'image' => UploadedFile::fake()->image('test_image.png'),
                'cost' => '100.99',
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
            'cost' => '100.99',
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create', 'update']);
    }

}
