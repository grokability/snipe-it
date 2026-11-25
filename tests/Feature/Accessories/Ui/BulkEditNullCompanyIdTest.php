<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * GHSA-rhwq-w7gg-rwr6 / GHSA-3prj-5x7r-hx5r. The API bulk-edit got a
 * null_company_id superuser gate in commit 26ac0828fc. The web mirror
 * had the same vulnerable null-out loop with no gate, so a companied
 * non-superuser with accessories.edit could POST null_company_id=1 to
 * strip tenant scoping.
 */
class BulkEditNullCompanyIdTest extends TestCase
{
    public function test_non_superuser_cannot_null_company_id_via_ui_bulk_edit_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAccessories()->viewAccessories()->make()
        );
        $accessory = Accessory::factory()->for($company, 'company')->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkedit.store'), [
                'ids' => [$accessory->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('accessories.index'));

        $this->assertEquals($company->id, $accessory->fresh()->company_id);
    }

    public function test_non_superuser_cannot_null_company_id_via_ui_bulk_edit_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAccessories()->viewAccessories()->make()
        );
        $accessory = Accessory::factory()->for($company, 'company')->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkedit.store'), [
                'ids' => [$accessory->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('accessories.index'));

        $this->assertEquals($company->id, $accessory->fresh()->company_id);
    }

    public function test_superuser_can_still_null_company_id_via_ui_bulk_edit()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $accessory = Accessory::factory()->for($company, 'company')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkedit.store'), [
                'ids' => [$accessory->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('accessories.index'));

        $this->assertNull($accessory->fresh()->company_id);
    }
}
