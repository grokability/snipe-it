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
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('validate_asset_name')->default('0');
            $table->char('asset_name_regex')->nullable()->default(null);
            $table->boolean('unique_asset_name')->default('0');
            $table->boolean('ignore_blank_asset_name')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn('validate_asset_name');
            $table->dropColumn('asset_name_regex');
            $table->dropColumn('unique_asset_name');
            $table->dropColumn('ignore_blank_asset_name');
        });
    }
};
