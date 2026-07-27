<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * completed_at was added in 2026_05_18_094309 as a TIMESTAMP. The
     * 2026_07_17_000001 migration moved start_date and completion_date
     * (now expected_completion_date) to DATETIME for consistency and to
     * dodge TIMESTAMP's 2038 range limit + session-timezone auto-
     * conversion, but skipped completed_at. This finishes that job.
     *
     * Values fit in both types, so no data transformation is required.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->change();
        });
    }
};
