<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemoteIpAndActionSourceToActionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->string('action_source')->nullable()->default(null);
            $table->ipAddress('remote_ip')->nullable()->default(null);
            $table->string('user_agent')->nullable()->default(null);
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
            if (Schema::hasColumn('action_logs', 'action_source')) {
                $table->dropColumn('action_source');
            }
            if (Schema::hasColumn('action_logs', 'remote_ip')) {
                $table->dropColumn('remote_ip');
            }
            if (Schema::hasColumn('action_logs', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });
    }
}
