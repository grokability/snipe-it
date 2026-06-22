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
        Schema::table('settings', function ($table) {
            $table->longText('checkin_confirm_text')->nullable()->default(null);
            $table->longText('checkin_confirm_checkbox_text')->nullable()->default(null);
            $table->boolean('checkin_confirm')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn('checkin_confirm_text');
            $table->dropColumn('checkin_confirm_checkbox_text');
            $table->dropColumn('checkin_confirm');
        });
    }
};
