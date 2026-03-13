<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKitsAccessoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('kits_accessories')) {
            Schema::create('kits_accessories', function ($table) {
                $table->increments('id');
                $table->integer('kit_id')->nullable()->default(NULL); 
                $table->integer('accessory_id')->nullable()->default(NULL);    
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
        if (Schema::hasTable('kits_accessories')) {
            Schema::drop('kits_accessories');
        }
    }
}
