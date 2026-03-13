<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldCustomFieldset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_field_custom_fieldset', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('custom_field_id');
            $table->integer('custom_fieldset_id');
            $table->integer('order');
            $table->boolean('required');
            $table->engine = 'InnoDB';
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('custom_field_custom_fieldset');
    }
}
