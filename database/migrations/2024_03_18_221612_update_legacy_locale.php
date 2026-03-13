<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLegacyLocale extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('locale', 10)->nullable()->default('en-US')->change();
        });

        Schema::table('settings', function (Blueprint $table) {
            //
            $table->string('locale', 10)->nullable()->default('en-US')->change();
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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('locale', 10)->nullable()->default(config('app.locale'))->change();
        });
        Schema::table('settings', function (Blueprint $table) {
            //
            $table->string('locale', 10)->nullable()->default(config('app.locale'))->change();
        });
    }
}
