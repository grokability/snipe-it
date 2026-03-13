<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('action_logs', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('components', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('consumables', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->index(['company_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('consumables', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('components', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('accessories', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });
    }
}
