<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixBadLdapServerUrlForV5 extends Migration
{
    /**
     * Under v4 and previous versions of Snipe-IT, we permitted users to incorrectly specify LDAP URL's in their settings, and Snipe-IT
     * would silently permit that.
     *
     * v5's LDAP system is not so lenient, and requires either ldap:// or ldaps:// in front of the server's URL. This migration tries
     * to find misconfigured LDAP URL's and prepend 'ldap://' to them. (That's what we assumed if we *didn't* see ldaps://)
     */

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // UPDATE settings SET ldap_server = CONCAT('ldap://',ldap_server) WHERE ldap_server NOT LIKE 'ldap://%' AND ldap_server NOT LIKE 'ldaps://%'
        $settings = App\Models\Setting::where('ldap_server', 'not like', 'ldap://%')->where('ldap_server', 'not like', 'ldaps://%');
        foreach ($settings->get() as $setting) { // we don't formally support having multiple settings records, but just in case they come up...
            $setting->ldap_server = 'ldap://'.$setting->ldap_server;
            $setting->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Since previous versions supported ldap:// URL's just fine, we don't need to migrate these changes back out on rollback.
    }
}
