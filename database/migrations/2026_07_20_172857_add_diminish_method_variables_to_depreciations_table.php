<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('depreciations', function (Blueprint $table) {
            $table->string('fiscal_year_start_month')->after('depreciation_min')->nullable();
            $table->float('rate_multiplier')->after('fiscal_year_start_month')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depreciations', function (Blueprint $table) {
            $table->dropColumn('fiscal_year_start_month');
            $table->dropColumn('rate_multiplier');
        });
    }
};
