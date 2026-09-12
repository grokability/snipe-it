<?php

namespace Tests\Feature\Migrations;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CleanStalePendingAcceptancesTest extends TestCase
{
    public function test_it_clears_a_pending_acceptance_for_an_asset_the_user_no_longer_holds(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['assigned_to' => null, 'assigned_type' => null]);

        $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->pending()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    public function test_it_clears_a_pending_acceptance_for_a_license_seat_the_user_no_longer_holds(): void
    {
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->create(['assigned_to' => null]);

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    public function test_it_clears_a_pending_acceptance_for_an_accessory_the_user_no_longer_holds(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    /**
     * Consumables have no checkin route at all, so this state cannot be produced
     * through the UI — it is constructed directly. The migration still covers the
     * type because user-deletion paths do remove consumables_users rows.
     */
    public function test_it_clears_a_pending_acceptance_for_a_consumable_the_user_no_longer_holds(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Consumable::class,
            'checkoutable_id' => $consumable->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    public function test_it_leaves_a_pending_acceptance_alone_when_the_user_still_holds_the_asset(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->pending()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertSame($user->id, $asset->refresh()->assigned_to);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
    }

    public function test_it_leaves_a_pending_acceptance_alone_when_the_user_still_holds_the_license_seat(): void
    {
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->assignedToUser($user)->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
    }

    public function test_it_leaves_a_pending_acceptance_alone_when_the_user_still_holds_the_accessory(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
    }

    public function test_it_leaves_a_pending_acceptance_alone_when_the_user_still_holds_the_consumable(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $consumable->users()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
            'created_by' => User::factory()->create()->id,
        ]);

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Consumable::class,
            'checkoutable_id' => $consumable->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
    }

    /**
     * Restoring an asset hands it back unassigned, so this pending could never
     * become actionable — it would sit on /account/accept with no buttons.
     */
    public function test_it_clears_a_pending_acceptance_when_the_asset_is_soft_deleted_but_still_assigned(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();
        $asset->delete();

        $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->pending()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertSoftDeleted($asset);
        $this->assertSame($user->id, Asset::withTrashed()->find($asset->id)->assigned_to);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    /**
     * Bulk license deletion soft-deletes seats without nulling assigned_to, so
     * this state is ordinary. There is no seat restore path anywhere in the
     * app, so the pending is dead rather than dormant.
     */
    public function test_it_clears_a_pending_acceptance_when_the_license_seat_is_soft_deleted_but_still_assigned(): void
    {
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->assignedToUser($user)->create();
        $seat->delete();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->assertSoftDeleted($seat);
        $this->assertSame($user->id, LicenseSeat::withTrashed()->find($seat->id)->assigned_to);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    /**
     * The accessory pivot carries assigned_type, so a row assigned to an Asset
     * rather than a User is not this user holding the item.
     */
    public function test_it_clears_when_the_only_accessory_pivot_row_belongs_to_another_holder_type(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create();

        AccessoryCheckout::create([
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
            'assigned_type' => Asset::class,
            'created_by' => User::factory()->create()->id,
        ]);

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    public function test_it_leaves_already_accepted_rows_alone(): void
    {
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->create(['assigned_to' => null]);

        $acceptance = CheckoutAcceptance::factory()->accepted()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
    }

    public function test_it_leaves_already_declined_rows_alone(): void
    {
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->create(['assigned_to' => null]);

        $acceptance = CheckoutAcceptance::factory()->declined()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
    }

    public function test_it_soft_deletes_rather_than_hard_deletes(): void
    {
        $user = User::factory()->create();
        $seat = LicenseSeat::factory()->create(['assigned_to' => null]);

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $this->assertDatabaseHas('checkout_acceptances', ['id' => $acceptance->id]);
        $this->assertNotNull(CheckoutAcceptance::withTrashed()->find($acceptance->id)->deleted_at);
        $this->assertNull(CheckoutAcceptance::find($acceptance->id));
    }

    public function test_running_it_twice_leaves_the_same_result(): void
    {
        $user = User::factory()->create();

        $staleSeat = LicenseSeat::factory()->create(['assigned_to' => null]);
        $stale = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $staleSeat->id,
            'assigned_to_id' => $user->id,
        ]);

        $heldSeat = LicenseSeat::factory()->assignedToUser($user)->create();
        $held = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $heldSeat->id,
            'assigned_to_id' => $user->id,
        ]);

        $this->runMigration();

        $deletedAtAfterFirstRun = CheckoutAcceptance::withTrashed()->find($stale->id)->deleted_at;

        $this->runMigration();

        $this->assertEquals(
            $deletedAtAfterFirstRun,
            CheckoutAcceptance::withTrashed()->find($stale->id)->deleted_at,
            'The second run re-stamped an already soft-deleted row; up() is not idempotent.'
        );
        $this->assertAcceptanceSurvived($held);
    }

    public function test_it_does_not_touch_rows_belonging_to_a_different_user(): void
    {
        $holder = User::factory()->create();
        $otherUser = User::factory()->create();
        $seat = LicenseSeat::factory()->assignedToUser($holder)->create();

        // The seat is held, but by someone else — so this pending row is stale.
        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $otherUser->id,
        ]);

        $this->runMigration();

        $this->assertAcceptanceCleared($acceptance);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Quantity reconcile — accessories and consumables are not 1:1, so a
    // held/not-held test cannot see "holds one unit, has pendings worth three".
    // ─────────────────────────────────────────────────────────────────────

    public function test_it_decrements_an_accessory_pending_whose_qty_exceeds_the_units_held(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 3,
        ]);

        $this->runMigration();

        // Deleting the row would leave the held unit with no record at all.
        $this->assertAcceptanceSurvived($acceptance);
        $this->assertSame(1, $this->qtyOf($acceptance));
    }

    public function test_it_spills_excess_units_across_accessory_acceptance_rows(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $older = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 2,
        ]);

        $newer = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 2,
        ]);

        $this->runMigration();

        // Four pending against one held: the older row is wholly excess and
        // goes, the remainder bites into the newer one.
        $this->assertAcceptanceCleared($older);
        $this->assertAcceptanceSurvived($newer);
        $this->assertSame(1, $this->qtyOf($newer));
    }

    public function test_it_clears_a_multi_unit_accessory_pending_when_the_user_holds_nothing(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 3,
        ]);

        $this->runMigration();

        // Holding nothing makes the row wholly excess, so the reconcile is a
        // superset of the boolean pass it replaced.
        $this->assertAcceptanceCleared($acceptance);
    }

    public function test_it_leaves_an_accessory_pending_alone_when_its_qty_matches_the_units_held(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUsers([$user, $user, $user])->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 3,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
        $this->assertSame(3, $this->qtyOf($acceptance), 'A correct row must not be renumbered.');
    }

    public function test_it_decrements_a_consumable_pending_whose_qty_exceeds_the_units_held(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $this->attachConsumableTo($consumable, $user);

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Consumable::class,
            'checkoutable_id' => $consumable->id,
            'assigned_to_id' => $user->id,
            'qty' => 4,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
        $this->assertSame(1, $this->qtyOf($acceptance));
    }

    public function test_running_the_reconcile_twice_does_not_decrement_further(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $acceptance = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 3,
        ]);

        $this->runMigration();
        $this->runMigration();

        $this->assertAcceptanceSurvived($acceptance);
        $this->assertSame(1, $this->qtyOf($acceptance), 'The second run decremented again; the reconcile is not idempotent.');
    }

    /**
     * Accepted rows are history and may cover units returned years ago, so
     * netting them off could clear a pending for a unit still held. The
     * reconcile compares against units held only, leaving an over-count here —
     * the safe direction, which the live checkin path narrows from there.
     */
    public function test_it_does_not_net_accepted_history_off_the_pending_total(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUsers([$user, $user, $user])->create();

        $accepted = CheckoutAcceptance::factory()->accepted()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 2,
        ]);

        $pending = CheckoutAcceptance::factory()->pending()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
            'assigned_to_id' => $user->id,
            'qty' => 3,
        ]);

        $this->runMigration();

        $this->assertAcceptanceSurvived($pending);
        $this->assertSame(3, $this->qtyOf($pending));
        $this->assertAcceptanceSurvived($accepted);
        $this->assertSame(2, $this->qtyOf($accepted), 'An accepted row must never be renumbered.');
    }

    /**
     * LazilyRefreshDatabase has already run this migration once against an empty
     * database, so each test builds fixtures and runs up() again — which only
     * works because up() is idempotent.
     */
    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_08_24_234955_clean_stale_pending_acceptances.php');
        $migration->up();
    }

    private function attachConsumableTo(Consumable $consumable, User $user): void
    {
        $consumable->users()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
            'created_by' => User::factory()->create()->id,
        ]);
    }

    private function qtyOf(CheckoutAcceptance $acceptance): int
    {
        return (int) DB::table('checkout_acceptances')->where('id', $acceptance->id)->value('qty');
    }

    private function assertAcceptanceCleared(CheckoutAcceptance $acceptance): void
    {
        $row = DB::table('checkout_acceptances')->where('id', $acceptance->id)->first();

        $this->assertNotNull($row, 'The row was hard-deleted; the migration must soft-delete.');
        $this->assertNotNull($row->deleted_at, 'Expected the migration to soft-delete this stale pending acceptance.');
    }

    private function assertAcceptanceSurvived(CheckoutAcceptance $acceptance): void
    {
        $row = DB::table('checkout_acceptances')->where('id', $acceptance->id)->first();

        $this->assertNotNull($row, 'The row was hard-deleted; the migration should not have touched it.');
        $this->assertNull($row->deleted_at, 'The migration soft-deleted a row it should have left alone.');
    }
}
