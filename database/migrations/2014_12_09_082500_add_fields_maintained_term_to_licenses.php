<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsMaintainedTermToLicenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licenses', function ($table) {
            $table->date('termination_date')->nullable();
            $table->boolean('maintained')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenses', function ($table) {
            $table->dropColumn('termination_date');
            $table->dropColumn('maintained');
        });
    }
}
