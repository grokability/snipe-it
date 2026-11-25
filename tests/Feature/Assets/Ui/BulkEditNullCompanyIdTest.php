<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * GHSA-rhwq-w7gg-rwr6 / GHSA-3prj-5x7r-hx5r. The Asset bulk-edit path
 * uses the sentinel value company_id=clear (rather than the
 * null_company_id checkbox the Accessory / Component / Consumable
 * bulk-edit paths use). Without a superuser gate, a companied non-
 * superuser with assets.edit could POST company_id=clear to strip
 * tenant scoping from the selected rows.
 */
class BulkEditNullCompanyIdTest extends TestCase
{
    public function test_non_superuser_cannot_clear_company_id_via_ui_bulk_edit_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAssets()->viewAssets()->make()
        );
        $asset = Asset::factory()->for($company, 'company')->create();

        $this->actingAs($actor)
            ->post(route('hardware/bulksave'), [
                'ids' => [$asset->id],
                'company_id' => 'clear',
            ])
            ->assertStatus(302);

        $this->assertEquals($company->id, $asset->fresh()->company_id);
    }

    public function test_non_superuser_cannot_clear_company_id_via_ui_bulk_edit_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAssets()->viewAssets()->make()
        );
        $asset = Asset::factory()->for($company, 'company')->create();

        $this->actingAs($actor)
            ->post(route('hardware/bulksave'), [
                'ids' => [$asset->id],
                'company_id' => 'clear',
            ])
            ->assertStatus(302);

        $this->assertEquals($company->id, $asset->fresh()->company_id);
    }

    public function test_superuser_can_still_clear_company_id_via_ui_bulk_edit()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company, 'company')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware/bulksave'), [
                'ids' => [$asset->id],
                'company_id' => 'clear',
            ])
            ->assertStatus(302);

        $this->assertNull($asset->fresh()->company_id);
    }
}
