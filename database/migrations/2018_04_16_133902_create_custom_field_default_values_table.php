<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomFieldDefaultValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('models_custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('asset_model_id');
            $table->integer('custom_field_id');
            $table->text('default_value')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('models_custom_fields');
    }
}
