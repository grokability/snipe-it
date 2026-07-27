<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class IndexCompaniesTest extends TestCase
{
    public function test_viewing_company_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.companies.index'))
            ->assertForbidden();
    }

    public function test_company_index_returns_expected_search_results()
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

    public function test_search_matches_parent_company_name()
    {
        // Companies table shows parent as a column. Before adding parent
        // to Company's $searchableRelations the search silently returned
        // nothing when typing a parent company's name.
        $actor = User::factory()->superuser()->create();

        $parent = Company::factory()->create(['name' => 'Umbrella Holdings LLC']);
        $child = Company::factory()->create(['parent_id' => $parent->id]);
        $standalone = Company::factory()->create();

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.companies.index', ['search' => 'Umbrella Holdings']))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($child->id, $ids, 'Child should match on parent-company name');
        $this->assertContains($parent->id, $ids, 'Parent should still match on its own name');
        $this->assertNotContains($standalone->id, $ids);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
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
