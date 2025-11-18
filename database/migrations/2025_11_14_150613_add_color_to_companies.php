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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tag_color', )->after('name')->nullable()->default(null);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('tag_color', )->after('name')->nullable()->default(null);
        });

        Schema::table('manufacturers', function (Blueprint $table) {
            $table->string('tag_color', )->after('name')->nullable()->default(null);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('tag_color', )->after('name')->nullable()->default(null);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->string('tag_color', )->after('name')->nullable()->default(null);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->string('tag_color', )->after('name')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function ($table) {
            $table->dropColumn('tag_color');
        });

        Schema::table('categories', function ($table) {
            $table->dropColumn('tag_color');
        });

        Schema::table('manufacturers', function ($table) {
            $table->dropColumn('tag_color');
        });

        Schema::table('suppliers', function ($table) {
            $table->dropColumn('tag_color');
        });

        Schema::table('locations', function ($table) {
            $table->dropColumn('tag_color');
        });

        Schema::table('departments', function ($table) {
            $table->dropColumn('tag_color');
        });
    }
};
