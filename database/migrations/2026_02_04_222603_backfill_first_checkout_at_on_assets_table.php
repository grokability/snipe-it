<?php

use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $batchSize = 500;
        $minId = (int)DB::table('assets')->min('id');
        $maxId = (int)DB::table('assets')->max('id');

        if (!$minId || !$maxId) {
            return;
        }

        for ($start = $minId; $start <= $maxId; $start += $batchSize) {
            $end = $start + $batchSize - 1;

            DB::update("
                UPDATE assets asset
                SET asset.first_checkout_at = (
                    SELECT MIN(log.created_at)
                    FROM action_logs log
                    WHERE log.item_type = 'App\\\\Models\\\\Asset'
                      AND log.action_type = 'checkout'
                      AND log.item_id = asset.id
                )
                WHERE asset.id BETWEEN {$start} AND {$end}
                  AND asset.first_checkout_at IS NULL
            ");
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('assets')->update(['first_checkout_at' => null]);
    }
};
