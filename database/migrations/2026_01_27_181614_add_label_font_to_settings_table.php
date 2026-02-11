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
            $table->string('labels_font')->after('labels_display_sgutter')->default('freemono');
            $table->string('labels_value_font')->after('labels_display_sgutter')->default('freesans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('labels_font');
            $table->dropColumn('labels_value_font');
        });
    }
};
