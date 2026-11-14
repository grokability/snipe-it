<?php

namespace Tests\Feature\Fmcs;

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Model-level FMCS rule coverage for GHSA-w4qp-gxf4-jhgg.
 *
 */
class FmcsModelRuleCoverageTest extends TestCase
{
    public function test_user_save_rejects_cross_company_manager_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->create();
        $target = User::factory()->forCompany($companyA)->create();
        $crossManager = User::factory()->forCompany($companyB)->create();

        $this->actingAs($actor);

        $target->manager_id = $crossManager->id;

        $this->assertFalse(
            $target->save(),
            'User save should reject a cross-company manager_id via the fmcs_manager rule on $rules.'
        );
        $this->assertArrayHasKey('manager_id', $target->getErrors()->toArray());
    }

    public function test_user_save_rejects_cross_company_department_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->create();
        $target = User::factory()->forCompany($companyA)->create();
        $crossDepartment = Department::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($actor);

        $target->department_id = $crossDepartment->id;

        $this->assertFalse(
            $target->save(),
            'User save should reject a cross-company department_id via the fmcs_department rule on $rules.'
        );
        $this->assertArrayHasKey('department_id', $target->getErrors()->toArray());
    }

    public function test_user_save_rejects_cross_company_location_id_under_scoped_locations(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->create();
        $target = User::factory()->forCompany($companyA)->create();
        $crossLocation = Location::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($actor);

        $target->location_id = $crossLocation->id;

        $this->assertFalse(
            $target->save(),
            'User save should reject a cross-company location_id via the fmcs_location rule on $rules.'
        );
        $this->assertArrayHasKey('location_id', $target->getErrors()->toArray());
    }

    public function test_user_save_allows_same_company_manager_department_location(): void
    {
        // Positive regression: the rules must not over-reject legitimate writes.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $company = Company::factory()->create();

        $actor = User::factory()->forCompany($company)->create();
        $target = User::factory()->forCompany($company)->create();
        $manager = User::factory()->forCompany($company)->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $location = Location::factory()->create(['company_id' => $company->id]);

        $this->actingAs($actor);

        $target->manager_id = $manager->id;
        $target->department_id = $department->id;
        $target->location_id = $location->id;

        $this->assertTrue($target->save(), 'User save should accept same-company references.');
    }

    public function test_location_save_rejects_cross_company_manager_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->create();
        $location = Location::factory()->create(['company_id' => $companyA->id]);
        $crossManager = User::factory()->forCompany($companyB)->create();

        $this->actingAs($actor);

        $location->manager_id = $crossManager->id;

        $this->assertFalse(
            $location->save(),
            'Location save should reject a cross-company manager_id via the fmcs_manager rule on $rules.'
        );
        $this->assertArrayHasKey('manager_id', $location->getErrors()->toArray());
    }

    public function test_department_save_rejects_cross_company_manager_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->create();
        $department = Department::factory()->create(['company_id' => $companyA->id]);
        $crossManager = User::factory()->forCompany($companyB)->create();

        $this->actingAs($actor);

        $department->manager_id = $crossManager->id;

        $this->assertFalse(
            $department->save(),
            'Department save should reject a cross-company manager_id via the fmcs_manager rule on $rules.'
        );
        $this->assertArrayHasKey('manager_id', $department->getErrors()->toArray());
    }

    public function test_department_save_rejects_cross_company_location_id_under_scoped_locations(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->forCompany($companyA)->create();
        $department = Department::factory()->create(['company_id' => $companyA->id]);
        $crossLocation = Location::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($actor);

        $department->location_id = $crossLocation->id;

        $this->assertFalse(
            $department->save(),
            'Department save should reject a cross-company location_id via the fmcs_location rule on $rules.'
        );
        $this->assertArrayHasKey('location_id', $department->getErrors()->toArray());
    }

    public function test_superuser_bypasses_all_three_rules(): void
    {
        // Superusers have full FMCS scope, so isCurrentUserHasAccess always
        // returns true for them. Rules must not inadvertently block them.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $target = User::factory()->forCompany($companyA)->create();
        $manager = User::factory()->forCompany($companyB)->create();
        $department = Department::factory()->create(['company_id' => $companyB->id]);
        $location = Location::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($superuser);

        $target->manager_id = $manager->id;
        $target->department_id = $department->id;
        $target->location_id = $location->id;

        $this->assertTrue($target->save(), 'Superuser must not be blocked by the actor-scope fmcs_* rules.');
    }

    public function test_cli_save_without_auth_allows_cross_company_references(): void
    {
        // CLI (LDAP sync, importer, seeders, queue workers) runs with no
        // authenticated actor, so there is no FMCS scope to compare
        // against. The CLI trust boundary authorizes those paths. Rules
        // must early-return true when auth()->check() is false, matching
        // the comment Notes on each validator.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $target = User::factory()->forCompany($companyA)->create();
        $manager = User::factory()->forCompany($companyB)->create();
        $department = Department::factory()->create(['company_id' => $companyB->id]);
        $location = Location::factory()->create(['company_id' => $companyB->id]);

        // No actingAs call. auth()->check() is false.
        $target->manager_id = $manager->id;
        $target->department_id = $department->id;
        $target->location_id = $location->id;

        $this->assertTrue(
            $target->save(),
            'CLI (no-auth) save should pass fmcs_* rules so LDAP sync and importers stay functional.'
        );
    }

    public function test_null_values_pass_all_three_rules(): void
    {
        // Clearing manager / department / location is a legitimate
        // workflow (user departs, department shuts down). The validators
        // early-return true on empty values so null writes always pass.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $company = Company::factory()->create();

        $actor = User::factory()->forCompany($company)->create();
        $target = User::factory()->forCompany($company)->create(['manager_id' => null, 'department_id' => null, 'location_id' => null]);

        $this->actingAs($actor);

        $target->manager_id = null;
        $target->department_id = null;
        $target->location_id = null;

        $this->assertTrue($target->save(), 'Null writes should pass the fmcs_* rules.');
    }
}
