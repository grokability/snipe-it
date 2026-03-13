<?php

namespace Tests\Feature\Components\Api;

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class ComponentIndexTest extends TestCase
{
    public function testComponentIndexAdheresToCompanyScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $componentA = Component::factory()->for($companyA)->create();
        $componentB = Component::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewComponents()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewComponents()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.components.index'))
            ->assertResponseContainsInRows($componentA)
            ->assertResponseContainsInRows($componentB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.components.index'))
            ->assertResponseContainsInRows($componentA)
            ->assertResponseContainsInRows($componentB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.components.index'))
            ->assertResponseContainsInRows($componentA)
            ->assertResponseContainsInRows($componentB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.components.index'))
            ->assertResponseContainsInRows($componentA)
            ->assertResponseContainsInRows($componentB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.components.index'))
            ->assertResponseContainsInRows($componentA)
            ->assertResponseDoesNotContainInRows($componentB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.components.index'))
            ->assertResponseDoesNotContainInRows($componentA)
            ->assertResponseContainsInRows($componentB);
    }
}
