<?php

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;

/**
 * One-shot cleanup for installs that ran either of the earlier
 * calendar backfill migrations before the trashed-source gate landed.
 * Those backfills loaded sources withTrashed() and force-synced them,
 * which published live calendar_events rows for records the admin had
 * already soft-deleted. The user-visible symptom was deleted
 * Maintenances showing up on the calendar with no way to open or
 * remove them (see issue #19760).
 *
 * For every HasCalendarEvents source model, soft-delete the
 * calendar_events rows whose source row is currently trashed. This
 * matches the shape the deleted observer would have written if the
 * source had been soft-deleted after the backfill, so if the admin
 * ever restores the source, the restored observer's query-builder
 * restore brings these back live.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (CalendarEvent::sourceModels() as $sourceClass) {
            if (! in_array(SoftDeletes::class, class_uses_recursive($sourceClass), true)) {
                continue;
            }

            // IDs of trashed source rows. Deliberately not using
            // onlyTrashed() so the query stays explicit about the
            // intent and so chunked reads on large tables don't fight
            // with the SoftDeletes global scope.
            $trashedIds = $sourceClass::query()
                ->onlyTrashed()
                ->pluck((new $sourceClass)->getKeyName())
                ->all();

            if ($trashedIds === []) {
                continue;
            }

            // Chunk the IN list so an install with thousands of trashed
            // sources doesn't blow MySQL's prepared-statement placeholder
            // ceiling 
            foreach (array_chunk($trashedIds, 1000) as $chunk) {
                CalendarEvent::query()
                    ->where('source_type', $sourceClass)
                    ->whereIn('source_id', $chunk)
                    ->delete();
            }
        }
    }

    public function down(): void
    {
        // No-op. Restoring these would re-expose the symptom the
        // forward migration exists to fix. Admins who restore an
        // affected source get the live calendar_event back via the
        // restored observer in HasCalendarEvents.
    }
};
