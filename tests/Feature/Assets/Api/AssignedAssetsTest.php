<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.assets.assigned_assets', Asset::factory()->create()))
        ->assertForbidden();
});

test('adheres to company scoping', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $asset = Asset::factory()->for($companyA)->create();

    $user = User::factory()->forCompany($companyB)->viewAssets()->create();

    $this->actingAsForApi($user)
        ->getJson(route('api.assets.assigned_assets', $asset))
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre('Asset not found');
});

test('can get assets assigned to specific asset', function () {
    $unassociatedAsset = Asset::factory()->create();

    $asset = Asset::factory()->hasAssignedAssets(2)->create();

    $assetsAssignedToAsset = Asset::where([
        'assigned_to' => $asset->id,
        'assigned_type' => Asset::class,
    ])->get();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.assigned_assets', $asset))
        ->assertOk()
        ->assertResponseContainsInRows($assetsAssignedToAsset, 'serial')
        ->assertResponseDoesNotContainInRows($unassociatedAsset, 'serial')
        ->assertJson(function (AssertableJson $json) {
            $json->where('total', 2)
                ->count('rows', 2)
                ->etc();
        });
});

test('adheres to offset and limit', function () {
    $asset = Asset::factory()->hasAssignedAssets(2)->create();

    $assetsAssignedToAsset = Asset::where([
        'assigned_to' => $asset->id,
        'assigned_type' => Asset::class,
    ])->get();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.assigned_assets', [
            'asset' => $asset,
            'offset' => 1,
            'limit' => 1,
        ]))
        ->assertOk()
        ->assertResponseDoesNotContainInRows($assetsAssignedToAsset->first(), 'serial')
        ->assertResponseContainsInRows($assetsAssignedToAsset->last(), 'serial')
        ->assertJson(function (AssertableJson $json) {
            $json->where('total', 2)
                ->count('rows', 1)
                ->etc();
        });
});
