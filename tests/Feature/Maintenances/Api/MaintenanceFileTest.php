<?php

namespace Tests\Feature\Maintenances\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MaintenanceFileTest extends TestCase
{
    public function test_non_superuser_can_upload_and_list_maintenance_files_with_assets_edit_permission()
    {
        $company = Company::factory()->create();

        $user = User::factory()
            ->editAssets()
            ->create(['company_id' => $company->id]);

        $asset = Asset::factory()->create(['company_id' => $company->id]);

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'created_by' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->post(route('api.files.store', ['object_type' => 'maintenances', 'id' => $maintenance->id]), [
                'file' => [UploadedFile::fake()->create('maintenance-test.pdf', 64)],
            ])
            ->assertOk();

        $uploadedLog = Actionlog::query()
            ->where('action_type', 'uploaded')
            ->where('item_type', Maintenance::class)
            ->where('item_id', $maintenance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($uploadedLog);
        $this->assertSame($company->id, $uploadedLog->company_id);

        $this->actingAsForApi($user)
            ->getJson(route('api.files.index', ['object_type' => 'maintenances', 'id' => $maintenance->id]))
            ->assertOk()
            ->assertJsonStructure(['rows', 'total'])
            ->assertJsonPath('total', 1);
    }

    public function test_user_cannot_list_or_upload_files_for_maintenance_in_another_company_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
        $maintenanceForCompanyB = Maintenance::factory()->create();
        $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.files.index', ['object_type' => 'maintenances', 'id' => $maintenanceForCompanyB->id]))
            ->assertForbidden();

        $this->actingAsForApi($userInCompanyA)
            ->post(route('api.files.store', ['object_type' => 'maintenances', 'id' => $maintenanceForCompanyB->id]), [
                'file' => [UploadedFile::fake()->create('cross-company.pdf', 64)],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('action_logs', [
            'action_type' => 'uploaded',
            'item_type' => Maintenance::class,
            'item_id' => $maintenanceForCompanyB->id,
        ]);
    }
}
