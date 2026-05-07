<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteAssetsTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.assets.destroy', $asset))
            ->assertForbidden();

        $this->assertNotSoftDeleted($asset);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->for($companyA)->create();
        $assetB = Asset::factory()->for($companyB)->create();
        $assetC = Asset::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteAssets()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->deleteAssets()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.assets.destroy', $assetB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.assets.destroy', $assetA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.assets.destroy', $assetC))
            ->assertStatusMessageIs('success');

        $this->assertNotSoftDeleted($assetA);
        $this->assertNotSoftDeleted($assetB);
        $this->assertSoftDeleted($assetC);
    }

    public function test_cannot_delete_asset_that_is_checked_out()
    {
        $this->markTestSkipped('This behavior is not functioning yet.');
    }

    public function test_can_delete_asset()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->deleteAssets()->create())
            ->deleteJson(route('api.assets.destroy', $asset))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($asset);
        $this->assertHasTheseActionLogs($asset, ['create', 'delete']);
    }
}
