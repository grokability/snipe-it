<?php

namespace Tests\Feature\Reporting;

use App\Models\User;
use Tests\TestCase;

/**
 * Index-page authorization for /unaccepted-acceptances. The surface
 * moved out of /reports/unaccepted_assets in v8.9 and the gate
 * tightened from reports.view (plus a widened viewer carve-out) to
 * canCheckoutAtLeastOneItemType, so view-only users no longer reach
 * the page. Reporter: @ShaunNeighbourSwale (#19811).
 */
class UnacceptedAssetReportTest extends TestCase
{
    public function test_permission_required_to_view_unaccepted_acceptances()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('unaccepted-acceptances.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_unaccepted_acceptances()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('unaccepted-acceptances.index'))
            ->assertOk();
    }

    public function test_view_only_user_cannot_reach_the_page()
    {
        // Regression pin for the gate tightening. A user who can view
        // components but can't check anything out shouldn't reach the
        // page, since the mutating actions on it (reminder sends,
        // deletes) are what the page exists for.
        $this->actingAs(User::factory()->viewComponents()->create())
            ->get(route('unaccepted-acceptances.index'))
            ->assertForbidden();
    }

    public function test_checkout_grant_on_any_type_reaches_the_page()
    {
        // The gate is canCheckoutAtLeastOneItemType, so a checkout-
        // on-components user can view the page even without broader
        // admin permissions.
        $this->actingAs(User::factory()->checkoutComponents()->create())
            ->get(route('unaccepted-acceptances.index'))
            ->assertOk();
    }
}
