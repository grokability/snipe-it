<?php

namespace Tests\Feature\Departments\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DepartmentsIndexTest extends TestCase
{
    public function test_viewing_department_index_requires_authentication()
    {
        $this->getJson(route('api.departments.index'))->assertRedirect();
    }

    public function test_viewing_department_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.departments.index'))
            ->assertForbidden();
    }

    public function test_department_index_returns_expected_departments()
    {
        Department::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.departments.index', [
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
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_department_index_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $departmentA = Department::factory()->for($companyA)->create();
        $departmentB = Department::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewDepartments()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewDepartments()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.departments.index'))
            ->assertResponseContainsInRows($departmentA)
            ->assertResponseContainsInRows($departmentB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.departments.index'))
            ->assertResponseContainsInRows($departmentA)
            ->assertResponseContainsInRows($departmentB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.departments.index'))
            ->assertResponseContainsInRows($departmentA)
            ->assertResponseContainsInRows($departmentB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.departments.index'))
            ->assertResponseContainsInRows($departmentA)
            ->assertResponseContainsInRows($departmentB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.departments.index'))
            ->assertResponseContainsInRows($departmentA)
            ->assertResponseDoesNotContainInRows($departmentB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.departments.index'))
            ->assertResponseDoesNotContainInRows($departmentA)
            ->assertResponseContainsInRows($departmentB);
    }

    public function test_department_index_filters_all_supported_exact_fields()
    {
        $user = User::factory()->superuser()->create();
        $targetCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $targetManager = User::factory()->create();
        $otherManager = User::factory()->create();
        $targetLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $targetDepartment = Department::factory()->create([
            'name' => 'Target Department',
            'company_id' => $targetCompany->id,
            'manager_id' => $targetManager->id,
            'location_id' => $targetLocation->id,
            'tag_color' => '#AA11AA',
        ]);

        $otherDepartment = Department::factory()->create([
            'name' => 'Other Department',
            'company_id' => $otherCompany->id,
            'manager_id' => $otherManager->id,
            'location_id' => $otherLocation->id,
            'tag_color' => '#11AA11',
        ]);

        $filters = [
            'name' => 'Target Department',
            'company_id' => $targetCompany->id,
            'manager_id' => $targetManager->id,
            'location_id' => $targetLocation->id,
            'tag_color' => '#AA11AA',
        ];

        foreach ($filters as $filterKey => $filterValue) {
            $this->actingAsForApi($user)
                ->getJson(route('api.departments.index', [$filterKey => $filterValue]))
                ->assertOk()
                ->assertResponseContainsInRows($targetDepartment)
                ->assertResponseDoesNotContainInRows($otherDepartment);
        }
    }
}
