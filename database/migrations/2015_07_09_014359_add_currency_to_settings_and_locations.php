<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCurrencyToSettingsAndLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('default_currency', 10)->nullable()->default(null);
        });

        DB::table('settings')->update(['default_currency' => trans('general.currency')]);

        Schema::table('locations', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->default(null);
        });

        DB::table('locations')->update(['currency' => trans('general.currency')]);
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
            //
            $table->dropColumn('default_currency');
        });

        Schema::table('locations', function (Blueprint $table) {
            //
            $table->dropColumn('currency');
        });
    }
}
