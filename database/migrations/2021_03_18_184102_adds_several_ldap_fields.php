<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsSeveralLdapFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('ldap_phone_field')->after('ldap_email')->nullable();
            $table->string('ldap_jobtitle')->after('ldap_phone_field')->nullable();
            $table->string('ldap_country')->after('ldap_jobtitle')->nullable();
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
            $table->dropColumn('ldap_phone_field');
            $table->dropColumn('ldap_jobtitle');
            $table->dropColumn('ldap_country');
        });
    }
}
