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
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['manager_id']);
            $table->index(['manager_id','deleted_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['manager_id']);
            $table->index(['manager_id','deleted_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['manager_id','deleted_at']);
            $table->index(['manager_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['manager_id','deleted_at']);
            $table->index(['manager_id']);
        });
    }
};
