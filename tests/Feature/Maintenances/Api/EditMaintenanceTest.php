<?php

namespace Tests\Feature\Maintenances\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditMaintenanceTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.update', Maintenance::factory()->create()->id))
            ->assertOk();
    }

    public function test_can_edit_maintenance()
    {
        Storage::fake('public');
        $actor = User::factory()->superuser()->create();
        $supplier = Supplier::factory()->create();
        $maintenance = Maintenance::factory()->create();
        $type = MaintenanceType::factory()->create();

        $response = $this->actingAs($actor)
            ->followingRedirects()
            ->patch(route('maintenances.update', $maintenance), [
                'name' => 'Test Maintenance',
                'supplier_id' => $supplier->id,
                'maintenance_type_id' => $type->id,
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
            'maintenance_type_id' => $type->id,
            'asset_maintenance_type' => $type->name,
            'name' => 'Test Maintenance',
            'is_warranty' => 1,
            'start_date' => '2021-01-01',
            'completion_date' => '2021-01-10',
            'notes' => 'A note',
            'url' => 'https://snipeitapp.com',
            'image' => $maintenance->image,
        ]);

        $this->assertHasTheseActionLogs($maintenance, ['create', 'update']);

        $updateLog = Actionlog::query()
            ->where('item_type', Maintenance::class)
            ->where('item_id', $maintenance->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertNotNull($updateLog->log_meta);
        $this->assertArrayHasKey('name', json_decode($updateLog->log_meta, true));
    }

    public function test_user_cannot_edit_maintenance_for_another_company_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
        $maintenanceForCompanyB = Maintenance::factory()->create();
        $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

        $this->actingAsForApi($userInCompanyA)
            ->putJson(route('api.maintenances.update', $maintenanceForCompanyB), [
                'name' => 'Should Not Update',
            ])
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('maintenances', [
            'id' => $maintenanceForCompanyB->id,
            'name' => 'Should Not Update',
        ]);
    }

    public function test_can_update_maintenance_without_changing_asset_id()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $user = $company->users()->save(User::factory()->editAssets()->make());
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $maintenance = Maintenance::factory()->create(['asset_id' => $asset->id]);

        $this->actingAsForApi($user)
            ->patchJson(route('api.maintenances.update', $maintenance), [
                'name' => 'Updated Name',
                'start_date' => '2024-01-01',
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('maintenances', [
            'id' => $maintenance->id,
            'asset_id' => $asset->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_update_maintenance_to_another_asset_in_same_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $user = $company->users()->save(User::factory()->editAssets()->make());
        $assetA = Asset::factory()->create(['company_id' => $company->id]);
        $assetB = Asset::factory()->create(['company_id' => $company->id]);
        $maintenance = Maintenance::factory()->create(['asset_id' => $assetA->id]);

        $this->actingAsForApi($user)
            ->patchJson(route('api.maintenances.update', $maintenance), [
                'name' => 'Moved Maintenance',
                'asset_id' => $assetB->id,
                'start_date' => '2024-01-01',
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('maintenances', [
            'id' => $maintenance->id,
            'asset_id' => $assetB->id,
        ]);
    }

    public function test_cannot_reparent_maintenance_to_asset_in_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = $companyA->users()->save(User::factory()->editAssets()->make());
        $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $maintenance = Maintenance::factory()->create(['asset_id' => $assetA->id]);

        $this->actingAsForApi($user)
            ->patchJson(route('api.maintenances.update', $maintenance), [
                'name' => 'Cross-company reparent attempt',
                'asset_id' => $assetB->id,
                'start_date' => '2024-01-01',
            ])
            ->assertStatusMessageIs('error');

        $this->assertDatabaseHas('maintenances', [
            'id' => $maintenance->id,
            'asset_id' => $assetA->id,
        ]);
    }
}
