<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\TestCase;

class AccessoriesForSelectListTest extends TestCase implements TestsFullMultipleCompaniesSupport
{
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
            ->getJson(route('api.accessories.selectlist'))
            ->assertOk()
            ->assertJsonPath('total_count', 1)
            ->assertResponseContainsInResults($accessoryA)
            ->assertResponseDoesNotContainInResults($accessoryB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.accessories.selectlist'))
            ->assertOk()
            ->assertJsonPath('total_count', 1)
            ->assertResponseDoesNotContainInResults($accessoryA)
            ->assertResponseContainsInResults($accessoryB);

        $this->actingAsForApi($superuser)
            ->getJson(route('api.accessories.selectlist'))
            ->assertOk()
            ->assertJsonPath('total_count', 2)
            ->assertResponseContainsInResults($accessoryA)
            ->assertResponseContainsInResults($accessoryB);
    }

    public function testCanGetAccessoriesForSelectList()
    {
        [$accessoryA, $accessoryB] = Accessory::factory()->count(2)->create();

        $this->actingAsForApi(User::factory()->viewAccessories()->create())
            ->getJson(route('api.accessories.selectlist'))
            ->assertOk()
            ->assertJsonPath('total_count', 2)
            ->assertResponseContainsInResults($accessoryA)
            ->assertResponseContainsInResults($accessoryB);
    }
}
