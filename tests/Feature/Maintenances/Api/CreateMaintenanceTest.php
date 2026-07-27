<?php

namespace Tests\Feature\Maintenances\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateMaintenanceTest extends TestCase
{
    public function test_requires_permission_to_create_maintenance()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.maintenances.store'))
            ->assertForbidden();
    }

    public function test_can_create_maintenance()
    {

        Storage::fake('public');
        $actor = User::factory()->superuser()->create();

        $asset = Asset::factory()->create();
        $supplier = Supplier::factory()->create();
        $type = MaintenanceType::factory()->create();

        $response = $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'Test Maintenance',
                'asset_id' => $asset->id,
                'supplier_id' => $supplier->id,
                'maintenance_type_id' => $type->id,
                'start_date' => '2021-01-01 00:00:00',
                'completion_date' => '2021-01-10 00:00:00',
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
            'maintenance_type_id' => $type->id,
            'asset_maintenance_type' => $type->name,
            'name' => 'Test Maintenance',
            'is_warranty' => 1,
            'start_date' => '2021-01-01 00:00:00',
            'expected_completion_date' => '2021-01-10 00:00:00',
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
            'image' => $maintenance->image,
            'created_by' => $actor->id,
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create']);
    }

    public function test_bulk_create_creates_one_maintenance_per_asset()
    {
        $actor = User::factory()->superuser()->create();
        $assets = Asset::factory()->count(3)->create();
        $type = MaintenanceType::factory()->create();

        $response = $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'Bulk Test',
                'asset_ids' => $assets->pluck('id')->toArray(),
                'maintenance_type_id' => $type->id,
                'start_date' => '2026-01-01',
                'is_warranty' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('payload.total', 3);

        foreach ($assets as $asset) {
            $this->assertDatabaseHas('maintenances', [
                'name' => 'Bulk Test',
                'asset_id' => $asset->id,
                'maintenance_type_id' => $type->id,
            ]);
        }
    }

    public function test_bulk_create_skips_inaccessible_assets_under_fmcs()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAssets()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $ownAsset = Asset::factory()->create(['company_id' => $companyA->id]);
        $otherAsset = Asset::factory()->create(['company_id' => $companyB->id]);
        $type = MaintenanceType::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'FMCS Bulk Test',
                'asset_ids' => [$ownAsset->id, $otherAsset->id],
                'maintenance_type_id' => $type->id,
                'start_date' => '2026-01-01',
                'is_warranty' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('payload.total', 1);

        $this->assertDatabaseHas('maintenances', ['asset_id' => $ownAsset->id, 'name' => 'FMCS Bulk Test']);
        $this->assertDatabaseMissing('maintenances', ['asset_id' => $otherAsset->id, 'name' => 'FMCS Bulk Test']);
    }

    public function test_bulk_create_returns_error_when_all_assets_inaccessible()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAssets()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $otherAsset = Asset::factory()->create(['company_id' => $companyB->id]);
        $type = MaintenanceType::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'All Denied Test',
                'asset_ids' => [$otherAsset->id],
                'maintenance_type_id' => $type->id,
                'start_date' => '2026-01-01',
                'is_warranty' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'error');
    }

    public function test_legacy_completion_date_fieldname_still_works_on_create()
    {
        // API v1 back-compat: the DB column was renamed from
        // completion_date to expected_completion_date, but old API
        // consumers still POST the legacy fieldname. The model's
        // setCompletionDateAttribute mutator (kept fillable) routes it
        // to the new column.
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $type = MaintenanceType::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'Legacy Field Test',
                'asset_id' => $asset->id,
                'maintenance_type_id' => $type->id,
                'start_date' => '2021-01-01 00:00:00',
                'completion_date' => '2021-01-15 00:00:00',
                'is_warranty' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('maintenances', [
            'name' => 'Legacy Field Test',
            'expected_completion_date' => '2021-01-15 00:00:00',
        ]);
    }

    public function test_new_expected_completion_date_fieldname_works_on_create()
    {
        // Companion assertion: the new-name write path is honored too.
        $actor = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $type = MaintenanceType::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.store'), [
                'name' => 'New Field Test',
                'asset_id' => $asset->id,
                'maintenance_type_id' => $type->id,
                'start_date' => '2021-01-01 00:00:00',
                'expected_completion_date' => '2021-01-20 00:00:00',
                'is_warranty' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('maintenances', [
            'name' => 'New Field Test',
            'expected_completion_date' => '2021-01-20 00:00:00',
        ]);
    }
}
