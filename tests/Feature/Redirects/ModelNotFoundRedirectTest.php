<?php

namespace Tests\Feature\Redirects;

use App\Models\User;
use Tests\TestCase;

class ModelNotFoundRedirectTest extends TestCase
{
    public function testHandlesAsset404()
    {
        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.checkout.create', 9999))
            ->assertRedirectToRoute('hardware.index');
    }

    public function testHandlesMaintenance404()
    {
        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('maintenances.show', 9999))
            ->assertRedirectToRoute('maintenances.index');
    }

    public function testHandlesAssetModel404()
    {
        $this->actingAs(User::factory()->viewAssetModels()->create())
            ->get(route('models.show', 9999))
            ->assertRedirectToRoute('models.index');
    }

    public function testHandlesLicenseSeat404()
    {
        $this->actingAs(User::factory()->viewLicenses()->create())
            ->get(route('licenses.checkin', 9999))
            ->assertRedirectToRoute('licenses.index');
    }

    public function testHandlesPredefinedKit404()
    {
        $this->actingAs(User::factory()->viewPredefinedKits()->create())
            ->get(route('kits.show', 9999))
            ->assertRedirectToRoute('kits.index');
    }

    public function testHandlesReportTemplate404()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('report-templates.show', 9999))
            ->assertRedirectToRoute('reports/custom');
    }
}
