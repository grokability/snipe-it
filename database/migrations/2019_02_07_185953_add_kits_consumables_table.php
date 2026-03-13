<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKitsConsumablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('kits_consumables')) {
            Schema::create('kits_consumables', function ($table) {
                $table->increments('id');
                $table->integer('kit_id')->nullable()->default(NULL); 
                $table->integer('consumable_id')->nullable()->default(NULL);    
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
