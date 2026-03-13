<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ImportLdapSettings extends Migration
{
    /**
     * Migration to pull in LDAP settings from ldap.config into database.
     *
     * @return void
     */
    public function up()
    {
        $settings = \App\Models\Setting::first();

        // Only update the settings record if there IS an LDAP Config
        // AND the Settings table doesn't already have LDAP settings in it

        if ((config('ldap.url')) && ($settings) && ($settings->ldap_server)) {
            $settings->ldap_enabled = 1;
            $settings->ldap_server = config('ldap.url');
            $settings->ldap_uname = config('ldap.username');
            $settings->ldap_pword = Crypt::encrypt(config('ldap.password'));
            $settings->ldap_basedn = config('ldap.basedn');
            $settings->ldap_filter = config('ldap.filter');
            $settings->ldap_username_field = config('ldap.result.username');
            $settings->ldap_lname_field = config('ldap.result.last.name');
            $settings->ldap_fname_field = config('ldap.result.first.name');
            $settings->ldap_auth_filter_query = config('ldap.authentication.filter.query');
            $settings->ldap_version = config('ldap.version');
            $settings->ldap_active_flag = config('ldap.result.active.flag');
            $settings->ldap_emp_num = config('ldap.result.emp.num');
            $settings->ldap_email = config('ldap.result.email');

            // Save the imported settings
            if ($settings->save()) {
                echo 'LDAP settings imported into database'."\n";

                // Copy the old LDAP config file to prevent any future confusion
                if (@copy(app_path().'/config/'.app()->environment().'/ldap.php', app_path().'/config/'.app()->environment().'/deprecated.ldap.php')) {
                    if (@unlink(app_path().'/config/'.app()->environment().'/ldap.php')) {
                        echo 'Original LDAP file archived to '.app_path().'/config/'.app()->environment().'/deprecated.ldap.php'."\n";
                    } else {
                        echo 'Could not archive LDAP config file'."\n";
                    }
                } else {
                    echo 'Could not archive LDAP config file'."\n";
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (@copy(app_path().'/config/'.app()->environment().'/deprecated.ldap.php', app_path().'/config/'.app()->environment().'/ldap.php')) {
            echo 'Un-archived LDAP config file'."\n";
            @unlink(app_path().'/config/'.app()->environment().'/deprecated.ldap.php');
        } else {
            echo 'Could not un-archive LDAP config file. Manually rename it instead.'."\n";
        }
    }
}
