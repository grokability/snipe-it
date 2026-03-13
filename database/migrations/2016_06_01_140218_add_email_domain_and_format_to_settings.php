<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEmailDomainAndFormatToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('email_domain')->nullable()->default(null);
            $table->string('email_format')->nullable()->default('filastname');
            $table->string('username_format')->nullable()->default('filastname');
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
            $table->dropColumn(
                'email_domain',
                'email_format',
                'username_format'
            );
        });
    }
}
