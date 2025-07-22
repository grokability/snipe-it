<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexCompaniesTest extends TestCase
{

    public function testViewingCompanyIndexRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.companies.index'))
            ->assertForbidden();
    }

    public function testCompanyIndexReturnsExpectedSearchResults()
    {
        Company::factory()->count(10)->create();
        Company::factory()->create(['name' => 'My Test Company']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.companies.index', [
                    'search' => 'My Test Company',
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 1,
            ]);

    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {

        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewCompanies()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewCompanies()->make());

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.companies.index'))
            ->assertOk()
            ->assertResponseContainsInRows($companyA)
            ->assertResponseDoesNotContainInRows($companyB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.companies.index'))
            ->assertOk()
            ->assertResponseContainsInRows($companyB)
            ->assertResponseDoesNotContainInRows($companyA);

        $this->actingAsForApi($superUser)
            ->getJson(route('api.companies.index'))
            ->assertOk()
            ->assertResponseContainsInRows($companyA)
            ->assertResponseContainsInRows($companyB);
    }


}
