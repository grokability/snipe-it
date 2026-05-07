<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
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
            ->get(route('maintenances.edit', Maintenance::factory()->create()->id))
            ->assertOk();
    }

    public function test_can_update_maintenance()
    {
        Storage::fake('public');
        Storage::fake('local');

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
                'file' => [UploadedFile::fake()->create('maintenance-update.pdf', 64, 'application/pdf')],
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

        $this->assertHasTheseActionLogs($maintenance, ['create', 'update', 'uploaded']);

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

        $this->actingAs($userInCompanyA)
            ->get(route('maintenances.edit', $maintenanceForCompanyB))
            ->assertRedirectToRoute('maintenances.index');

        $this->actingAs($userInCompanyA)
            ->put(route('maintenances.update', $maintenanceForCompanyB), [
                'name' => 'Should Not Update',
                'asset_id' => $maintenanceForCompanyB->asset_id,
                'asset_maintenance_type' => $maintenanceForCompanyB->asset_maintenance_type,
                'start_date' => $maintenanceForCompanyB->start_date,
            ])
            ->assertRedirectToRoute('maintenances.index');

        $this->assertDatabaseMissing('maintenances', [
            'id' => $maintenanceForCompanyB->id,
            'name' => 'Should Not Update',
        ]);
    }
}
