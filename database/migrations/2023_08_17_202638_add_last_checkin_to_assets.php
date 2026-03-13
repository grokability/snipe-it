<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastCheckinToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dateTime('last_checkin')->after('last_checkout')->nullable();
        });

        DB::statement(
            "UPDATE " . DB::getTablePrefix() . "assets SET last_checkin=(SELECT MAX(" . DB::getTablePrefix() . "action_logs.action_date) FROM " . DB::getTablePrefix() . "action_logs WHERE item_type='App\\\Models\\\Asset' AND " . DB::getTablePrefix() . "action_logs.item_id=" . DB::getTablePrefix() . "assets.id AND " . DB::getTablePrefix() . "action_logs.action_type='checkin from')"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('last_checkin');
        });
    }
}
