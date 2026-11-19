<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('non superuser can upload maintenance file via ui route', function () {
    Storage::fake('local');

    $company = Company::factory()->create();

    $user = User::factory()
        ->editAssets()->forCompany($company)->create();

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

    expect($uploadedLog)->not->toBeNull();
    expect($uploadedLog->company_id)->toBe($company->id);
});

test('maintenance show page includes upload modal for user with file permission', function () {
    $company = Company::factory()->create();

    $user = User::factory()
        ->viewAssets()
        ->editAssets()->forCompany($company)->create();

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
});

test('user cannot view or upload files for maintenance in another company when fmcs enabled', function () {
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
});
