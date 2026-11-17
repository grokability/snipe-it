<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * Covers the pre-fix flow where `null_company_id=1` on the bulk-edit
 * endpoint let a non-superuser detach an accessory from its company,
 * bypassing the FMCS validation that the single-item update path
 * runs through `Company::getIdForCurrentUser()`. Under FMCS, a
 * null-company accessory becomes floater-visible to every tenant
 * (`null_company_is_floater=1`) or invisible to every non-superuser
 * (strict mode).
 */
class BulkEditNullCompanyIdTest extends TestCase
{
    public function test_non_superuser_cannot_null_company_id_via_bulk_edit_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAccessories()->viewAccessories()->make()
        );
        $accessory = Accessory::factory()->for($company)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.bulkedit'), [
                'ids' => [$accessory->id],
                'null_company_id' => true,
            ])
            ->assertStatus(422);

        $this->assertEquals($company->id, $accessory->fresh()->company_id);
    }

    public function test_non_superuser_cannot_null_company_id_via_bulk_edit_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAccessories()->viewAccessories()->make()
        );
        $accessory = Accessory::factory()->for($company)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.bulkedit'), [
                'ids' => [$accessory->id],
                'null_company_id' => true,
            ])
            ->assertStatus(422);

        $this->assertEquals($company->id, $accessory->fresh()->company_id);
    }

    public function test_superuser_can_still_null_company_id_via_bulk_edit()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $accessory = Accessory::factory()->for($company)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.accessories.bulkedit'), [
                'ids' => [$accessory->id],
                'null_company_id' => true,
            ])
            ->assertOk();

        $this->assertNull($accessory->fresh()->company_id);
    }

    public function test_non_superuser_can_still_bulk_edit_other_fields()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editAccessories()->viewAccessories()->make()
        );
        $accessory = Accessory::factory()->for($company)->create(['min_amt' => 1]);

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.bulkedit'), [
                'ids' => [$accessory->id],
                'min_amt' => 42,
            ])
            ->assertOk();

        $this->assertSame(42, (int) $accessory->fresh()->min_amt);
        $this->assertEquals($company->id, $accessory->fresh()->company_id);
    }
}
