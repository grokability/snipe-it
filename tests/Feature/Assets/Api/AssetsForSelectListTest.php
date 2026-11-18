<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

test('requires view selectlists permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('assets.selectlist'))
        ->assertForbidden();
});

test('assets can be searched for by asset tag', function () {
    Asset::factory()->create(['asset_tag' => '0001']);
    Asset::factory()->create(['asset_tag' => '0002']);

    $response = $this->actingAsForApi(User::factory()->createAssets()->create())
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertOk();

    $results = collect($response->json('results'));

    expect($results->count())->toEqual(2);
    expect($results->pluck('text')->contains(fn ($text) => str_contains($text, '0001')))->toBeTrue();
    expect($results->pluck('text')->contains(fn ($text) => str_contains($text, '0002')))->toBeTrue();
});

test('assets are scoped to company when multiple company support enabled', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create(['asset_tag' => '0001']);
    $assetB = Asset::factory()->for($companyB)->create(['asset_tag' => '0002']);

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->createAssets()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->createAssets()->make());

    $this->settings->disableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseContainsInResults($assetB);

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseContainsInResults($assetB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseContainsInResults($assetB);

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseContainsInResults($assetB);

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseDoesNotContainInResults($assetB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('assets.selectlist', ['search' => '000']))
        ->assertResponseDoesNotContainInResults($assetA)
        ->assertResponseContainsInResults($assetB);
});

test('asset is excluded from selectlist when exclude id matches', function () {
    [$assetA, $assetB] = Asset::factory()->count(2)->create();

    $actor = User::factory()->createAssets()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('assets.selectlist', ['excludeId' => $assetA->id]))
        ->assertResponseDoesNotContainInResults($assetA)
        ->assertResponseContainsInResults($assetB);
});

test('assets are filtered by multiple comma separated company ids when full company support is enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

    $assetA = Asset::factory()->for($companyA)->create(['asset_tag' => 'A001']);
    $assetB = Asset::factory()->for($companyB)->create(['asset_tag' => 'B001']);
    $assetC = Asset::factory()->for($companyC)->create(['asset_tag' => 'C001']);

    // The companyId filter is intentionally bypassed for superusers (v8.6.3 regression fix),
    // so this test uses a non-superuser admin who is a member of all three companies — that
    // gives them visibility to every candidate asset, leaving the explicit companyId filter
    // as the only active narrowing.
    $actor = User::factory()->createAssets()->create();
    $companyA->users()->attach($actor);
    $companyB->users()->attach($actor);
    $companyC->users()->attach($actor);

    $this->actingAsForApi($actor)
        ->getJson(route('assets.selectlist', ['companyId' => $companyA->id.','.$companyB->id]))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseContainsInResults($assetB)
        ->assertResponseDoesNotContainInResults($assetC);
});

test('superuser bypasses company id filter on assets selectlist', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create(['asset_tag' => 'SUSEL-A1']);
    $assetB = Asset::factory()->for($companyB)->create(['asset_tag' => 'SUSEL-B1']);

    $superuser = User::factory()->superuser()->create();

    $this->actingAsForApi($superuser)
        ->getJson(route('assets.selectlist', ['companyId' => $companyA->id]))
        ->assertResponseContainsInResults($assetA)
        ->assertResponseContainsInResults($assetB);
});

test('floater assets appear in selectlist under floater mode when narrowed by company', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->enableFloaterMode();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $companyAsset = Asset::factory()->for($companyA)->create(['asset_tag' => 'FLOATER-CA']);
    $otherCompanyAsset = Asset::factory()->for($companyB)->create(['asset_tag' => 'FLOATER-CB']);
    $floaterAsset = Asset::factory()->create(['asset_tag' => 'FLOATER-NULL', 'company_id' => null]);

    $actor = User::factory()->createAssets()->forCompany($companyA->id)->create();

    $this->actingAsForApi($actor)
        ->getJson(route('assets.selectlist', ['companyId' => $companyA->id]))
        ->assertResponseContainsInResults($companyAsset)
        ->assertResponseContainsInResults($floaterAsset)
        ->assertResponseDoesNotContainInResults($otherCompanyAsset);
});

test('floater assets are hidden in selectlist under strict mode when narrowed by company', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    $companyA = Company::factory()->create();
    $companyAsset = Asset::factory()->for($companyA)->create(['asset_tag' => 'STRICT-CA']);
    $floaterAsset = Asset::factory()->create(['asset_tag' => 'STRICT-NULL', 'company_id' => null]);

    $actor = User::factory()->createAssets()->forCompany($companyA->id)->create();

    $this->actingAsForApi($actor)
        ->getJson(route('assets.selectlist', ['companyId' => $companyA->id]))
        ->assertResponseContainsInResults($companyAsset)
        ->assertResponseDoesNotContainInResults($floaterAsset);
});
