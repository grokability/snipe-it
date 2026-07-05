<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class DeleteAcceptanceAuthorizationTest extends TestCase
{
    public function test_user_without_reports_view_cannot_delete_acceptance()
    {
        $acceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('reports/unaccepted_assets_delete', $acceptance->id))
            ->assertForbidden();

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_reports_user_can_delete_acceptance_for_their_own_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA] = Company::factory()->count(2)->create();

        $asset = Asset::factory()->create(['company_id' => $companyA->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()->pending()->for($asset, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('reports/unaccepted_assets_delete', $acceptance->id))
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('success');

        $this->assertNotNull($acceptance->fresh()->deleted_at);
    }

    public function test_reports_user_cannot_delete_acceptance_belonging_to_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('reports/unaccepted_assets_delete', $acceptance->id))
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('error');

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_superuser_can_delete_acceptance_from_any_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $superuser = User::factory()->superuser()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($superuser)
            ->delete(route('reports/unaccepted_assets_delete', $acceptance->id))
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('success');

        $this->assertNotNull($acceptance->fresh()->deleted_at);
    }

    public function test_pivot_only_user_cannot_delete_acceptance_belonging_to_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => null]);
        $reporter->companies()->sync([$companyA->id]);

        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('reports/unaccepted_assets_delete', $acceptance->id))
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('error');

        $this->assertNull($acceptance->fresh()->deleted_at);
    }

    public function test_company_scoping_not_enforced_when_fmcs_disabled()
    {
        $this->settings->disableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()->pending()->for($assetB, 'checkoutable')->create();

        $this->actingAs($reporter)
            ->delete(route('reports/unaccepted_assets_delete', $acceptance->id))
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('success');

        $this->assertNotNull($acceptance->fresh()->deleted_at);
    }
}
