<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveOptionKeysFromSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            //
            if (Schema::hasColumn('settings', 'option_name')) {
                $table->dropColumn('option_name');
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
        Schema::table('Settings', function (Blueprint $table) {
            //
            $table->string('option_name')->nullable();
        });
    }
}
