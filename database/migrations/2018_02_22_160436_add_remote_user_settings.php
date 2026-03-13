<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemoteUserSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('login_remote_user_enabled')->default(0);
            $table->boolean('login_common_disabled')->default(0);
            $table->string('login_remote_user_custom_logout_url')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('login_remote_user_enabled');
            $table->dropColumn('login_common_disabled');
            $table->dropColumn('login_remote_user_custom_logout_url');
        });
    }
}
