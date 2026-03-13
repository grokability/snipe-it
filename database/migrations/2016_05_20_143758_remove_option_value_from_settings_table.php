<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveOptionValueFromSettingsTable extends Migration
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
            if (Schema::hasColumn('settings', 'option_value')) {
                $table->dropColumn('option_value');
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
        //
        Schema::table('settings', function (Blueprint $table) {
            $table->string('option_value')->nullable();
        });
    }
}
