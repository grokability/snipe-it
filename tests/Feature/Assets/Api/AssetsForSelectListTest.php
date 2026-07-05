<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class AssetsForSelectListTest extends TestCase
{
    public function test_requires_view_selectlists_permission(): void
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('assets.selectlist'))
            ->assertForbidden();
    }

    public function test_assets_can_be_searched_for_by_asset_tag()
    {
        Asset::factory()->create(['asset_tag' => '0001']);
        Asset::factory()->create(['asset_tag' => '0002']);

        $response = $this->actingAsForApi(User::factory()->createAssets()->create())
            ->getJson(route('assets.selectlist', ['search' => '000']))
            ->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->pluck('text')->contains(fn ($text) => str_contains($text, '0001')));
        $this->assertTrue($results->pluck('text')->contains(fn ($text) => str_contains($text, '0002')));
    }

    public function test_assets_are_scoped_to_company_when_multiple_company_support_enabled()
    {
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
    }

    public function test_asset_is_excluded_from_selectlist_when_exclude_id_matches()
    {
        [$assetA, $assetB] = Asset::factory()->count(2)->create();

        $actor = User::factory()->createAssets()->create();

        $this->actingAsForApi($actor)
            ->getJson(route('assets.selectlist', ['excludeId' => $assetA->id]))
            ->assertResponseDoesNotContainInResults($assetA)
            ->assertResponseContainsInResults($assetB);
    }

    public function test_assets_are_filtered_by_multiple_comma_separated_company_ids_when_full_company_support_is_enabled()
    {
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
    }

    /**
     * v8.6.3 regression fix: superusers must bypass the companyId filter on asset selectlists.
     *
     * AssetsController::selectlist now skips the companyId narrowing when the requester is a
     * superuser, matching pre-v8.6.3 behavior where a superuser could pick any asset for
     * linking regardless of which company the asset belonged to.
     */
    public function test_superuser_bypasses_company_id_filter_on_assets_selectlist()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->for($companyA)->create(['asset_tag' => 'SUSEL-A1']);
        $assetB = Asset::factory()->for($companyB)->create(['asset_tag' => 'SUSEL-B1']);

        $superuser = User::factory()->superuser()->create();

        $this->actingAsForApi($superuser)
            ->getJson(route('assets.selectlist', ['companyId' => $companyA->id]))
            ->assertResponseContainsInResults($assetA)
            ->assertResponseContainsInResults($assetB);
    }
}
