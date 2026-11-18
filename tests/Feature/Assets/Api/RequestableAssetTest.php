<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

test('viewing requestable assets requires correct permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.assets.requestable'))
        ->assertForbidden();
});

test('returns requestable assets', function () {
    $requestableAsset = Asset::factory()->requestable()->create(['asset_tag' => 'requestable']);
    $nonRequestableAsset = Asset::factory()->nonrequestable()->create(['asset_tag' => 'non-requestable']);

    $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
        ->getJson(route('api.assets.requestable'))
        ->assertOk()
        ->assertResponseContainsInRows($requestableAsset, 'asset_tag')
        ->assertResponseDoesNotContainInRows($nonRequestableAsset, 'asset_tag');
});

test('requestable assets are scoped to company when multiple company support enabled', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->requestable()->for($companyA)->create(['asset_tag' => '0001']);
    $assetB = Asset::factory()->requestable()->for($companyB)->create(['asset_tag' => '0002']);

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->viewRequestableAssets()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewRequestableAssets()->make());

    $this->settings->disableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.assets.requestable'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.assets.requestable'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.assets.requestable'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.assets.requestable'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.assets.requestable'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseDoesNotContainInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.assets.requestable'))
        ->assertResponseDoesNotContainInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');
});
