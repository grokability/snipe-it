<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToNewActivityReportFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->index('action_type');
            $table->index('remote_ip');
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
            $table->dropIndex(['action_type']);
            $table->dropIndex(['remote_ip']);
        });
    }
}
