<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLogoAndColorsToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('settings', function ($table) {
            $table->string('logo')->nullable()->default(null);
            $table->string('header_color')->nullable()->default(null);
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
        Schema::table('settings', function ($table) {
            $table->dropColumn('logo');
            $table->dropColumn('header_color');
        });
    }
}
