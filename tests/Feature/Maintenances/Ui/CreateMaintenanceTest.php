<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateMaintenanceTest extends TestCase
{
    public function test_page_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('maintenances.create'))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.create'))
            ->assertOk();
    }

    public function test_can_create_maintenance()
    {
        Storage::fake('public');
        Storage::fake('local');
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $supplier = Supplier::factory()->create();
        $type = MaintenanceType::factory()->create();

        $this->actingAs($actor)
            ->post(route('maintenances.store'), [
                'name' => 'Test Maintenance',
                'selected_assets' => [$asset->id],
                'supplier_id' => $supplier->id,
                'maintenance_type_id' => $type->id,
                'start_date' => '2021-01-01 00:00:00',
                'completion_date' => '2021-01-10 00:00:00',
                'is_warranty' => '1',
                'cost' => '100.00',
                'image' => UploadedFile::fake()->image('test_image.png'),
                'file' => [UploadedFile::fake()->create('maintenance.pdf', 64, 'application/pdf')],
                'notes' => 'A note',
                'url' => 'https://snipeitapp.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('maintenances.index'));

        // Since we rename the file in the ImageUploadRequest, we have to fetch the record from the database
        $maintenance = Maintenance::where('name', 'Test Maintenance')->first();

        // Assert file was stored...
        Storage::disk('public')->assertExists(app('maintenances_path').$maintenance->image);

        $uploadedLog = Actionlog::query()
            ->where('action_type', 'uploaded')
            ->where('item_type', Maintenance::class)
            ->where('item_id', $maintenance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($uploadedLog);
        Storage::disk('local')->assertExists('private_uploads/maintenances/'.$uploadedLog->filename);

        $this->assertDatabaseHas('maintenances', [
            'asset_id' => $asset->id,
            'supplier_id' => $supplier->id,
            'maintenance_type_id' => $type->id,
            'asset_maintenance_type' => $type->name,
            'name' => 'Test Maintenance',
            'is_warranty' => 1,
            'start_date' => '2021-01-01 00:00:00',
            'expected_completion_date' => '2021-01-10 00:00:00',
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
            'cost' => '100.00',
            'image' => $maintenance->image,
            'created_by' => $actor->id,
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create', 'uploaded']);
    }

    public function test_can_backfill_a_completed_maintenance_via_create()
    {
        // Users record maintenances that were already finished (backfill).
        // Setting completed_at on create should: (a) persist the value,
        // (b) stamp completed_by, (c) compute asset_maintenance_time from
        // start_date → completed_at, and (d) fire the MaintenanceComplete
        // action log alongside the create log.
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $type = MaintenanceType::factory()->create();

        $this->actingAs($actor)
            ->post(route('maintenances.store'), [
                'name' => 'Backfilled Maintenance',
                'selected_assets' => [$asset->id],
                'maintenance_type_id' => $type->id,
                'start_date' => '2021-01-01 00:00:00',
                'completed_at' => '2021-01-08 00:00:00',
                'cost' => '0.00',
                'notes' => 'Backfilled after the fact',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('maintenances.index'));

        $maintenance = Maintenance::where('name', 'Backfilled Maintenance')->firstOrFail();

        $this->assertEquals('2021-01-08 00:00:00', $maintenance->completed_at->format('Y-m-d H:i:s'));
        $this->assertEquals($actor->id, $maintenance->completed_by);
        $this->assertEquals(7, $maintenance->asset_maintenance_time, 'Duration should be start_date → completed_at, not created_at → completed_at (created_at is now, not the historical timeline)');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Maintenance::class,
            'item_id' => $maintenance->id,
            'action_type' => 'completed',
            'created_by' => $actor->id,
        ]);
    }

    public function test_creating_without_completed_at_leaves_maintenance_active()
    {
        // Sanity guard: the default create flow (no completed_at) must
        // not accidentally stamp anything. Snipe called this out — a
        // datetimepicker that defaults to now() would silently mark
        // freshly-created maintenances complete.
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $type = MaintenanceType::factory()->create();

        $this->actingAs($actor)
            ->post(route('maintenances.store'), [
                'name' => 'Active Maintenance',
                'selected_assets' => [$asset->id],
                'maintenance_type_id' => $type->id,
                'start_date' => '2021-01-01 00:00:00',
            ])
            ->assertSessionHasNoErrors();

        $maintenance = Maintenance::where('name', 'Active Maintenance')->firstOrFail();

        $this->assertNull($maintenance->completed_at);
        $this->assertNull($maintenance->completed_by);
        $this->assertNull($maintenance->asset_maintenance_time);
        $this->assertDatabaseMissing('action_logs', [
            'item_type' => Maintenance::class,
            'item_id' => $maintenance->id,
            'action_type' => 'completed',
        ]);
    }
}
