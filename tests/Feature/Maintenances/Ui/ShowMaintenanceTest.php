<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

class ShowMaintenanceTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.show', Maintenance::factory()->create()->id))
            ->assertOk();
    }

    public function test_page_renders_history_tab_and_history_table()
    {
        $maintenance = Maintenance::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.show', $maintenance))
            ->assertOk()
            ->assertSee(trans('general.history'))
            ->assertSee(route('api.maintenances.history', $maintenance), false);
    }

    public function test_user_cannot_view_maintenance_for_another_company_when_fmcs_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
        $maintenanceForCompanyB = Maintenance::factory()->create();
        $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

        $this->actingAs($userInCompanyA)
            ->get(route('maintenances.show', $maintenanceForCompanyB))
            ->assertRedirectToRoute('maintenances.index');
    }
}
