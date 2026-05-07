<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddKitsModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('kits_models')) {
            Schema::create('kits_models', function ($table) {
                $table->increments('id');
                $table->integer('kit_id')->nullable()->default(null);
                $table->integer('model_id')->nullable()->default(null);
                $table->integer('quantity')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('kits_models')) {
            Schema::drop('kits_models');
        }
    }
}
