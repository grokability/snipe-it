<?php

use App\Models\Asset;
use App\Models\License;
use App\Models\Maintenance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catch-up migration for installs that already ran the original
 * create_calendar_events_table + backfill migrations before the
 * calendar_events.company_id column existed. Fresh schemas built
 * after the create migration gained the column no-op through this
 * migration via the hasColumn gate, and even when the column exists
 * we only touch rows whose company_id is still null, so an install
 * that already got populated via the updated backfill migration
 * skips the heavy work.
 *
 * Uses single-pass correlated-subquery UPDATEs per source class
 * rather than iterating source rows through the observer. On a
 * 500k-asset install the Eloquent-driven path would have fired
 * half a million UPDATE round-trips. This is three statements
 * total, scoped to NULL rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('calendar_events', 'company_id')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('end');
                $table->index('company_id', 'calendar_events_company_id_index');
            });
        }

        // Fast path. If every row already has company_id, there's
        // nothing to do (fresh install, or an upgrade that walked
        // the updated backfill migrations).
        if (! DB::table('calendar_events')->whereNull('company_id')->exists()) {
            return;
        }

        // Asset and License store company_id directly. One correlated-
        // subquery UPDATE per source resolves the whole set.
        $prefix = DB::getTablePrefix();
        $events = $prefix.'calendar_events';

        foreach ([Asset::class, License::class] as $sourceClass) {
            $table = $prefix.(new $sourceClass)->getTable();
            DB::statement(
                "UPDATE {$events} SET company_id = (
                    SELECT {$table}.company_id FROM {$table}
                    WHERE {$table}.id = {$events}.source_id
                 ) WHERE source_type = ? AND company_id IS NULL",
                [$sourceClass],
            );
        }

        // Maintenance stores no company_id of its own. company lives on
        // the parent asset (item_id when item_type matches Asset's
        // class string). Two-hop correlated subquery through assets.
        DB::statement(
            "UPDATE {$events} SET company_id = (
                SELECT a.company_id FROM {$prefix}assets a
                INNER JOIN {$prefix}maintenances m ON m.item_id = a.id AND m.item_type = ?
                WHERE m.id = {$events}.source_id
             ) WHERE source_type = ? AND company_id IS NULL",
            [Asset::class, Maintenance::class],
        );

        // User and CheckoutRequest events stay null. Users carry pivot
        // companies (no single value) and requests derive their
        // company from the requestable. Both fall through to the
        // null-company read-path semantics the controller applies.
    }

    public function down(): void
    {
        if (Schema::hasColumn('calendar_events', 'company_id')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropIndex('calendar_events_company_id_index');
                $table->dropColumn('company_id');
            });
        }
    }
};
