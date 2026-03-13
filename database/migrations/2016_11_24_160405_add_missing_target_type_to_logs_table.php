<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AddMissingTargetTypeToLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get list of action logs with a target id but not a target type.  This fixes missing target_type in accept_asset
        DB::table('action_logs')->where('target_type', null)->where(function ($query) {
            $query->where('action_type', 'accepted')
            ->orWhere('action_type', 'declined');
        })->update(['target_type'=> \App\Models\User::class]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to do.
    }
}
