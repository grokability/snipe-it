<?php

use Illuminate\Database\Migrations\Migration;

class EditsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function ($table) {
            // $table->dropColumn('option_label');
            // $table->dropColumn('option_name');
            // $table->dropColumn('option_value');
            $table->integer('per_page')->default(20);
            $table->string('site_name', '100')->default('Snipe IT Asset Management');
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
    }
}
