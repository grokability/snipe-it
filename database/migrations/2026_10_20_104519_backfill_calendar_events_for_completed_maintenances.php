<?php

use App\Models\Maintenance;
use Illuminate\Database\Migrations\Migration;

/**
 * One-time backfill for #19870. This re-syncs every
 * maintenance that has completed_at set so its
 * calendar_events row picks up the actual completion end.
 */
return new class extends Migration
{
    public function up(): void
    {
        Maintenance::query()
            ->whereNotNull('completed_at')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $row->forceSyncCalendarEvents();
                }
            });
    }

    public function down(): void
    {
        // no-op
    }
};
