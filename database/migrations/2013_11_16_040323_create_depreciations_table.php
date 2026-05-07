<?php

use Illuminate\Database\Migrations\Migration;

class CreateDepreciationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('depreciations', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('months');
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->engine = 'InnoDB';
            // $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('depreciations');
    }
}
