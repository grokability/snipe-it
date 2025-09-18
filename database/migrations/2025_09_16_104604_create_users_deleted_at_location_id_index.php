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
        Schema::table('users', function (Blueprint $table) {
            // We are doing 'deleted_at' *first* here because that way this index can do double-duty -
            // handling queries for 'all undeleted users' as well as 'users who are deleted in this location'
            // and 'users who are not-deleted in this location'
            $table->index(['deleted_at','location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_at','location_id']);
        });
    }
};
