<?php

use Illuminate\Database\Migrations\Migration;

class AddRequestableToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('assets', function ($table) {
            $table->tinyInteger('requestable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('assets', function ($table) {
            $table->dropColumn('requestable');
        });
    }
}
