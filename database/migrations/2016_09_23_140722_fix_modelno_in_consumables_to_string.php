<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixModelnoInConsumablesToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumables', function ($table) {
            $table->string('model_no')->nullable()->default(null)->change();
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
            $table->integer('model_no')->nullable()->default(null)->change();
        });
    }
}
