<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomFieldsets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_fieldsets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->engine = 'InnoDB';
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('custom_fieldsets');
    }
}
