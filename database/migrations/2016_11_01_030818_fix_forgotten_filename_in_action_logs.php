<?php

use App\Models\Actionlog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixForgottenFilenameInActionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $logs = DB::table('asset_logs')->where('filename', '!=', null)->get();
            //
            foreach ($logs as $log) {
                $matching_action_log = Actionlog::where('item_id', $log->asset_id)
                                          ->where('created_at', $log->created_at)
                                          ->where('note', $log->note)
                                          ->where('filename', null)
                                          ->withTrashed()
                                          ->get()->first();

                if ($matching_action_log) {
                    $matching_action_log->filename = $log->filename;
                    $matching_action_log->save();
                } else {
                    echo "Couldn't find matching Action log row when trying to migrate".
                         "  filename from asset log:\n".
                         "LogDate{$log->created_at} LogForAsset:{$log->asset_id}".
                         "LogNote:{$log->note} \n";
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            //
        });
    }
}
