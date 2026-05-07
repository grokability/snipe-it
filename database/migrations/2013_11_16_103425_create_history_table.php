<?php

use Illuminate\Database\Migrations\Migration;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('history', function ($table) {
        //     $table->increments('id');
        //     $table->integer('checkedout_to')->nullable;
        //     $table->integer('location_id')->nullable;
        //     $table->timestamps();
        //     $table->integer('user_id')->nullable();
        //     $table->engine = 'InnoDB';
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::drop('history');
    }
}
