<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class IndexAccessoryCheckoutsTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.accessories.checkedout', $accessory))
            ->assertForbidden();
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $accessoryA = Accessory::factory()->for($companyA)->create();
        $accessoryB = Accessory::factory()->for($companyB)->create();

        $superuser = User::factory()->superuser()->create();
        $userInCompanyA = $companyA->users()->save(User::factory()->viewAccessories()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewAccessories()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.accessories.checkedout', $accessoryB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.accessories.checkedout', $accessoryA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superuser)
            ->getJson(route('api.accessories.checkedout', $accessoryA))
            ->assertOk();
    }

    public function test_can_get_accessory_checkouts()
    {
        [$userA, $userB] = User::factory()->count(2)->create();

        $accessory = Accessory::factory()->checkedOutToUsers([$userA, $userB])->create();

        $this->assertEquals(2, $accessory->checkouts()->count());

        $this->actingAsForApi(User::factory()->viewAccessories()->create())
            ->getJson(route('api.accessories.checkedout', $accessory))
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('rows.0.assigned_to.id', $userA->id)
            ->assertJsonPath('rows.1.assigned_to.id', $userB->id);
    }

    public function test_can_get_accessory_checkouts_with_offset_and_limit_in_query_string()
    {
        [$userA, $userB, $userC] = User::factory()->count(3)->create();

        $accessory = Accessory::factory()->checkedOutToUsers([$userA, $userB, $userC])->create();

        $actor = $this->actingAsForApi(User::factory()->viewAccessories()->create());

        $actor->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'limit' => 1]))
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('rows.0.assigned_to.id', $userA->id);

        $actor->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'limit' => 2, 'offset' => 1]))
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('rows.0.assigned_to.id', $userB->id)
            ->assertJsonPath('rows.1.assigned_to.id', $userC->id);
    }

    public function test_checkout_search_by_company_name_returns_matching_users()
    {
        $company = Company::factory()->create(['name' => 'Jedi Order']);
        $jedi = User::factory()->create();
        $company->users()->attach($jedi);
        $sith = User::factory()->create();

        $accessory = Accessory::factory()->checkedOutToUsers([$jedi, $sith])->create();

        $this->actingAsForApi(User::factory()->viewAccessories()->create())
            ->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'search' => 'Jedi Order']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.assigned_to.id', $jedi->id);
    }

    public function test_checkout_search_by_company_name_does_not_return_users_in_other_companies()
    {
        Company::factory()->create(['name' => 'Jedi Order']);
        $sith = User::factory()->create();

        $accessory = Accessory::factory()->checkedOutToUsers([$sith])->create();

        $this->actingAsForApi(User::factory()->viewAccessories()->create())
            ->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'search' => 'Jedi Order']))
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    /**
     * Security regression pin. Before the fix in UsersTransformer::transformUserCompact,
     * a caller with accessories.view but not users.view could read the
     * assignee's login handle (username), org relationships (companies),
     * and avatar hash through this endpoint. Denied fallback keeps id +
     * type + name (display_name) because someone with accessories.view
     * legitimately needs to know WHO has the accessory. Note: display_name
     * often equals "first_name last_name", so those values still surface
     * via `name` in the fallback. What the fallback actually protects
     * against is enumeration of usernames, company relationships, and
     * avatar hashes that could be used to correlate identity elsewhere.
     */
    public function test_does_not_leak_assignee_pii_when_caller_lacks_users_view(): void
    {
        $assignee = User::factory()->create([
            'first_name' => 'Hidden',
            'last_name' => 'Assignee',
            'username' => 'hidden-assignee',
        ]);
        $accessory = Accessory::factory()->checkedOutToUsers([$assignee])->create();

        // viewAccessories only. No viewUsers.
        $actor = User::factory()->viewAccessories()->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.accessories.checkedout', $accessory))
            ->assertOk()
            ->assertJsonPath('rows.0.assigned_to.id', $assignee->id)
            ->assertJsonPath('rows.0.assigned_to.type', 'user')
            // Display name IS present in the denied fallback - basic identity is fine.
            ->assertJsonPath('rows.0.assigned_to.name', $assignee->display_name);

        $assigned = $response->json('rows.0.assigned_to');
        // The keys that actually get stripped by the denied fallback.
        $this->assertArrayNotHasKey('username', $assigned, 'Login handle must not leak to a caller without users.view');
        $this->assertArrayNotHasKey('companies', $assigned, 'Company relationships must not leak');
        $this->assertArrayNotHasKey('image', $assigned, 'Avatar URL must not leak');
        $this->assertArrayNotHasKey('created_by', $assigned);
        $this->assertArrayNotHasKey('created_at', $assigned);
    }

    /**
     * Same fix, positive-path sanity: a caller who DOES have users.view
     * (in addition to accessories.view) still gets the full identity
     * block. Guards against my info-disclosure guard accidentally
     * stripping identity from authorized callers too.
     */
    public function test_returns_full_assignee_identity_when_caller_has_users_view(): void
    {
        $assignee = User::factory()->create([
            'first_name' => 'Visible',
            'last_name' => 'Assignee',
            'username' => 'visible-assignee',
        ]);
        $accessory = Accessory::factory()->checkedOutToUsers([$assignee])->create();

        $actor = User::factory()->viewAccessories()->viewUsers()->create();

        $this->actingAsForApi($actor)
            ->getJson(route('api.accessories.checkedout', $accessory))
            ->assertOk()
            ->assertJsonPath('rows.0.assigned_to.id', $assignee->id)
            ->assertJsonPath('rows.0.assigned_to.first_name', 'Visible')
            ->assertJsonPath('rows.0.assigned_to.username', 'visible-assignee');
    }
}
