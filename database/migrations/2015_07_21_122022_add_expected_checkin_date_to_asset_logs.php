<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddExpectedCheckinDateToAssetLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_logs', function ($table) {
            $table->date('expected_checkin')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            $table->dropColumn('expected_checkin');
        });
    }
}
