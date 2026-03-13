<?php

namespace Tests\Feature\Licenses\Ui;

use App\Models\License;
use App\Models\Depreciation;
use App\Models\User;
use Tests\TestCase;

class LicenseViewTest extends TestCase
{
    public function testPermissionRequiredToViewLicense()
    {
        $license = License::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('licenses.show', $license))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.show', License::factory()->create()->id))
            ->assertOk();
    }
    
    public function testLicenseWithPurchaseDateDepreciatesCorrectly()
    {
        $depreciation = Depreciation::factory()->create(['months' => 12]);
        $license = License::factory()->create(['depreciation_id' => $depreciation->id, 'purchase_date' => '2020-01-01']);
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.show', $license))
            ->assertOk()
            ->assertSee([
                '2021-01-01'
            ], false);
    }
}
