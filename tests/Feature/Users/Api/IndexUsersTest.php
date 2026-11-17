<?php

namespace Tests\Feature\Users\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexUsersTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.users.index'))
            ->assertForbidden();
    }

    public function test_returns_managed_users_count_correctly()
    {
        $manager = User::factory()->create(['first_name' => 'Manages Users']);
        User::factory()->create(['first_name' => 'Does Not Manage Users']);

        User::factory()->create(['manager_id' => $manager->id]);
        User::factory()->create(['manager_id' => $manager->id]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'manages_users_count' => 2,
            ]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json->has('rows', 1)
                ->where('rows.0.first_name', 'Manages Users')
                ->etc();
        });
    }

    public function test_returns_managed_locations_count_correctly()
    {
        $manager = User::factory()->create(['first_name' => 'Manages Locations']);
        User::factory()->create(['first_name' => 'Does Not Manage Locations']);

        Location::factory()->create(['manager_id' => $manager->id]);
        Location::factory()->create(['manager_id' => $manager->id]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'manages_locations_count' => 2,
            ]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json->has('rows', 1)
                ->where('rows.0.first_name', 'Manages Locations')
                ->etc();
        });
    }

    public function test_returns_assigned_maintenances_count_correctly()
    {
        // The withCount/has filter both target the new assignedMaintenances
        // morphMany. checked_out_to_* on a maintenance is populated by
        // MaintenanceObserver::creating() from the asset's assigned_to —
        // so seed via "asset checked out to user, then maintenance on
        // that asset" rather than passing checked_out_to_* directly.
        $busy = User::factory()->create(['first_name' => 'Busy Breaker']);
        User::factory()->create(['first_name' => 'Quiet User']);

        $busyAsset = Asset::factory()->assignedToUser($busy)->create();
        Maintenance::factory()->create(['asset_id' => $busyAsset->id]);
        Maintenance::factory()->create([
            'asset_id' => $busyAsset->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'assigned_maintenances_count' => 2,
            ]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json->has('rows', 1)
                ->where('rows.0.first_name', 'Busy Breaker')
                ->where('rows.0.assigned_maintenances_count', 2)
                ->etc();
        });
    }

    public function test_can_sort_users_index_by_assigned_maintenances_count()
    {
        $most = User::factory()->create();
        $some = User::factory()->create();

        $mostAsset = Asset::factory()->assignedToUser($most)->create();
        Maintenance::factory()->count(3)->create(['asset_id' => $mostAsset->id]);

        $someAsset = Asset::factory()->assignedToUser($some)->create();
        Maintenance::factory()->create(['asset_id' => $someAsset->id]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'sort' => 'assigned_maintenances_count',
                'order' => 'desc',
            ]))
            ->assertOk();

        // Filter to just the two users we created with maintenances; everyone
        // else seeded by factories has count=0 and their relative order is
        // not what we're testing here. Position of $most must precede $some.
        $orderedIds = collect($response->json('rows'))->pluck('id');
        $mostPos = $orderedIds->search($most->id);
        $somePos = $orderedIds->search($some->id);

        $this->assertNotFalse($mostPos, 'User with 3 maintenances should appear in the result');
        $this->assertNotFalse($somePos, 'User with 1 maintenance should appear in the result');
        $this->assertLessThan(
            $somePos,
            $mostPos,
            'User with 3 maintenances must sort before user with 1 when ordering desc by assigned_maintenances_count',
        );
    }

    public function test_gracefully_handles_malformed_filter()
    {
        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                // filter should be a json encoded array and not a string
                'filter' => 'email:an-email-address@example.com',
            ]))
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.filter')->etc();
            });
    }

    /**
     * FMCS + floaters on: a company-scoped, non-superuser caller sees
     * their own pivot companies AND null-company (floater) users, but
     * NOT users pivoted to a different company.
     *
     * Regression pin for support ticket 56305. Root cause was that
     * `orWhereDoesntHave('companies')` in the floater branch had the
     * companies-table CompanyableScope applied recursively to its
     * subquery, filtering the JOIN to the caller's own companies. A user
     * pivoted only to OUT-OF-SCOPE companies then looked pivot-less and
     * slipped through as an apparent floater. Fix reads the company_user
     * pivot directly. See the same pin at tests/Unit/CompanyScopingTest.php.
     */
    public function test_users_index_hides_other_companies_users_but_includes_floaters_for_company_scoped_caller()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $companyACaller = $companyA->users()->save(User::factory()->viewUsers()->make());
        $companyAPeer = $companyA->users()->save(User::factory()->make(['first_name' => 'PeerInA']));
        $companyBUser = $companyB->users()->save(User::factory()->make(['first_name' => 'UserInB']));
        $floater = User::factory()->withoutCompany()->create(['first_name' => 'FloaterUser']);

        $this->settings->enableFloaterMode();

        $rows = $this->actingAsForApi($companyACaller)
            ->getJson(route('api.users.index'))
            ->assertOk()
            ->json('rows');

        $visibleIds = collect($rows)->pluck('id')->all();

        $this->assertContains($companyACaller->id, $visibleIds);
        $this->assertContains($companyAPeer->id, $visibleIds);
        $this->assertContains($floater->id, $visibleIds, 'Floater user should be visible per docs.');
        $this->assertNotContains($companyBUser->id, $visibleIds, 'Company B user leaked to a company A caller.');
    }

    public function test_returns_result_via_filter()
    {

        User::factory()->count(3)->create(['first_name' => 'Awesome', 'last_name' => 'Admin', 'email' => 'awesome@example.org']);
        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'filter' => '{"first_name":"Awesome","last_name":"Admin","email":"awesome@example.org"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'filter' => '{"first_name":"Not Awesome"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());
    }

    public function test_end_date_within_filters_to_users_leaving_in_window(): void
    {
        // Regression test for issue #19809. The dashboard's offboarding
        // tile links to users.index?end_date_within=30, so the API has
        // to narrow by range on end_date rather than equality.
        $leavingSoon = User::factory()->create([
            'first_name' => 'Leaving',
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
        User::factory()->create([
            'first_name' => 'Leaving Later',
            'end_date' => now()->addDays(60)->format('Y-m-d'),
        ]);
        User::factory()->create([
            'first_name' => 'Staying',
            'end_date' => null,
        ]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', ['end_date_within' => 30]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) use ($leavingSoon) {
            $json->has('rows', 1)
                ->where('rows.0.id', $leavingSoon->id)
                ->etc();
        });
    }

    public function test_with_assignments_filters_to_users_holding_at_least_one_item(): void
    {
        // Issue #19809. The offboarding tile narrows to users who are
        // leaving AND still hold an assigned item, so the API filter
        // backs the same cut.
        $withAsset = User::factory()->create(['first_name' => 'Has Asset']);
        Asset::factory()->create([
            'assigned_to' => $withAsset->id,
            'assigned_type' => User::class,
        ]);
        User::factory()->create(['first_name' => 'Has Nothing']);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', ['with_assignments' => 1]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) use ($withAsset) {
            $json->has('rows', 1)
                ->where('rows.0.id', $withAsset->id)
                ->etc();
        });
    }
}
