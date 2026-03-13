<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateAcceptedAtToAcceptanceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('asset_logs', function ($table) {
            $table->integer('accepted_id')->nullable()->default(null);
        });

        $results = DB::table('asset_logs as invitation')->join('asset_logs as acceptance', function ($join) {
            $join->on('invitation.checkedout_to', '=', 'acceptance.checkedout_to');
            $join->on('invitation.asset_id', '=', 'acceptance.asset_id');
        })->select('invitation.id as invitation_id', 'acceptance.id as acceptance_id')
            ->where('invitation.action_type', 'checkout')->where('acceptance.action_type', 'accepted')->get();

        foreach ($results as $result) {
            $update = DB::update('update '.DB::getTablePrefix().'asset_logs set accepted_id=? where id=?', [$result->acceptance_id, $result->invitation_id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('asset_logs', function ($table) {
            $table->dropColumn('accepted_id');
        });
    }
}
