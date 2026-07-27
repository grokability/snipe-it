<?php

namespace Tests\Feature\Helpers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckLowInventoryTest extends TestCase
{
    /**
     * Regression pin for the customer report where a consumable with
     * min_amt=1 and remaining=1 was flagged as "below the minimum required
     * quantity" in the alert menu and the daily inventory-alerts email.
     * Sitting exactly at min_amt is not below min_amt and must not appear.
     */
    public function test_consumable_at_min_amt_is_not_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $consumable = Consumable::factory()->create(['qty' => 1, 'min_amt' => 1]);

        $this->assertNotContains(
            $consumable->id,
            $this->idsForType(Helper::checkLowInventory(), 'consumables'),
        );
    }

    public function test_consumable_below_min_amt_is_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $consumable = Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

        $this->assertContains(
            $consumable->id,
            $this->idsForType(Helper::checkLowInventory(), 'consumables'),
        );
    }

    public function test_consumable_above_min_amt_is_not_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $consumable = Consumable::factory()->create(['qty' => 5, 'min_amt' => 1]);

        $this->assertNotContains(
            $consumable->id,
            $this->idsForType(Helper::checkLowInventory(), 'consumables'),
        );
    }

    public function test_consumable_with_null_min_amt_is_never_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $consumable = Consumable::factory()->create(['qty' => 0, 'min_amt' => null]);

        $this->assertNotContains(
            $consumable->id,
            $this->idsForType(Helper::checkLowInventory(), 'consumables'),
        );
    }

    /**
     * alert_threshold is an early-warning buffer: with threshold=3 and
     * min_amt=5, warnings should start when only 2 units above min remain
     * (avail=7) and stop once the avail crosses the buffer edge upward
     * (avail=8 is comfortably outside the warning zone).
     */
    public function test_consumable_at_top_of_threshold_buffer_is_flagged()
    {
        $this->settings->set(['alert_threshold' => 3]);

        $consumable = Consumable::factory()->create(['qty' => 7, 'min_amt' => 5]);

        $this->assertContains(
            $consumable->id,
            $this->idsForType(Helper::checkLowInventory(), 'consumables'),
        );
    }

    public function test_consumable_just_outside_threshold_buffer_is_not_flagged()
    {
        $this->settings->set(['alert_threshold' => 3]);

        $consumable = Consumable::factory()->create(['qty' => 8, 'min_amt' => 5]);

        $this->assertNotContains(
            $consumable->id,
            $this->idsForType(Helper::checkLowInventory(), 'consumables'),
        );
    }

    public function test_accessory_at_min_amt_is_not_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $accessory = Accessory::factory()->create(['qty' => 1, 'min_amt' => 1]);

        $this->assertNotContains(
            $accessory->id,
            $this->idsForType(Helper::checkLowInventory(), 'accessories'),
        );
    }

    public function test_accessory_below_min_amt_is_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $accessory = Accessory::factory()->create(['qty' => 0, 'min_amt' => 1]);

        $this->assertContains(
            $accessory->id,
            $this->idsForType(Helper::checkLowInventory(), 'accessories'),
        );
    }

    public function test_component_at_min_amt_is_not_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $component = Component::factory()->create(['qty' => 1, 'min_amt' => 1]);

        $this->assertNotContains(
            $component->id,
            $this->idsForType(Helper::checkLowInventory(), 'components'),
        );
    }

    public function test_component_below_min_amt_is_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        // Component's validation rule is qty >= 1, so drive "below min" via
        // a higher min_amt rather than a zero qty.
        $component = Component::factory()->create(['qty' => 1, 'min_amt' => 2]);

        $this->assertContains(
            $component->id,
            $this->idsForType(Helper::checkLowInventory(), 'components'),
        );
    }

    /**
     * The AssetModel branch counts RTD (Ready to Deploy, unassigned) assets
     * via the availableAssets() scope, so this test creates exactly min_amt
     * such assets to prove that hitting the floor without falling through
     * doesn't fire the alert.
     */
    public function test_asset_model_at_min_amt_is_not_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $model = AssetModel::factory()->create(['min_amt' => 1]);
        Asset::factory()->create(['model_id' => $model->id]);

        $this->assertNotContains(
            $model->id,
            $this->idsForType(Helper::checkLowInventory(), 'models'),
        );
    }

    public function test_asset_model_below_min_amt_is_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        // min_amt=1 with zero RTD assets = below the floor.
        $model = AssetModel::factory()->create(['min_amt' => 1]);

        $this->assertContains(
            $model->id,
            $this->idsForType(Helper::checkLowInventory(), 'models'),
        );
    }

    /**
     * License seat records are auto-created by the License::created observer
     * (see app/Models/License.php), so a freshly-created license with seats=N
     * has N unassigned seats available.
     */
    public function test_license_at_min_amt_is_not_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $license = License::factory()->create(['seats' => 1, 'min_amt' => 1]);

        $this->assertNotContains(
            $license->id,
            $this->idsForType(Helper::checkLowInventory(), 'licenses'),
        );
    }

    public function test_license_below_min_amt_is_flagged_as_low()
    {
        $this->settings->set(['alert_threshold' => 0]);

        // seats=1 with min_amt=2 means only 1 seat available where 2 is the floor.
        $license = License::factory()->create(['seats' => 1, 'min_amt' => 2]);

        $this->assertContains(
            $license->id,
            $this->idsForType(Helper::checkLowInventory(), 'licenses'),
        );
    }

    /**
     * Checkouts reduce effective availability: an accessory with qty=5
     * and min_amt=3 is above the floor at rest, but with 3 units checked
     * out only 2 remain — below the floor — so the alert must fire.
     * Guards against the havingRaw filter silently ignoring the
     * checkouts_count subquery.
     */
    public function test_accessory_with_checkouts_below_min_amt_is_flagged()
    {
        $this->settings->set(['alert_threshold' => 0]);

        $accessory = Accessory::factory()->create(['qty' => 5, 'min_amt' => 3]);
        $user = User::factory()->create();

        $accessory->checkouts()->createMany([
            ['assigned_to' => $user->id, 'assigned_type' => User::class, 'created_by' => 1],
            ['assigned_to' => $user->id, 'assigned_type' => User::class, 'created_by' => 1],
            ['assigned_to' => $user->id, 'assigned_type' => User::class, 'created_by' => 1],
        ]);

        $this->assertContains(
            $accessory->id,
            $this->idsForType(Helper::checkLowInventory(), 'accessories'),
        );
    }

    public function test_accessory_with_checkouts_still_above_min_amt_is_not_flagged()
    {
        $this->settings->set(['alert_threshold' => 0]);

        // 5 total, 1 checked out → 4 remaining, min is 3 → above the floor.
        $accessory = Accessory::factory()->create(['qty' => 5, 'min_amt' => 3]);
        $user = User::factory()->create();
        $accessory->checkouts()->create([
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
            'created_by' => 1,
        ]);

        $this->assertNotContains(
            $accessory->id,
            $this->idsForType(Helper::checkLowInventory(), 'accessories'),
        );
    }

    /**
     * License seats are created by the License::created observer, and
     * assigning a seat should drop the "available" count. Locks in the
     * behaviour of the licenses_available withCount alias — the previous
     * code path went through $license->remaincount() which fires
     * additional N+1 queries and could drift from the alias.
     */
    public function test_license_with_assigned_seats_below_min_amt_is_flagged()
    {
        $this->settings->set(['alert_threshold' => 0]);

        // 5 seats, min 3 → healthy at rest. Assign 3 → 2 available → below min.
        $license = License::factory()->create(['seats' => 5, 'min_amt' => 3]);
        $seats = $license->licenseseats()->orderBy('id')->take(3)->get();
        foreach ($seats as $seat) {
            $seat->update(['assigned_to' => User::factory()->create()->id]);
        }

        $this->assertContains(
            $license->id,
            $this->idsForType(Helper::checkLowInventory(), 'licenses'),
        );
    }

    /**
     * Regression pin for the query-count refactor: the whole checkLowInventory
     * call should fire a bounded, small number of queries regardless of how
     * many low-inventory rows exist across each category. If someone
     * accidentally re-introduces a $model->numRemaining() / remaincount()
     * call in the foreach loops this will spike well past the ceiling.
     */
    public function test_check_low_inventory_query_count_stays_bounded()
    {
        $this->settings->set(['alert_threshold' => 0]);

        // Populate each category with several low-inventory rows so the
        // foreach paths that used to trigger extra per-row queries actually
        // execute.
        Consumable::factory()->count(3)->create(['qty' => 0, 'min_amt' => 1]);
        Accessory::factory()->count(3)->create(['qty' => 0, 'min_amt' => 1]);
        Component::factory()->count(3)->create(['qty' => 1, 'min_amt' => 5]);
        AssetModel::factory()->count(3)->create(['min_amt' => 5]);
        License::factory()->count(3)->create(['seats' => 1, 'min_amt' => 5]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Helper::checkLowInventory();

        // Ceiling covers: 1 settings + 5 category queries + 1 status_labels
        // lookup for the RTD() scope in the AssetModel branch = 7. Held to
        // 10 to leave a little headroom for a future settings-related
        // memoization change without loosening the N+1 guard.
        $count = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(
            10,
            $count,
            "checkLowInventory fired {$count} queries — expected <=10. Something is doing per-row DB access again.",
        );
    }

    private function idsForType(array $items, string $type): array
    {
        return collect($items)
            ->where('type', $type)
            ->pluck('id')
            ->all();
    }
}
