<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('migrations', function (Blueprint $table) {
            // Add the id column to the migrations table if it doesn't yet have one
            if (! Schema::hasColumn('migrations', 'id')) {
                $table->increments('id');
            }
        });

        Schema::table('password_resets', function (Blueprint $table) {
            // Add the id column to the password_resets table if it doesn't yet have one
            if (! Schema::hasColumn('password_resets', 'id') && $this->notUsingSqlite()) {
                $table->increments('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('migrations', function (Blueprint $table) {
            if (Schema::hasColumn('migrations', 'id')) {
                $table->dropColumn('id');
            }
        });

        Schema::table('password_resets', function (Blueprint $table) {
            if (Schema::hasColumn('password_resets', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    private function notUsingSqlite()
    {
        return Schema::connection($this->getConnection())->getConnection()->getDriverName() !== 'sqlite';
    }
}
