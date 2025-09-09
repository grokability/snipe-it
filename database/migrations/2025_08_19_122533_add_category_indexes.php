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
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['deleted_at']);
        });
        Schema::table('accessories', function (Blueprint $table) {
            $table->index(['deleted_at','category_id']);
        });
        Schema::table('consumables', function (Blueprint $table) {
            $table->index(['deleted_at','category_id']);
        });
        Schema::table('components', function (Blueprint $table) {
            $table->index(['deleted_at','category_id']);
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->index(['deleted_at','category_id']);
        });
        Schema::table('models', function (Blueprint $table) {
            $table->index(['deleted_at','category_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('accessories', function (Blueprint $table) {
            $table->dropIndex(['deleted_at','category_id']);
        });
        Schema::table('consumables', function (Blueprint $table) {
            $table->dropIndex(['deleted_at','category_id']);
        });
        Schema::table('components', function (Blueprint $table) {
            $table->dropIndex(['deleted_at','category_id']);
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['deleted_at','category_id']);
        });
        Schema::table('models', function (Blueprint $table) {
            $table->dropIndex(['deleted_at','category_id']);
        });
    }
};
