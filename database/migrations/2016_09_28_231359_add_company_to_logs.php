<?php

use App\Models\Actionlog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCompanyToLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            //
            $table->integer('company_id')->nullable()->default(null);
        });

        $logs = Actionlog::with('item')->get();
        foreach ($logs as $log) {
            if ($log->item) {
                $log->company_id = $log->item->company_id;
                $log->save();
            } else {
                var_dump($log);
            }
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
            //
            $table->dropColumn('company_id');
        });
    }
}
