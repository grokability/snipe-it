<?php

namespace Tests\Feature\ActionLogs;

use App\Models\Asset;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifies the backfill migration logic that stamps action_logs.company_id
 * for legacy asset audit rows where the company is currently NULL.
 *
 * Rather than running the migration class directly (which would conflict with
 * LazilyRefreshDatabase), we replicate the exact UPDATE SQL used by the
 * migration and assert on the resulting rows.
 */
class ActionlogCompanyIdBackfillTest extends TestCase
{
    private const ASSET_CLASS = 'App\\Models\\Asset';

    private const AUDIT_ACTION = 'audit';

    /**
     * Insert an action_log row bypassing Eloquent so that company_id stays NULL,
     * simulating a historical record written before FMCS stamping was added.
     */
    private function insertLegacyLog(array $attributes): int
    {
        return DB::table('action_logs')->insertGetId(array_merge([
            'action_type' => self::AUDIT_ACTION,
            'item_type' => self::ASSET_CLASS,
            'item_id' => null,
            'company_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    /**
     * Run the same UPDATE logic the migration uses.
     */
    private function runBackfill(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('
                UPDATE action_logs al
                INNER JOIN assets src ON src.id = al.item_id AND src.company_id IS NOT NULL
                SET al.company_id = src.company_id
                WHERE al.action_type = ?
                  AND al.item_type = ?
                  AND al.company_id IS NULL
                  AND al.deleted_at IS NULL
            ', [self::AUDIT_ACTION, self::ASSET_CLASS]);
        } else {
            DB::statement('
                UPDATE action_logs
                SET company_id = (
                    SELECT src.company_id FROM assets src
                    WHERE src.id = action_logs.item_id AND src.company_id IS NOT NULL
                    LIMIT 1
                )
                WHERE action_type = ?
                  AND item_type = ?
                  AND company_id IS NULL
                  AND deleted_at IS NULL
                  AND EXISTS (
                      SELECT 1 FROM assets src2
                      WHERE src2.id = action_logs.item_id AND src2.company_id IS NOT NULL
                  )
            ', [self::AUDIT_ACTION, self::ASSET_CLASS]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────

    public function test_backfill_populates_company_id_for_asset_audit(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);

        $logId = $this->insertLegacyLog(['item_type' => self::ASSET_CLASS, 'item_id' => $asset->id]);

        $this->runBackfill();

        $this->assertDatabaseHas('action_logs', [
            'id' => $logId,
            'company_id' => $company->id,
        ]);
    }

    public function test_backfill_does_not_overwrite_existing_company_id(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $otherCompany->id]);

        // Row already has a company_id — the backfill must leave it alone
        $logId = $this->insertLegacyLog([
            'item_type' => self::ASSET_CLASS,
            'item_id' => $asset->id,
            'company_id' => $company->id,
        ]);

        $this->runBackfill();

        $this->assertDatabaseHas('action_logs', [
            'id' => $logId,
            'company_id' => $company->id, // unchanged
        ]);
    }

    public function test_backfill_leaves_null_when_item_has_no_company(): void
    {
        $asset = Asset::factory()->create(['company_id' => null]);

        $logId = $this->insertLegacyLog(['item_type' => self::ASSET_CLASS, 'item_id' => $asset->id]);

        $this->runBackfill();

        $this->assertDatabaseHas('action_logs', [
            'id' => $logId,
            'company_id' => null, // item has no company, so log stays null
        ]);
    }

    public function test_backfill_ignores_non_audit_action_logs(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);

        $logId = $this->insertLegacyLog([
            'action_type' => 'checkout',
            'item_type' => self::ASSET_CLASS,
            'item_id' => $asset->id,
        ]);

        $this->runBackfill();

        $this->assertDatabaseHas('action_logs', [
            'id' => $logId,
            'company_id' => null,
        ]);
    }
}
