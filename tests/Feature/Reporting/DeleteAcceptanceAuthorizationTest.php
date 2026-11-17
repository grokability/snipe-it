<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * Auth + FMCS pins for the acceptance-delete endpoint on the moved
 * /unaccepted-acceptances surface. Gate shifted from reports.view to
 * canCheckoutAtLeastOneItemType in v8.9 so the mutating path isn't
 * reachable by a read-only Reports grant.
 */
class DeleteAcceptanceAuthorizationTest extends TestCase
{
    public function test_user_without_checkout_grant_cannot_delete_acceptance()
    {
        $acceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertForbidden();

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_user_with_reports_view_only_cannot_delete_acceptance()
    {
        // reports.view no longer unlocks the acceptance mutations.
        $acceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertForbidden();

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_checkout_user_can_delete_acceptance_for_their_own_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA] = Company::factory()->count(2)->create();

        $asset = Asset::factory()->create(['company_id' => $companyA->id]);
        $reporter = User::factory()->checkoutAccessories()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->for($asset, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('success');

        $this->assertNotNull($acceptance->fresh()->deleted_at);
    }

    public function test_checkout_user_cannot_delete_acceptance_belonging_to_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->checkoutAccessories()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('error');

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_superuser_can_delete_acceptance_from_any_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $superuser = User::factory()->superuser()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($superuser)
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('success');

        $this->assertNotNull($acceptance->fresh()->deleted_at);
    }

    public function test_pivot_only_user_cannot_delete_acceptance_belonging_to_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->checkoutAccessories()->withoutCompany()->create();
        $reporter->companies()->sync([$companyA->id]);

        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('error');

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_company_scoping_not_enforced_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->checkoutAccessories()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('unaccepted-acceptances.destroy', $acceptance->id))
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('success');

        $this->assertNotNull($acceptance->fresh()->deleted_at);
    }
}
