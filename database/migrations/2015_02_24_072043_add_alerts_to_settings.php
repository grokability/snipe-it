<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAlertsToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('settings', function ($table) {
            $table->string('alert_email')->nullable()->default(null);
            $table->boolean('alerts_enabled')->default(1);
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
        Schema::table('settings', function ($table) {
            $table->dropColumn('alert_email');
            $table->dropColumn('alerts_enabled');
        });
    }
}
