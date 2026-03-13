<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEulaToActionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->text('stored_eula')->nullable()->default(null);
            $table->string('stored_eula_file')->nullable()->default(null);
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
            $table->dropColumn('stored_eula');
            $table->dropColumn('stored_eula_file');
        });
    }
}
