<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rewrite historical `action_logs` rows that carry the SCIM
     * subclass names in `item_type` / `target_type` to the canonical
     * parent class. `SCIMUser` / `SCIMGroup` exist only to route
     * library hooks (same DB table, same id as `User` / `Group`),
     * but every save through the SCIM path used to stamp the
     * subclass into `action_logs` because `Loggable` reads
     * `$this::class` at write time. The frontend's activity-log
     * link formatter only knows `user` / `group`, so SCIM-sourced
     * rows rendered as broken `//<id>` hrefs (issue #20118).
     *
     * `Loggable::normalizeLoggableClass()` now blocks new writes
     * from taking the subclass shape. This migration cleans the
     * installs that already accumulated the bad rows.
     */
    public function up(): void
    {
        $mappings = [
            'App\\Models\\SCIMUser' => 'App\\Models\\User',
            'App\\Models\\SCIMGroup' => 'App\\Models\\Group',
        ];

        foreach ($mappings as $from => $to) {
            DB::table('action_logs')
                ->where('item_type', $from)
                ->update(['item_type' => $to]);

            DB::table('action_logs')
                ->where('target_type', $from)
                ->update(['target_type' => $to]);
        }
    }

    public function down(): void
    {
        // No-op. SCIMUser / SCIMGroup were never the semantically-
        // correct values for `action_logs.item_type`, so there is
        // nothing to restore on a down migration.
    }
};
