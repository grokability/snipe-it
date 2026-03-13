<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class RequestableAssetTest extends TestCase
{
    public function testViewingRequestableAssetsRequiresCorrectPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assets.requestable'))
            ->assertForbidden();
    }

    public function testReturnsRequestableAssets()
    {
        $requestableAsset = Asset::factory()->requestable()->create(['asset_tag' => 'requestable']);
        $nonRequestableAsset = Asset::factory()->nonrequestable()->create(['asset_tag' => 'non-requestable']);

        $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.assets.requestable'))
            ->assertOk()
            ->assertResponseContainsInRows($requestableAsset, 'asset_tag')
            ->assertResponseDoesNotContainInRows($nonRequestableAsset, 'asset_tag');
    }

    public function testRequestableAssetsAreScopedToCompanyWhenMultipleCompanySupportEnabled()
    {
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
    }
}
