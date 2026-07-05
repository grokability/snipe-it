<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifies FMCS scoping rules for the location index and selectlist endpoints.
 *
 * Rules under test:
 *  1. FMCS OFF  → all locations visible to any authorized user regardless of company
 *  2. FMCS ON, user has companies  → only locations whose company_id matches one of the user's companies
 *  3. FMCS ON, user has companies  → locations with NULL company_id are NOT visible
 *  4. FMCS ON, user has companies  → locations in OTHER companies are NOT visible
 *  5. FMCS ON, user has NO companies → only locations with NULL company_id are visible
 *  6. FMCS ON, user has NO companies → locations with a company_id are NOT visible
 *  7. scope_locations_fmcs does not change visibility; rules 2-6 hold with or without it
 */
class LocationsFmcsScopingTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function userInCompany(Company $company): User
    {
        $user = User::factory()->viewLocationHistory()->createUsers()->create();
        DB::table('company_user')->insert([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function userWithNoCompany(): User
    {
        return User::factory()->viewLocationHistory()->createUsers()->create(['company_id' => null]);
    }

    private function indexIds(User $user): array
    {
        return collect(
            $this->actingAsForApi($user)
                ->getJson(route('api.locations.index', ['limit' => 500]))
                ->assertOk()
                ->json('rows')
        )->pluck('id')->all();
    }

    private function selectlistIds(User $user): array
    {
        return collect(
            $this->actingAsForApi($user)
                ->getJson(route('api.locations.selectlist', ['limit' => 500]))
                ->assertOk()
                ->json('results')
        )->pluck('id')->all();
    }

    // -----------------------------------------------------------------------
    // FMCS OFF
    // -----------------------------------------------------------------------

    public function test_fmcs_off_user_sees_all_locations_on_index()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $locationA = Location::factory()->create(['company_id' => $companyA->id]);
        $locationB = Location::factory()->create(['company_id' => $companyB->id]);
        $locationNull = Location::factory()->create(['company_id' => null]);

        $user = $this->userInCompany($companyA);
        $ids = $this->indexIds($user);

        $this->assertContains($locationA->id, $ids, 'Own-company location should be visible');
        $this->assertContains($locationB->id, $ids, 'Other-company location should be visible when FMCS off');
        $this->assertContains($locationNull->id, $ids, 'Null-company location should be visible when FMCS off');
    }

    public function test_fmcs_off_user_sees_all_locations_on_selectlist()
    {
        $this->settings->disableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $locationA = Location::factory()->create(['company_id' => $companyA->id]);
        $locationB = Location::factory()->create(['company_id' => $companyB->id]);
        $locationNull = Location::factory()->create(['company_id' => null]);

        $user = $this->userInCompany($companyA);
        $ids = $this->selectlistIds($user);

        $this->assertContains($locationA->id, $ids, 'Own-company location should be in selectlist');
        $this->assertContains($locationB->id, $ids, 'Other-company location should be in selectlist when FMCS off');
        $this->assertContains($locationNull->id, $ids, 'Null-company location should be in selectlist when FMCS off');
    }

    // -----------------------------------------------------------------------
    // FMCS ON — user WITH companies
    // -----------------------------------------------------------------------

    public function test_fmcs_on_user_with_company_sees_own_company_location_on_index()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $user = $this->userInCompany($company);

        $this->assertContains($location->id, $this->indexIds($user),
            'Location in same company should be visible');
    }

    public function test_fmcs_on_user_with_company_cannot_see_other_company_location_on_index()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $locationB = Location::factory()->create(['company_id' => $companyB->id]);
        $user = $this->userInCompany($companyA);

        $this->assertNotContains($locationB->id, $this->indexIds($user),
            'Location in a different company should not be visible');
    }

    public function test_fmcs_on_user_with_company_cannot_see_null_company_location_on_index()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $locationNull = Location::factory()->create(['company_id' => null]);
        $user = $this->userInCompany($company);

        $this->assertNotContains($locationNull->id, $this->indexIds($user),
            'Location with no company should not be visible to company-scoped user');
    }

    public function test_fmcs_on_user_with_company_sees_own_company_location_on_selectlist()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $user = $this->userInCompany($company);

        $this->assertContains($location->id, $this->selectlistIds($user),
            'Location in same company should appear in selectlist');
    }

    public function test_fmcs_on_user_with_company_cannot_see_other_company_location_on_selectlist()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $locationB = Location::factory()->create(['company_id' => $companyB->id]);
        $user = $this->userInCompany($companyA);

        $this->assertNotContains($locationB->id, $this->selectlistIds($user),
            'Location in a different company should not appear in selectlist');
    }

    public function test_fmcs_on_user_with_company_cannot_see_null_company_location_on_selectlist()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $locationNull = Location::factory()->create(['company_id' => null]);
        $user = $this->userInCompany($company);

        $this->assertNotContains($locationNull->id, $this->selectlistIds($user),
            'Location with no company should not appear in selectlist for company-scoped user');
    }

    // -----------------------------------------------------------------------
    // FMCS ON — user with NO companies
    // -----------------------------------------------------------------------

    public function test_fmcs_on_user_with_no_company_sees_null_company_locations_on_index()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $locationNull = Location::factory()->create(['company_id' => null]);
        $user = $this->userWithNoCompany();

        $this->assertContains($locationNull->id, $this->indexIds($user),
            'Location with no company should be visible to user with no company');
    }

    public function test_fmcs_on_user_with_no_company_cannot_see_company_locations_on_index()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $user = $this->userWithNoCompany();

        $this->assertNotContains($location->id, $this->indexIds($user),
            'Location with a company should not be visible to user with no company');
    }

    public function test_fmcs_on_user_with_no_company_sees_null_company_locations_on_selectlist()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $locationNull = Location::factory()->create(['company_id' => null]);
        $user = $this->userWithNoCompany();

        $this->assertContains($locationNull->id, $this->selectlistIds($user),
            'Location with no company should appear in selectlist for user with no company');
    }

    public function test_fmcs_on_user_with_no_company_cannot_see_company_locations_on_selectlist()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $user = $this->userWithNoCompany();

        $this->assertNotContains($location->id, $this->selectlistIds($user),
            'Location with a company should not appear in selectlist for user with no company');
    }

    // -----------------------------------------------------------------------
    // scope_locations_fmcs does not change visibility rules
    // -----------------------------------------------------------------------

    public function test_scope_locations_fmcs_does_not_change_visibility_for_user_with_company()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $locationA = Location::factory()->create(['company_id' => $companyA->id]);
        $locationB = Location::factory()->create(['company_id' => $companyB->id]);
        $locationNull = Location::factory()->create(['company_id' => null]);

        $user = $this->userInCompany($companyA);
        $ids = $this->indexIds($user);

        $this->assertContains($locationA->id, $ids, 'Own-company location should still be visible');
        $this->assertNotContains($locationB->id, $ids, 'Other-company location should still be hidden');
        $this->assertNotContains($locationNull->id, $ids, 'Null-company location should still be hidden from company-scoped user');
    }

    public function test_scope_locations_fmcs_does_not_change_visibility_for_user_with_no_company()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $company = Company::factory()->create();
        $locationNull = Location::factory()->create(['company_id' => null]);
        $locationA = Location::factory()->create(['company_id' => $company->id]);

        $user = $this->userWithNoCompany();
        $ids = $this->indexIds($user);

        $this->assertContains($locationNull->id, $ids, 'Null-company location should still be visible to no-company user');
        $this->assertNotContains($locationA->id, $ids, 'Company location should still be hidden from no-company user');
    }
}
