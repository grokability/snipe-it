<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingAllowUserSkin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update the users table
        Schema::table('settings', function ($table) {
            $table->boolean('allow_user_skin')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Update the users table
        Schema::table('settings', function ($table) {
            $table->dropColumn('allow_user_skin');
        });
    }
}
