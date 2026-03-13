<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionDateToActionlog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->datetime('action_date')->nullable()->default(null);
        });

        $results = DB::table('action_logs')->select('created_at', 'id')->where('action_type', 'checkout')->orWhere('action_type', 'checkin from')->get();

        foreach ($results as $result) {
            $update = DB::update('update '.DB::getTablePrefix().'action_logs set action_date=? where id=?', [$result->created_at, $result->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropColumn('action_date');
        });
    }
}
