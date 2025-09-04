<?php

namespace Tests\Feature\Maintenances\Api;

use App\Models\Maintenance;
use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteMaintenancesTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $maintenance = Maintenance::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.maintenances.destroy', $maintenance))
            ->assertForbidden();

        $this->assertNotSoftDeleted($maintenance);
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $maintenanceA = Maintenance::factory()->create();
        $maintenanceB = Maintenance::factory()->create();
        $maintenanceC = Maintenance::factory()->create();

        $maintenanceA->asset->update(['company_id' => $companyA->id]);
        $maintenanceB->asset->update(['company_id' => $companyB->id]);
        $maintenanceC->asset->update(['company_id' => $companyB->id]);

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->editAssets()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.maintenances.destroy', $maintenanceB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.maintenances.destroy', $maintenanceA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.maintenances.destroy', $maintenanceC))
            ->assertStatusMessageIs('success');

        $this->assertNotSoftDeleted($maintenanceA);
        $this->assertNotSoftDeleted($maintenanceB);
        $this->assertSoftDeleted($maintenanceC);
        $this->assertHasTheseActionLogs($maintenanceC, ['create', 'delete']);
    }

    public function testCanDeleteMaintenance()
    {
        $maintenance = Maintenance::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->deleteJson(route('api.maintenances.destroy', $maintenance))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($maintenance);

        $this->assertHasTheseActionLogs($maintenance, ['create', 'delete']);
    }
}
