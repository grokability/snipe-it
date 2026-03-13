<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixOrderNumberInComponentsToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function ($table) {
            $table->string('order_number')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('components', function ($table) {
            $table->integer('order_number')->nullable()->default(null)->change();
        });
    }
}
