<?php

namespace Tests\Feature\Fmcs;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Cross-tenant tests for the advanced-search filter payload on Users and
 * Hardware under all three FMCS modes (strict, floater, location-scoped).
 *
 * Users is called out separately because User-to-Company resolves
 * through the company_user pivot rather than the companyable_id column
 * every other model uses.
 */
class AdvancedSearchFmcsScopingTest extends TestCase
{
    protected Company $companyA;

    protected Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();
    }

    /**
     * Build a non-superuser caller pinned to company A with the requested
     * view permissions. Mirrors HttpCrossCompanyLeakTest's helper so the
     * caller shape is identical across both suites.
     */
    private function callerScopedToCompanyA(array $viewPermissionKeys): User
    {
        $permissions = [];
        foreach ($viewPermissionKeys as $key) {
            $permissions[$key.'.view'] = '1';
        }

        return $this->companyA->users()->save(
            User::factory()->make(['permissions' => json_encode($permissions)])
        );
    }

    private function idsFromResponse(string $endpoint, array $query, User $caller): array
    {
        return collect(
            $this->actingAsForApi($caller)
                ->getJson($endpoint.'?'.http_build_query($query))
                ->assertOk()
                ->json('rows')
        )->pluck('id')->all();
    }

    // ----- Users -----

    public function test_users_location_filter_cannot_see_other_company_user_under_strict_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $location = Location::factory()->create();
        $this->companyB->users()->save(
            User::factory()->make(['location_id' => $location->id, 'first_name' => 'Hidden'])
        );
        $caller = $this->callerScopedToCompanyA(['users']);

        $ids = $this->idsFromResponse(route('api.users.index'), [
            'filter' => json_encode(['location' => (string) $location->id]),
        ], $caller);

        $this->assertNotContains(
            User::where('first_name', 'Hidden')->value('id'),
            $ids,
            'Company B user at the filtered location leaked to a company A caller under strict FMCS.'
        );
    }

    public function test_users_location_filter_cannot_see_other_company_user_under_floater_fmcs(): void
    {
        $this->settings->enableFloaterMode();

        $location = Location::factory()->create();
        $this->companyB->users()->save(
            User::factory()->make(['location_id' => $location->id, 'first_name' => 'Hidden'])
        );
        $caller = $this->callerScopedToCompanyA(['users']);

        $ids = $this->idsFromResponse(route('api.users.index'), [
            'filter' => json_encode(['location' => (string) $location->id]),
        ], $caller);

        $this->assertNotContains(
            User::where('first_name', 'Hidden')->value('id'),
            $ids,
            'Company B user at the filtered location leaked to a company A caller under floater FMCS.'
        );
    }

    public function test_users_companies_filter_cannot_name_other_company_under_strict_fmcs(): void
    {
        // The companies filter runs through the company_user pivot via
        // applyCustomFilter. A caller in company A asking for company B
        // explicitly should still see zero rows because the base pivot
        // scope limits the universe to users in company A.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $this->companyB->users()->save(User::factory()->make(['first_name' => 'Hidden']));
        $caller = $this->callerScopedToCompanyA(['users']);

        $ids = $this->idsFromResponse(route('api.users.index'), [
            'filter' => json_encode(['companies' => (string) $this->companyB->id]),
        ], $caller);

        $this->assertNotContains(
            User::where('first_name', 'Hidden')->value('id'),
            $ids,
            'Hostile companies=B filter leaked a company B user to a company A caller.'
        );
    }

    public function test_users_location_filter_cannot_see_cross_company_user_under_location_scoping(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->disableFloaterMode();

        $location = Location::factory()->create();
        $this->companyB->users()->save(
            User::factory()->make(['location_id' => $location->id, 'first_name' => 'Hidden'])
        );
        $caller = $this->callerScopedToCompanyA(['users']);

        $ids = $this->idsFromResponse(route('api.users.index'), [
            'filter' => json_encode(['location' => (string) $location->id]),
        ], $caller);

        $this->assertNotContains(
            User::where('first_name', 'Hidden')->value('id'),
            $ids,
            'Company B user leaked to a company A caller under location-scoped FMCS.'
        );
    }

    // ----- Hardware (Asset) -----

    public function test_hardware_company_filter_cannot_name_other_company_under_strict_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $hiddenAsset = Asset::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['assets']);

        $ids = $this->idsFromResponse(route('api.assets.index'), [
            'filter' => json_encode(['company' => (string) $this->companyB->id]),
        ], $caller);

        $this->assertNotContains(
            $hiddenAsset->id,
            $ids,
            'Hostile company=B filter leaked a company B asset to a company A caller under strict FMCS.'
        );
    }

    public function test_hardware_company_filter_cannot_name_other_company_under_floater_fmcs(): void
    {
        $this->settings->enableFloaterMode();

        $hiddenAsset = Asset::factory()->for($this->companyB)->create();
        $caller = $this->callerScopedToCompanyA(['assets']);

        $ids = $this->idsFromResponse(route('api.assets.index'), [
            'filter' => json_encode(['company' => (string) $this->companyB->id]),
        ], $caller);

        $this->assertNotContains(
            $hiddenAsset->id,
            $ids,
            'Hostile company=B filter leaked a company B asset to a company A caller under floater FMCS.'
        );
    }

    public function test_hardware_location_filter_cannot_see_other_company_asset_under_strict_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $location = Location::factory()->create();
        $hiddenAsset = Asset::factory()->for($this->companyB)->create(['location_id' => $location->id]);
        $caller = $this->callerScopedToCompanyA(['assets']);

        $ids = $this->idsFromResponse(route('api.assets.index'), [
            'filter' => json_encode(['location' => (string) $location->id]),
        ], $caller);

        $this->assertNotContains(
            $hiddenAsset->id,
            $ids,
            'Company B asset at the filtered location leaked to a company A caller under strict FMCS.'
        );
    }

    public function test_hardware_location_filter_cannot_see_cross_company_asset_under_location_scoping(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->settings->disableFloaterMode();

        $location = Location::factory()->create();
        $hiddenAsset = Asset::factory()->for($this->companyB)->create(['location_id' => $location->id]);
        $caller = $this->callerScopedToCompanyA(['assets']);

        $ids = $this->idsFromResponse(route('api.assets.index'), [
            'filter' => json_encode(['location' => (string) $location->id]),
        ], $caller);

        $this->assertNotContains(
            $hiddenAsset->id,
            $ids,
            'Company B asset leaked to a company A caller under location-scoped FMCS.'
        );
    }
}
