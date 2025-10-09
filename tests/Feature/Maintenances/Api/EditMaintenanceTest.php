<?php

namespace Tests\Feature\Maintenances\Api;

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
            ->get(route('maintenances.update', Maintenance::factory()->create()->id))
            ->assertOk();
    }


    public function testCanEditMaintenance()
    {
        Storage::fake('public');
        $actor = User::factory()->superuser()->create();
        $supplier = Supplier::factory()->create();
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($actor)
            ->followingRedirects()
            ->patch(route('maintenances.update',  $maintenance), [
                'name' => 'Test Maintenance',
                'supplier_id' => $supplier->id,
                'asset_maintenance_type' => 'Maintenance',
                'start_date' => '2021-01-01',
                'completion_date' => '2021-01-10',
                'is_warranty' => '1',
                'image' => UploadedFile::fake()->image('test_image.png'),
                'notes' => 'A note',
                'url' => 'https://snipeitapp.com',
            ])
            ->assertOk();

        $this->followRedirects($response)->assertSee('alert-success');

        $maintenance->refresh();
        // Assert file was stored...
        Storage::disk('public')->assertExists(app('maintenances_path').$maintenance->image);


        $this->assertDatabaseHas('maintenances', [
            'supplier_id' => $supplier->id,
            'asset_maintenance_type' => 'Maintenance',
            'name' => 'Test Maintenance',
            'is_warranty' => 1,
            'start_date' => '2021-01-01',
            'completion_date' => '2021-01-10',
            'asset_maintenance_time' => '9',
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
            'image' => $maintenance->image,
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create', 'update']);
    }
}
