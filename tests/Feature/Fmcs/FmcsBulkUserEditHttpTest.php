<?php

namespace Tests\Feature\Fmcs;

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * HTTP-level regression coverage for GHSA-w4qp-gxf4-jhgg path 1.
 */
class FmcsBulkUserEditHttpTest extends TestCase
{
    public function test_strict_fmcs_rejects_cross_company_manager_id_in_bulk_edit(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $editor = User::factory()->editUsers()->forCompany($companyA)->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $crossManager = User::factory()->forCompany($companyB)->create();

        $response = $this->actingAs($editor)
            ->from(route('users.index'))
            ->post(route('users/bulkeditsave'), [
                'ids' => [$victim->id],
                'manager_id' => $crossManager->id,
            ]);

        $response->assertSessionHasErrors('manager_id');

        $victim->refresh();
        $this->assertNotEquals(
            $crossManager->id,
            $victim->manager_id,
            'Bulk edit must not persist a cross-company manager_id.'
        );
    }

    public function test_strict_fmcs_rejects_cross_company_department_id_in_bulk_edit(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $editor = User::factory()->editUsers()->forCompany($companyA)->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $crossDepartment = Department::factory()->create(['company_id' => $companyB->id]);

        $response = $this->actingAs($editor)
            ->from(route('users.index'))
            ->post(route('users/bulkeditsave'), [
                'ids' => [$victim->id],
                'department_id' => $crossDepartment->id,
            ]);

        $response->assertSessionHasErrors('department_id');

        $victim->refresh();
        $this->assertNotEquals($crossDepartment->id, $victim->department_id);
    }

    public function test_scoped_locations_fmcs_rejects_cross_company_location_id_in_bulk_edit(): void
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $editor = User::factory()->editUsers()->forCompany($companyA)->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $crossLocation = Location::factory()->create(['company_id' => $companyB->id]);

        $response = $this->actingAs($editor)
            ->from(route('users.index'))
            ->post(route('users/bulkeditsave'), [
                'ids' => [$victim->id],
                'location_id' => $crossLocation->id,
            ]);

        $response->assertSessionHasErrors('location_id');

        $victim->refresh();
        $this->assertNotEquals($crossLocation->id, $victim->location_id);
    }

    public function test_same_company_bulk_edit_still_works(): void
    {
        // Positive regression: legitimate bulk edits with in-scope
        // references must continue to work. If the pre-flight validator
        // accidentally rejects same-company values, the controller
        // becomes unusable for its documented purpose.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $company = Company::factory()->create();

        $editor = User::factory()->editUsers()->forCompany($company)->create();
        $victim = User::factory()->forCompany($company)->create();
        $manager = User::factory()->forCompany($company)->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $location = Location::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($editor)
            ->from(route('users.index'))
            ->post(route('users/bulkeditsave'), [
                'ids' => [$victim->id],
                'manager_id' => $manager->id,
                'department_id' => $department->id,
                'location_id' => $location->id,
            ]);

        $response->assertSessionHasNoErrors();

        $victim->refresh();
        $this->assertEquals($manager->id, $victim->manager_id);
        $this->assertEquals($department->id, $victim->department_id);
        $this->assertEquals($location->id, $victim->location_id);
    }

    public function test_superuser_can_bulk_assign_cross_company_references(): void
    {
        // Superusers bypass FMCS. The fail-fast validator and the
        // model rules alike must let a superuser perform the cross-
        // company assignment that legitimately crosses tenants.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $victim = User::factory()->forCompany($companyA)->create();
        $manager = User::factory()->forCompany($companyB)->create();
        $department = Department::factory()->create(['company_id' => $companyB->id]);
        $location = Location::factory()->create(['company_id' => $companyB->id]);

        $response = $this->actingAs($superuser)
            ->from(route('users.index'))
            ->post(route('users/bulkeditsave'), [
                'ids' => [$victim->id],
                'manager_id' => $manager->id,
                'department_id' => $department->id,
                'location_id' => $location->id,
            ]);

        $response->assertSessionHasNoErrors();

        $victim->refresh();
        $this->assertEquals($manager->id, $victim->manager_id);
        $this->assertEquals($department->id, $victim->department_id);
        $this->assertEquals($location->id, $victim->location_id);
    }
}
