<?php

use App\Models\Asset;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

/**
 * Insert an action_log row bypassing Eloquent so that company_id stays NULL,
 * simulating a historical record written before FMCS stamping was added.
 */
function insertLegacyLog(array $attributes): int
{
    return DB::table('action_logs')->insertGetId(array_merge([
        'action_type' => 'audit',
        'item_type' => 'App\\Models\\Asset',
        'item_id' => null,
        'company_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

/**
 * Run the same UPDATE logic the migration uses.
 */
function runBackfill(): void
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
            ', ['audit', 'App\\Models\\Asset']);
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
            ', ['audit', 'App\\Models\\Asset']);
    }
}

test('backfill populates company id for asset audit', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->create(['company_id' => $company->id]);

    $logId = insertLegacyLog(['item_type' => 'App\\Models\\Asset', 'item_id' => $asset->id]);

    runBackfill();

    $this->assertDatabaseHas('action_logs', [
        'id' => $logId,
        'company_id' => $company->id,
    ]);
});

test('backfill does not overwrite existing company id', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $asset = Asset::factory()->create(['company_id' => $otherCompany->id]);

    // Row already has a company_id — the backfill must leave it alone
    $logId = insertLegacyLog([
        'item_type' => 'App\\Models\\Asset',
        'item_id' => $asset->id,
        'company_id' => $company->id,
    ]);

    runBackfill();

    $this->assertDatabaseHas('action_logs', [
        'id' => $logId,
        'company_id' => $company->id, // unchanged
    ]);
});

test('backfill leaves null when item has no company', function () {
    $asset = Asset::factory()->create(['company_id' => null]);

    $logId = insertLegacyLog(['item_type' => 'App\\Models\\Asset', 'item_id' => $asset->id]);

    runBackfill();

    $this->assertDatabaseHas('action_logs', [
        'id' => $logId,
        'company_id' => null, // item has no company, so log stays null
    ]);
});

test('backfill ignores non audit action logs', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->create(['company_id' => $company->id]);

    $logId = insertLegacyLog([
        'action_type' => 'checkout',
        'item_type' => 'App\\Models\\Asset',
        'item_id' => $asset->id,
    ]);

    runBackfill();

    $this->assertDatabaseHas('action_logs', [
        'id' => $logId,
        'company_id' => null,
    ]);
});
