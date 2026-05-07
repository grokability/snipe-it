<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill action_logs.company_id only for legacy asset audits where the
 * value is currently NULL.
 *
 * Audits are only recorded on assets, so this migration intentionally scopes
 * to action_type='audit' and item_type=App\Models\Asset.
 *
 * Rows whose asset genuinely has no company (assets.company_id IS NULL) are
 * left as NULL.
 */
return new class extends Migration
{
    private const ASSET_CLASS = 'App\\Models\\Asset';

    private const AUDIT_ACTION = 'audit';

    public function up(): void
    {
        $this->updateAssetAuditLogs(DB::getDriverName());
    }

    public function down(): void
    {
        // This backfill is intentionally non-reversible — we cannot know which
        // rows were NULL before the migration ran vs which were backfilled.
    }

    /**
     * Stamp company_id for legacy audit rows tied to assets.
     */
    private function updateAssetAuditLogs(string $driver): void
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL/MariaDB supports UPDATE ... JOIN directly
            DB::statement('
                UPDATE action_logs al
                INNER JOIN assets src
                    ON src.id = al.item_id
                    AND src.company_id IS NOT NULL
                SET al.company_id = src.company_id
                WHERE al.action_type = ?
                  AND al.item_type = ?
                  AND al.company_id IS NULL
                  AND al.deleted_at IS NULL
            ', [self::AUDIT_ACTION, self::ASSET_CLASS]);
        } else {
            // SQLite / PostgreSQL: use a correlated subquery update
            DB::statement('
                UPDATE action_logs
                SET company_id = (
                    SELECT src.company_id
                    FROM assets src
                    WHERE src.id = action_logs.item_id
                      AND src.company_id IS NOT NULL
                    LIMIT 1
                )
                WHERE action_type = ?
                  AND item_type = ?
                  AND company_id IS NULL
                  AND deleted_at IS NULL
                  AND EXISTS (
                      SELECT 1 FROM assets src2
                      WHERE src2.id = action_logs.item_id
                        AND src2.company_id IS NOT NULL
                  )
            ', [self::AUDIT_ACTION, self::ASSET_CLASS]);
        }
    }
};
