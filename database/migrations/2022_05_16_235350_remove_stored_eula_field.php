<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Actionlog;

class RemoveStoredEulaField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $actionlog_eulas = Actionlog::whereNotNull('stored_eula_file')->get();

        foreach ($actionlog_eulas as $eula_file) {
            $eula_file->filename = $eula_file->stored_eula_file;
            $eula_file->save();
        }

        $actionlog_bad_action_type = Actionlog::where('item_id', '=', 0)->whereNull('target_type')->whereNull('action_type')->whereNull('target_type')->get();

        foreach ($actionlog_bad_action_type as $bad_action_type) {
            $bad_action_type->delete();
        }

        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropColumn('stored_eula_file');
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
            $table->string('stored_eula_file')->nullable()->default(null);
        });
    }
}
