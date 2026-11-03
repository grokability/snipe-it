<?php

namespace Tests\Feature\Fmcs;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

/*
 * Regression coverage for GHSA-4jj6-58j6-qw5w. SaveUserRequest's manager_id
 * and department_id rules had exists:... but no FMCS scope check, so a
 * company-A editor could persist a victim's manager_id as a company-B
 * user or move the victim into a company-B department. Both outcomes
 * leaked cross-tenant data: foreign manager read access through the
 * Manager View feature on the first, department-roster PII on the second.
 */
class FmcsUserScopeValidatorTest extends TestCase
{
    public function test_editor_cannot_set_cross_company_manager_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->editUsers()->forCompany($companyA)->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $foreignManager = User::factory()->forCompany($companyB)->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $victim), [
                'first_name' => $victim->first_name,
                'last_name' => $victim->last_name,
                'username' => $victim->username,
                'company_ids' => [$companyA->id],
                'manager_id' => $foreignManager->id,
            ])
            ->assertStatusMessageIs('error');

        $victim->refresh();
        $this->assertNull($victim->manager_id, 'Cross-company manager_id must not persist.');
    }

    public function test_editor_cannot_set_cross_company_department_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->editUsers()->forCompany($companyA)->create();
        $victim = User::factory()->forCompany($companyA)->create();

        $foreignDepartment = Department::factory()->make();
        $foreignDepartment->company_id = $companyB->id;
        $foreignDepartment->setValidating(false);
        $foreignDepartment->save();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $victim), [
                'first_name' => $victim->first_name,
                'last_name' => $victim->last_name,
                'username' => $victim->username,
                'company_ids' => [$companyA->id],
                'department_id' => $foreignDepartment->id,
            ])
            ->assertStatusMessageIs('error');

        $victim->refresh();
        $this->assertNull($victim->department_id, 'Cross-company department_id must not persist.');
    }

    public function test_editor_can_set_same_company_manager_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = User::factory()->editUsers()->forCompany($company)->create();
        $victim = User::factory()->forCompany($company)->create();
        $sameCompanyManager = User::factory()->forCompany($company)->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $victim), [
                'first_name' => $victim->first_name,
                'last_name' => $victim->last_name,
                'username' => $victim->username,
                'company_ids' => [$company->id],
                'manager_id' => $sameCompanyManager->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertSame($sameCompanyManager->id, $victim->fresh()->manager_id);
    }

    public function test_editor_can_set_same_company_department_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = User::factory()->editUsers()->forCompany($company)->create();
        $victim = User::factory()->forCompany($company)->create();

        $sameCompanyDepartment = Department::factory()->make();
        $sameCompanyDepartment->company_id = $company->id;
        $sameCompanyDepartment->setValidating(false);
        $sameCompanyDepartment->save();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $victim), [
                'first_name' => $victim->first_name,
                'last_name' => $victim->last_name,
                'username' => $victim->username,
                'company_ids' => [$company->id],
                'department_id' => $sameCompanyDepartment->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertSame($sameCompanyDepartment->id, $victim->fresh()->department_id);
    }

    public function test_superuser_can_set_cross_company_manager_id(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->superuser()->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $foreignManager = User::factory()->forCompany($companyB)->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $victim), [
                'first_name' => $victim->first_name,
                'last_name' => $victim->last_name,
                'username' => $victim->username,
                'company_ids' => [$companyA->id],
                'manager_id' => $foreignManager->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertSame($foreignManager->id, $victim->fresh()->manager_id);
    }

    public function test_fmcs_off_allows_cross_company_manager_id(): void
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->editUsers()->forCompany($companyA)->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $foreignManager = User::factory()->forCompany($companyB)->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $victim), [
                'first_name' => $victim->first_name,
                'last_name' => $victim->last_name,
                'username' => $victim->username,
                'manager_id' => $foreignManager->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertSame($foreignManager->id, $victim->fresh()->manager_id);
    }
}
