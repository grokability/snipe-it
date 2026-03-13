<?php

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
        Schema::table('depreciations', function (Blueprint $table) {
            $table->string('depreciation_type')->after('depreciation_min')->default('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depreciations', function (Blueprint $table) {
            $table->dropColumn('depreciation_type');
        });
    }
};
