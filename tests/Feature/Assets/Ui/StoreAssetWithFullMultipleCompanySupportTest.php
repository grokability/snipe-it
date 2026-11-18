<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;

uses(\Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting::class);

test('adheres to full multiple companies support scoping', function ($data) {
    ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAs($actor)
        ->post(route('hardware.store'), [
            'asset_tags' => ['1' => '1234'],
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->create()->id,
            'company_id' => $company->id,
        ]);

    $asset = Asset::where('asset_tag', '1234')->sole();

    $assertions($asset);
})->with('dataForFullMultipleCompanySupportTesting');

test('non admin with pivot only company membership can create asset in their company', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();

    // Create a user with NULL scalar company_id but a pivot membership in $company.
    // This mirrors users created/updated via the web UI, which writes only to the pivot.
    $user = User::factory()->createAssets()->withoutCompany()->create();
    $user->companies()->sync([$company->id]);

    $this->actingAs($user)
        ->post(route('hardware.store'), [
            'asset_tags' => ['1' => '5678'],
            'serials' => ['1' => null],
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            'company_id' => $company->id,
            'rtd_location_id' => $location->id,
            'redirect_option' => 'back',
        ])->assertSessionHasNoErrors();

    $asset = Asset::withoutGlobalScopes()->where('asset_tag', '5678')->first();

    expect($asset)->not->toBeNull('Asset was not created.');
    expect($asset->company_id)->toEqual($company->id, 'Asset should be assigned to the submitted company, not null.');
});

test('allows creating asset with scoped location', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();

    $admin = User::factory()->admin()->forCompany($company)->create();

    $this->actingAs($admin)
        ->post(route('hardware.store'), [
            'asset_tags' => ['1' => '1234'],
            'serials' => ['1' => null],
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $admin->id,
            'assigned_asset' => null,
            'notes' => null,
            'rtd_location_id' => $location->id,
            'name' => null,
            'warranty_months' => null,
            'expected_checkin' => null,
            'next_audit_date' => null,
            'order_number' => null,
            'purchase_date' => null,
            'asset_eol_date' => null,
            'purchase_cost' => null,
            'redirect_option' => 'back',
        ])->assertSessionHasNoErrors();

    $asset = Asset::where(['asset_tag' => '1234'])->first();

    if (! $asset->exists()) {
        $this->fail('Asset was not created.');
    }

    expect($asset->rtd_location_id)->toEqual($location->id);
    expect($asset->assigned_to)->toEqual($admin->id);
});
