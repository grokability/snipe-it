<?php

namespace Tests\Feature\Redirects;

use App\Models\User;
use Tests\TestCase;

class ModelNotFoundRedirectTest extends TestCase
{
    public function test_handles_asset404()
    {
        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.checkout.create', 9999))
            ->assertRedirectToRoute('hardware.index');
    }

    public function test_handles_maintenance404()
    {
        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('maintenances.show', 9999))
            ->assertRedirectToRoute('maintenances.index');
    }

    public function test_handles_asset_model404()
    {
        $this->actingAs(User::factory()->viewAssetModels()->create())
            ->get(route('models.show', 9999))
            ->assertRedirectToRoute('models.index');
    }

    public function test_handles_license_seat404()
    {
        $this->actingAs(User::factory()->viewLicenses()->create())
            ->get(route('licenses.checkin', 9999))
            ->assertRedirectToRoute('licenses.index');
    }

    public function test_handles_predefined_kit404()
    {
        $this->actingAs(User::factory()->viewPredefinedKits()->create())
            ->get(route('kits.show', 9999))
            ->assertRedirectToRoute('kits.index');
    }

    public function test_handles_report_template404()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('report-templates.show', 9999))
            ->assertRedirectToRoute('reports/custom');
    }
}
