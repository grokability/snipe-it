<?php

namespace Tests\Feature\Migrations;

use App\Enums\ActionType;
use App\Models\Accessory;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Coverage for the pair of qty-safety migrations tied to issue #19474
 * (legacy accessories getting reset to 1 after the v8.7.0 reconcile).
 *
 * The migrations under test are:
 *
 *   - 2026_08_03_144000_reconcile_inventory_qty_from_action_logs.php
 *       Post-fix version has a `qty_adjust`-presence guard that skips
 *       any row the AdjustsQuantity trait has never touched.
 *
 *   - 2026_08_13_000000_restore_clobbered_inventory_qty_from_legacy_snapshot.php
 *       New floor migration that undoes the damage on installs which
 *       ran the pre-fix reconcile. Restores qty from the legacy_qty
 *       column captured by 2026_08_03_143500 for rows still showing
 *       the clobber pattern (qty < legacy_qty AND zero qty_adjust
 *       events since).
 *
 * Both migrations are one-shot data migrations. The tests here
 * instantiate the migration class directly and call ->up(), which is
 * safe against LazilyRefreshDatabase because both are designed to be
 * idempotent (the reconcile is a no-op on rows that already agree with
 * the ledger, and the restore is a no-op on rows where qty >= legacy_qty
 * or where qty_adjust events exist).
 */
#[Group('mysql')]
class InventoryQtyReconcileAndRestoreTest extends TestCase
{
    private function runReconcile(): void
    {
        $migration = require database_path('migrations/2026_08_03_144000_reconcile_inventory_qty_from_action_logs.php');
        $migration->up();
    }

    private function runRestore(): void
    {
        $migration = require database_path('migrations/2026_08_13_000000_restore_clobbered_inventory_qty_from_legacy_snapshot.php');
        $migration->up();
    }

    /**
     * Insert a legacy create action_log entry the way pre-trait Snipe-IT
     * used to: `create` type with a fixed quantity that does not reflect
     * the parent row's real on-hand count. Bypass Eloquent to avoid the
     * observer writing its own more-modern entry on top.
     */
    private function insertLegacyCreateLog(Accessory $accessory, int $quantity = 1): void
    {
        DB::table('action_logs')->insert([
            'action_type' => ActionType::Create->value,
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'quantity' => $quantity,
            'company_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertQtyAdjustLog(Accessory $accessory, int $delta): void
    {
        DB::table('action_logs')->insert([
            'action_type' => ActionType::QuantityAdjust->value,
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'quantity' => $delta,
            'company_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function factoryAccessoryWithoutObserverLogs(int $qty): Accessory
    {
        // Suppress the observer-driven create log so tests control the
        // ledger shape explicitly. Without this the factory's created
        // observer would write a matching create+quantity log, which
        // would defeat the point of a "pre-trait legacy row" fixture.
        Accessory::withoutEvents(fn () => null);

        $accessory = Accessory::factory()->create(['qty' => $qty]);

        // Delete any log entries the observer chain wrote so we have a
        // clean slate to add our own legacy fixture entries.
        DB::table('action_logs')
            ->where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->delete();

        // Snapshot the current qty into legacy_qty the same way the
        // 143500 snapshot migration did. Direct UPDATE bypasses casts.
        DB::table('accessories')
            ->where('id', $accessory->id)
            ->update(['legacy_qty' => $qty]);

        return $accessory->refresh();
    }

    // ────────────────────────────────────────────────────────────────
    // Tightened reconcile guard
    // ────────────────────────────────────────────────────────────────

    #[Test]
    public function tightened_reconcile_skips_legacy_row_with_only_a_stray_create_log()
    {
        // The exact #19474 pattern: pre-trait accessory with real
        // qty=50, ledger has a single create entry with quantity=1 and
        // no qty_adjust events. Pre-fix reconcile clobbered these to 1.
        // Post-fix reconcile must skip them.
        $accessory = $this->factoryAccessoryWithoutObserverLogs(50);
        $this->insertLegacyCreateLog($accessory, 1);

        $this->runReconcile();

        $this->assertSame(50, (int) $accessory->fresh()->qty);
    }

    #[Test]
    public function tightened_reconcile_still_reconciles_rows_with_qty_adjust_history()
    {
        // Post-trait accessory: create log matches initial qty, then
        // qty_adjust entries record replenishment. If somehow the qty
        // column drifted from the ledger sum (direct DB edit, botched
        // restore) the reconcile should still correct it - qty_adjust
        // presence is the signal that the ledger is trustworthy for
        // this row.
        $accessory = $this->factoryAccessoryWithoutObserverLogs(3);
        $this->insertLegacyCreateLog($accessory, 5); // ledger says initial was 5
        $this->insertQtyAdjustLog($accessory, 2);    // then +2, ledger sum = 7

        $this->runReconcile();

        $this->assertSame(7, (int) $accessory->fresh()->qty);
    }

    // ────────────────────────────────────────────────────────────────
    // Floor migration: restore from legacy_qty snapshot
    // ────────────────────────────────────────────────────────────────

    #[Test]
    public function restore_lifts_qty_back_to_legacy_snapshot_for_clobbered_row()
    {
        // Simulate the post-clobber state: qty was reconciled down to 1,
        // legacy_qty preserves the pre-clobber value of 50, and there
        // are no qty_adjust events (which is the whole reason the row
        // got clobbered in the first place).
        $accessory = $this->factoryAccessoryWithoutObserverLogs(50);
        DB::table('accessories')->where('id', $accessory->id)->update(['qty' => 1]);
        $this->insertLegacyCreateLog($accessory, 1);

        $this->runRestore();

        $this->assertSame(50, (int) $accessory->fresh()->qty);
    }

    #[Test]
    public function restore_leaves_row_alone_when_qty_matches_or_exceeds_legacy_snapshot()
    {
        // Post-clobber, admin manually re-adjusted qty back up to a
        // value at or above legacy_qty (they might have replenished
        // more than they had before, or just restored it to its old
        // value). The restore must not touch these.
        $accessory = $this->factoryAccessoryWithoutObserverLogs(50);
        DB::table('accessories')->where('id', $accessory->id)->update(['qty' => 60]);

        $this->runRestore();

        $this->assertSame(60, (int) $accessory->fresh()->qty);
    }

    #[Test]
    public function restore_leaves_row_alone_when_admin_has_used_the_replenish_ui_since()
    {
        // qty is below legacy_qty AND there's at least one qty_adjust
        // entry. The admin has been managing this row's stock through
        // the replenish/decrement UI since the clobber, so their
        // downward adjustment is intentional and must not be rolled
        // back to the pre-clobber snapshot.
        $accessory = $this->factoryAccessoryWithoutObserverLogs(50);
        DB::table('accessories')->where('id', $accessory->id)->update(['qty' => 30]);
        $this->insertQtyAdjustLog($accessory, -20); // admin decremented from 50 to 30

        $this->runRestore();

        $this->assertSame(30, (int) $accessory->fresh()->qty);
    }

    #[Test]
    public function restore_is_a_no_op_when_legacy_qty_is_null()
    {
        // Rows created after the snapshot migration ran leave
        // legacy_qty NULL. Restore must skip them - there's nothing
        // to restore from.
        $accessory = $this->factoryAccessoryWithoutObserverLogs(10);
        DB::table('accessories')->where('id', $accessory->id)->update([
            'qty' => 5,
            'legacy_qty' => null,
        ]);

        $this->runRestore();

        $this->assertSame(5, (int) $accessory->fresh()->qty);
    }
}
