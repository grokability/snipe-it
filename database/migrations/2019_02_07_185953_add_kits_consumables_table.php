<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddKitsConsumablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('kits_consumables')) {
            Schema::create('kits_consumables', function ($table) {
                $table->increments('id');
                $table->integer('kit_id')->nullable()->default(null);
                $table->integer('consumable_id')->nullable()->default(null);
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
        if (Schema::hasTable('kits_consumables')) {
            Schema::drop('kits_consumables');
        }
    }
}
