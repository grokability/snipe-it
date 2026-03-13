<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleAuthToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('google_login')->nullable()->default(0);
            $table->string('google_client_id')->nullable()->default(null);
            $table->string('google_client_secret')->nullable()->default(null);
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
            $table->dropColumn('google_login');
            $table->dropColumn('google_client_id');
            $table->dropColumn('google_client_secret');

        });
    }
}
