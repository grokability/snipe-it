<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class IndexAccessoryTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.accessories.index'))
            ->assertForbidden();
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $accessoryA = Accessory::factory()->for($companyA)->create(['name' => 'Accessory A']);
        $accessoryB = Accessory::factory()->for($companyB)->create(['name' => 'Accessory B']);
        $accessoryC = Accessory::factory()->for($companyB)->create(['name' => 'Accessory C']);

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewAccessories()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewAccessories()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.accessories.index'))
            ->assertOk()
            ->assertResponseContainsInRows($accessoryA)
            ->assertResponseDoesNotContainInRows($accessoryB)
            ->assertResponseDoesNotContainInRows($accessoryC);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.accessories.index'))
            ->assertOk()
            ->assertResponseDoesNotContainInRows($accessoryA)
            ->assertResponseContainsInRows($accessoryB)
            ->assertResponseContainsInRows($accessoryC);

        $this->actingAsForApi($superUser)
            ->getJson(route('api.accessories.index'))
            ->assertOk()
            ->assertResponseContainsInRows($accessoryA)
            ->assertResponseContainsInRows($accessoryB)
            ->assertResponseContainsInRows($accessoryC);
    }

    public function testCanGetAccessories()
    {
        $user = User::factory()->viewAccessories()->create();

        $accessoryA = Accessory::factory()->create(['name' => 'Accessory A']);
        $accessoryB = Accessory::factory()->create(['name' => 'Accessory B']);

        $this->actingAsForApi($user)
            ->getJson(route('api.accessories.index'))
            ->assertOk()
            ->assertResponseContainsInRows($accessoryA)
            ->assertResponseContainsInRows($accessoryB);
    }
}
