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
    public function testRequiresPermission()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.accessories.checkedout', $accessory))
            ->assertForbidden();
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
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

    public function testCanGetAccessoryCheckouts()
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

    public function testCanGetAccessoryCheckoutsWithOffsetAndLimitInQueryString()
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
}
