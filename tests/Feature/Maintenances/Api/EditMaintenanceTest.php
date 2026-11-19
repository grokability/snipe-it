<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenances.update', Maintenance::factory()->create()->id))
        ->assertOk();
});

test('can edit maintenance', function () {
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
            'start_date' => '2021-01-01 00:00:00',
            'completion_date' => '2021-01-10 00:00:00',
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
        'start_date' => '2021-01-01 00:00:00',
        'expected_completion_date' => '2021-01-10 00:00:00',
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

    expect($updateLog)->not->toBeNull();
    expect($updateLog->log_meta)->not->toBeNull();
    expect(json_decode($updateLog->log_meta, true))->toHaveKey('name');
});

test('user cannot edit maintenance for another company when fmcs enabled', function () {
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
});

test('can update maintenance without changing asset id', function () {
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
        'item_id' => $asset->id,
        'item_type' => \App\Models\Asset::class,
        'name' => 'Updated Name',
    ]);
});

test('can update maintenance to another asset in same company', function () {
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
        'item_id' => $assetB->id,
        'item_type' => \App\Models\Asset::class,
    ]);
});

test('cannot reparent maintenance to asset in another company', function () {
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
        'item_id' => $assetA->id,
        'item_type' => \App\Models\Asset::class,
    ]);
});

test('cannot reparent maintenance via item id to asset in another company', function () {
    // Both `asset_id` (legacy alias) and `item_id` are fillable on the
    // Maintenance model. Gating only the legacy alias leaves the
    // polymorphic FK as an alternative FMCS-bypass route to the same
    // effect.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $user = $companyA->users()->save(User::factory()->editAssets()->make());
    $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
    $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
    $maintenance = Maintenance::factory()->create(['asset_id' => $assetA->id]);

    $this->actingAsForApi($user)
        ->patchJson(route('api.maintenances.update', $maintenance), [
            'name' => 'Cross-company reparent attempt via item_id',
            'item_id' => $assetB->id,
            'start_date' => '2024-01-01',
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'item_id' => $assetA->id,
        'item_type' => \App\Models\Asset::class,
    ]);
});

test('can reparent maintenance via item id within same company', function () {
    // Happy path for the polymorphic FK: same-company re-parenting via
    // item_id should still succeed. Guards against a fix that would
    // reject item_id outright.
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $user = $company->users()->save(User::factory()->editAssets()->make());
    $assetA = Asset::factory()->create(['company_id' => $company->id]);
    $assetB = Asset::factory()->create(['company_id' => $company->id]);
    $maintenance = Maintenance::factory()->create(['asset_id' => $assetA->id]);

    $this->actingAsForApi($user)
        ->patchJson(route('api.maintenances.update', $maintenance), [
            'name' => 'Moved via item_id',
            'item_id' => $assetB->id,
            'start_date' => '2024-01-01',
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'item_id' => $assetB->id,
        'item_type' => \App\Models\Asset::class,
    ]);
});

test('cannot set item type to non asset class', function () {
    // Maintenances only support Asset-scoped attachments today, so any
    // other item_type gets rejected rather than persisted.
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $user = $company->users()->save(User::factory()->editAssets()->make());
    $asset = Asset::factory()->create(['company_id' => $company->id]);
    $maintenance = Maintenance::factory()->create(['asset_id' => $asset->id]);

    $this->actingAsForApi($user)
        ->patchJson(route('api.maintenances.update', $maintenance), [
            'item_id' => $user->id,
            'item_type' => User::class,
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseHas('maintenances', [
        'id' => $maintenance->id,
        'item_id' => $asset->id,
        'item_type' => \App\Models\Asset::class,
    ]);
});
