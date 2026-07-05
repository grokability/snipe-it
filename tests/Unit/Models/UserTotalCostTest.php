<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

/**
 * Pins User::getUserTotalCost() — the source of every value rendered in the
 * "user well" on the user detail page (assets/licenses/accessories costs +
 * maintenance cost + active-maintenance count + grand total). All five
 * fields are computed in one pass so the blade can call the method four
 * times in a row without paying for redundant relation queries.
 */
class UserTotalCostTest extends TestCase
{
    public function test_maintenance_cost_sums_complete_and_active_records(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        // Mix of completed + active maintenances. The observer copies
        // checked_out_to_* from the asset's assigned_to/_type at insert
        // time, so all three of these will be tagged to $user.
        Maintenance::factory()->create(['asset_id' => $asset->id, 'cost' => 100.00, 'completed_at' => null]);
        Maintenance::factory()->create(['asset_id' => $asset->id, 'cost' => 50.50, 'completed_at' => now()]);
        Maintenance::factory()->create(['asset_id' => $asset->id, 'cost' => 25.00, 'completed_at' => null]);

        $totals = $user->getUserTotalCost();

        $this->assertEqualsWithDelta(175.50, (float) $totals->maintenance_cost, 0.001);
    }

    public function test_total_user_cost_now_rolls_maintenance_cost_into_the_grand_total(): void
    {
        // Previously total_user_cost = assets + licenses + accessories. After
        // the user-detail well started surfacing maintenance cost, the "total"
        // line needed to include it too so the rows still add up visually.
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create(['purchase_cost' => 200]);
        Maintenance::factory()->create(['asset_id' => $asset->id, 'cost' => 40.00]);

        $totals = $user->getUserTotalCost();

        $this->assertEqualsWithDelta(200, (float) $totals->asset_cost, 0.001);
        $this->assertEqualsWithDelta(40, (float) $totals->maintenance_cost, 0.001);
        $this->assertEqualsWithDelta(
            (float) $totals->asset_cost + (float) $totals->license_cost + (float) $totals->accessory_cost + (float) $totals->maintenance_cost,
            (float) $totals->total_user_cost,
            0.001,
            'total_user_cost must include maintenance_cost so the well rows add up',
        );
    }

    public function test_zero_state_when_user_has_no_records(): void
    {
        $user = User::factory()->create();

        $totals = $user->getUserTotalCost();

        $this->assertEqualsWithDelta(0, (float) $totals->maintenance_cost, 0.001);
        $this->assertEqualsWithDelta(0, (float) $totals->total_user_cost, 0.001);
    }
}
