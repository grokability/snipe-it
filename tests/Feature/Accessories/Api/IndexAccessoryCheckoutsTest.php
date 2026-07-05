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
}
