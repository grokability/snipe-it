<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ## Historical-row backfill heuristic
 *
 * 1. Column default is true. Every existing consumable starts in
 *    the pre-8.8.5 "every checkout = consumed" mode.
 *
 * 2. For each consumable that already has at least one row with
 *    consumed_at IS NOT NULL in consumables_users, flip its flag
 *    to false. That admin is actively using the outstanding-
 *    tracking workflow introduced in 8.8.4 and we must not stamp
 *    the still-outstanding rows out from under them.
 *
 * 3. For every consumable still flagged true after step 2,
 *    backfill consumed_at = created_at on all its outstanding
 *    pivot rows.
 *
 * Edge case: an admin who had just started using mark-consumed on
 * one consumable (one consumed row exists) will have ONLY that parent
 * flipped to false. Their other outstanding rows on that same
 * parent stay outstanding. That's the safer default: never
 * silently stamp rows the admin might have intentionally left
 * outstanding.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumables', function (Blueprint $table) {
            $table->boolean('auto_consume_on_checkout')->default(true)->after('min_amt');
        });

        // Step 2: any consumable with a mark-consumed row in its history
        // is on the outstanding-tracking workflow. Opt it out of the
        // default true so future checkouts and backfill below leave
        // its outstanding rows untouched.
        DB::table('consumables')
            ->whereIn('id', function ($query) {
                $query->select('consumable_id')
                    ->from('consumables_users')
                    ->whereNotNull('consumed_at')
                    ->distinct();
            })
            ->update(['auto_consume_on_checkout' => false]);

        // Step 3: backfill historical outstanding rows under parents
        // that stayed true.
        DB::table('consumables_users')
            ->whereNull('consumed_at')
            ->whereIn('consumable_id', function ($query) {
                $query->select('id')
                    ->from('consumables')
                    ->where('auto_consume_on_checkout', true);
            })
            ->update(['consumed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('consumables', function (Blueprint $table) {
            $table->dropColumn('auto_consume_on_checkout');
        });
    }
};
