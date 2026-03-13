<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Enable2faFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function ($table) {
            $table->tinyInteger('two_factor_enabled')->nullable()->default(null);
        });

        Schema::table('users', function ($table) {
            $table->string('two_factor_secret', 32)->nullable()->default(null);
            $table->boolean('two_factor_enrolled')->default(0);
            $table->boolean('two_factor_optin')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn('two_factor_enabled');
        });

        Schema::table('users', function ($table) {
            $table->dropColumn('two_factor_secret');
            $table->dropColumn('two_factor_enrolled');
            $table->dropColumn('two_factor_optin');
        });
    }
}
