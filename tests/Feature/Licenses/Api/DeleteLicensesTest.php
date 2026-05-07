<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\Company;
use App\Models\License;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteLicensesTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.licenses.destroy', $license))
            ->assertForbidden();

        $this->assertNotSoftDeleted($license);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $licenseA = License::factory()->for($companyA)->create();
        $licenseB = License::factory()->for($companyB)->create();
        $licenseC = License::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteLicenses()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->deleteLicenses()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.licenses.destroy', $licenseB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.licenses.destroy', $licenseA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.licenses.destroy', $licenseC))
            ->assertStatusMessageIs('success');

        $this->assertNotSoftDeleted($licenseA);
        $this->assertNotSoftDeleted($licenseB);
        $this->assertSoftDeleted($licenseC);
    }

    public function test_license_cannot_be_deleted_if_still_assigned()
    {
        $license = License::factory()->create(['seats' => 2]);
        $license->freeSeat()->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->deleteLicenses()->create())
            ->deleteJson(route('api.licenses.destroy', $license))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($license);
    }

    public function test_can_delete_license()
    {
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->deleteLicenses()->create())
            ->deleteJson(route('api.licenses.destroy', $license))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($license);
    }

    public function test_license_seats_are_deleted_when_license_is_deleted()
    {
        $license = License::factory()->create(['seats' => 2]);

        $this->assertTrue($license->fresh()->licenseseats->isNotEmpty(), 'License seats not created like expected');

        $this->actingAsForApi(User::factory()->deleteLicenses()->create())
            ->deleteJson(route('api.licenses.destroy', $license));

        $this->assertTrue($license->fresh()->licenseseats->isEmpty());
    }
}
