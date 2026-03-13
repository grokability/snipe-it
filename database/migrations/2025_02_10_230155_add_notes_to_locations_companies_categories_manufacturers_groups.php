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
            $table->text('notes')->nullable()->default(null);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->text('notes')->nullable()->default(null);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->text('notes')->nullable()->default(null);
        });

        Schema::table('manufacturers', function (Blueprint $table) {
            $table->text('notes')->nullable()->default(null);
        });

        Schema::table('permission_groups', function (Blueprint $table) {
            $table->text('notes')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('permission_groups', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
