<?php

namespace Tests\Feature\Departments\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DepartmentsIndexTest extends TestCase
{
    public function testViewingDepartmentIndexRequiresAuthentication()
    {
        $this->getJson(route('api.departments.index'))->assertRedirect();
    }

    public function testViewingDepartmentIndexRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.departments.index'))
            ->assertForbidden();
    }

    public function testDepartmentIndexReturnsExpectedDepartments()
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
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function testDepartmentIndexAdheresToCompanyScoping()
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
}
