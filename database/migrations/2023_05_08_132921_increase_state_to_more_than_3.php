<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseStateToMoreThan3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('state', 191)->nullable()->default(null)->change();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('state', 191)->nullable()->default(null)->change();
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
            $table->string('state', 3)->nullable()->default(null)->change();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('state', 32)->nullable()->default(null)->change();
        });
    }
}
