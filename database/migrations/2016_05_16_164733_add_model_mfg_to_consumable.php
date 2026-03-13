<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddModelMfgToConsumable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumables', function (Blueprint $table) {
            $table->integer('model_no')->nullable()->default(null);
            $table->integer('manufacturer_id')->nullable()->default(null);
            $table->string('item_no')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumables', function ($table) {
            $table->dropColumn(
                'model_no',
                'manufacturer_id',
                'item_no'
            );
        });
    }
}
