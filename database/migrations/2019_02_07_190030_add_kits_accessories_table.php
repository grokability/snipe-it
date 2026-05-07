<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddKitsAccessoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('kits_accessories')) {
            Schema::create('kits_accessories', function ($table) {
                $table->increments('id');
                $table->integer('kit_id')->nullable()->default(null);
                $table->integer('accessory_id')->nullable()->default(null);
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
