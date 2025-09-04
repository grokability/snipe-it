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
        if (!Schema::hasColumn('settings', 'manager_view_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('manager_view_enabled')
                    ->default(false)
                    ->comment('Allow managers to view assets assigned to their subordinates');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('settings', 'manager_view_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('manager_view_enabled');
            });
        }
    }
};
