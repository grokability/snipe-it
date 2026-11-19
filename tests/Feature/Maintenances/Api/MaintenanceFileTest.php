<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Swap the private and public disks for in-memory fakes so
    // uploaded files do not accumulate under storage/private_uploads
    // and public/uploads across test runs. Was missing here, so
    // every run of this file left real bytes on disk.
    Storage::fake('local');
    Storage::fake('public');
});

test('non superuser can upload and list maintenance files with assets edit permission', function () {
    $company = Company::factory()->create();

    $user = User::factory()
        ->editAssets()->forCompany($company)->create();

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

    expect($uploadedLog)->not->toBeNull();
    expect($uploadedLog->company_id)->toBe($company->id);

    $this->actingAsForApi($user)
        ->getJson(route('api.files.index', ['object_type' => 'maintenances', 'id' => $maintenance->id]))
        ->assertOk()
        ->assertJsonStructure(['rows', 'total'])
        ->assertJsonPath('total', 1);
});

test('user cannot list or upload files for maintenance in another company when fmcs enabled', function () {
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
});
