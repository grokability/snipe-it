<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for the FMCS scope gap originally reported by
 * Arpit Jain (arpitjain099) on 2026-08-02. The old
 * /reports/unaccepted_assets page ran CheckoutAcceptance::pending()
 * with no company scope. CheckoutAcceptance has no company_id column
 * and does not use CompanyableTrait / CompanyableChildTrait, so the
 * CompanyableScope global scope never applies on its own.
 *
 * Post-v8.9 the surface is API-driven. The API filters at the DB
 * layer via whereHasMorph(viewableTypes), which routes each morph
 * type through its own CompanyableTrait (and CompanyableChildTrait
 * for LicenseSeat). Companies the caller can't see fall out of the
 * query, so both the row list AND the total count stay scoped.
 */
class AcceptanceReportFmcsScopeTest extends TestCase
{
    private function seedPendingAcceptanceOwnedBy(Company $company): array
    {
        $asset = Asset::factory()->create(['company_id' => $company->id, 'name' => 'Asset-'.$company->id]);
        $acceptance = CheckoutAcceptance::factory()->pending()->for($asset, 'checkoutable')->create();

        return [$asset, $acceptance];
    }

    public function test_api_hides_other_company_pending_acceptances_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        [$assetA] = $this->seedPendingAcceptanceOwnedBy($companyA);
        [$assetB] = $this->seedPendingAcceptanceOwnedBy($companyB);

        $callerA = User::factory()->viewAssets()->checkoutAccessories()->forCompany($companyA)->create();

        $response = $this->actingAsForApi($callerA)
            ->getJson(route('api.unaccepted-acceptances.index'))
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $itemNames = array_map(fn ($row) => $row['item']['name'], $response->json('rows'));
        $this->assertContains($assetA->display_name, $itemNames);
        $this->assertNotContains($assetB->display_name, $itemNames);
    }

    public function test_api_shows_all_pending_acceptances_to_a_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        [$assetA] = $this->seedPendingAcceptanceOwnedBy($companyA);
        [$assetB] = $this->seedPendingAcceptanceOwnedBy($companyB);

        $superuser = User::factory()->superuser()->forCompany($companyA)->create();

        $response = $this->actingAsForApi($superuser)
            ->getJson(route('api.unaccepted-acceptances.index'))
            ->assertOk();

        $this->assertSame(2, $response->json('total'));
        $itemNames = array_map(fn ($row) => $row['item']['name'], $response->json('rows'));
        $this->assertContains($assetA->display_name, $itemNames);
        $this->assertContains($assetB->display_name, $itemNames);
    }

    public function test_fmcs_disabled_leaves_api_unscoped()
    {
        // With FMCS off the CompanyableTrait scope short-circuits and
        // every row passes. Guard against future refactors that would
        // accidentally scope installs that haven't opted into FMCS.
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        [$assetA] = $this->seedPendingAcceptanceOwnedBy($companyA);
        [$assetB] = $this->seedPendingAcceptanceOwnedBy($companyB);

        $callerA = User::factory()->viewAssets()->checkoutAccessories()->forCompany($companyA)->create();

        $response = $this->actingAsForApi($callerA)
            ->getJson(route('api.unaccepted-acceptances.index'))
            ->assertOk();

        $this->assertSame(2, $response->json('total'));
        $itemNames = array_map(fn ($row) => $row['item']['name'], $response->json('rows'));
        $this->assertContains($assetA->display_name, $itemNames);
        $this->assertContains($assetB->display_name, $itemNames);
    }
}
