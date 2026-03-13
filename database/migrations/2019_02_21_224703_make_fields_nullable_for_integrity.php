<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFieldsNullableForIntegrity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('city')->nullable()->default(null)->change();
            $table->string('state')->nullable()->default(null)->change();
            $table->string('country')->nullable()->default(null)->change();
            $table->integer('user_id')->nullable()->default(null)->change();
            $table->string('address')->nullable()->default(null)->change();
            $table->string('address2')->nullable()->default(null)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->default(null)->change();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->default(null)->change();
        });

        Schema::table('status_labels', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->default(null)->change();
        });

        Schema::table('models', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->default(null)->change();
            $table->integer('manufacturer_id')->nullable()->default(null)->change();
            $table->integer('category_id')->nullable()->default(null)->change();
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->default(null)->change();
            $table->boolean('maintained')->nullable()->default(null)->change();
        });

        Schema::table('depreciations', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
