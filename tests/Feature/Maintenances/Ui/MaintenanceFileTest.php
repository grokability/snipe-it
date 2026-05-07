<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaintenanceFileTest extends TestCase
{
    public function test_non_superuser_can_upload_maintenance_file_via_ui_route()
    {
        Storage::fake('local');

        $company = Company::factory()->create();

        $user = User::factory()
            ->editAssets()
            ->create(['company_id' => $company->id]);

        $asset = Asset::factory()->create(['company_id' => $company->id]);

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('ui.files.store', ['object_type' => 'maintenances', 'id' => $maintenance->id]), [
                'file' => [UploadedFile::fake()->create('maintenance-test.pdf', 64)],
                'notes' => 'UI upload test',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $uploadedLog = Actionlog::query()
            ->where('action_type', 'uploaded')
            ->where('item_type', Maintenance::class)
            ->where('item_id', $maintenance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($uploadedLog);
        $this->assertSame($company->id, $uploadedLog->company_id);
    }

    public function test_maintenance_show_page_includes_upload_modal_for_user_with_file_permission()
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

        $this->actingAs($user)
            ->get(route('maintenances.show', $maintenance))
            ->assertOk()
            ->assertSee('id="uploadFileModal"', false)
            ->assertSee(route('ui.files.store', ['object_type' => 'maintenances', 'id' => $maintenance->id]), false);
    }

    public function test_user_cannot_view_or_upload_files_for_maintenance_in_another_company_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
        $maintenanceForCompanyB = Maintenance::factory()->create();
        $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

        $this->actingAs($userInCompanyA)
            ->get(route('maintenances.show', $maintenanceForCompanyB))
            ->assertRedirectToRoute('maintenances.index');

        $this->actingAs($userInCompanyA)
            ->post(route('ui.files.store', ['object_type' => 'maintenances', 'id' => $maintenanceForCompanyB->id]), [
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
