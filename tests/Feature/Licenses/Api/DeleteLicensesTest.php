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
    public function testRequiresPermission()
    {
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.licenses.destroy', $license))
            ->assertForbidden();

        $this->assertNotSoftDeleted($license);
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
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

    public function testLicenseCannotBeDeletedIfStillAssigned()
    {
        $license = License::factory()->create(['seats' => 2]);
        $license->freeSeat()->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->deleteLicenses()->create())
            ->deleteJson(route('api.licenses.destroy', $license))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($license);
    }

    public function testCanDeleteLicense()
    {
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->deleteLicenses()->create())
            ->deleteJson(route('api.licenses.destroy', $license))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($license);
    }

    public function testLicenseSeatsAreDeletedWhenLicenseIsDeleted()
    {
        $license = License::factory()->create(['seats' => 2]);

        $this->assertTrue($license->fresh()->licenseseats->isNotEmpty(), 'License seats not created like expected');

        $this->actingAsForApi(User::factory()->deleteLicenses()->create())
            ->deleteJson(route('api.licenses.destroy', $license));

        $this->assertTrue($license->fresh()->licenseseats->isEmpty());
    }
}
