<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAccessoriesUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('accessories_users', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->default(null);
            $table->integer('accessory_id')->nullable()->default(null);
            $table->integer('assigned_to')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('accessories', function ($table) {
            $table->integer('location_id')->nullable()->default(null);
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
        Schema::drop('accessories_users');

        Schema::table('accessories', function ($table) {
            $table->dropColumn('location_id');
        });
    }
}
