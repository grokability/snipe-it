<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsLdapManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Updates the Settings Table
        Schema::table('settings', function (Blueprint $table) {
            $table->string('ldap_manager')->after('ldap_jobtitle')->nullable();
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
            $table->dropColumn('ldap_manager');
        });
    }
}
