<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        Setting::truncate();
        $settings = new Setting;
        $settings->per_page = 20;
        $settings->site_name = 'Snipe-IT Demo';
        $settings->auto_increment_assets = 1;
        $settings->logo = 'snipe-logo.png';
        $settings->alert_email = 'service@snipe-it.io';
        $settings->header_color = null;
        $settings->label2_2d_type = 'QRCODE';
        $settings->default_currency = 'USD';
        $settings->brand = 2;
        // Forumsys hosts a free public read-only LDAP directory
        // (ldap://ldap.forumsys.com) that's handy for exercising the
        // LDAP wizard against a real server without standing up your
        // own. Pre-filling the fields lets devs (and the demo site)
        // click through Admin > Settings > LDAP and use the Test
        // Bind / Test Find User previews end to end. LDAP is enabled
        // so the wizard's return-visitor branch unlocks all steps
        // for demo visitors. The login controller separately skips
        // the LDAP auth branch when the app is in demo mode
        // (config('app.lock_passwords')), so demo logins never
        // actually hit Forumsys.
        $settings->ldap_enabled = '1';
        $settings->ldap_server = 'ldap://ldap.forumsys.com';
        $settings->is_ad = false;
        $settings->ldap_tls = false;
        $settings->ldap_server_cert_ignore = false;
        $settings->ldap_client_tls_cert = null;
        $settings->ldap_client_tls_key = null;
        $settings->ldap_basedn = 'dc=example,dc=com';
        $settings->ldap_uname = 'cn=read-only-admin,dc=example,dc=com';
        $settings->ldap_pword = Crypt::encrypt('password');
        $settings->ldap_filter = '';
        $settings->ldap_auth_filter_query = 'uid=';
        $settings->ldap_username_field = 'uid';
        $settings->ldap_fname_field = 'cn';
        $settings->ldap_lname_field = 'sn';
        $settings->ldap_email = 'mail';
        $settings->full_multiple_companies_support = 0;
        $settings->label2_1d_type = 'C128';
        $settings->skin = 'blue';
        $settings->email_domain = 'example.org';
        $settings->email_format = 'filastname';
        $settings->username_format = 'filastname';
        $settings->date_display_format = 'D M d, Y';
        $settings->time_display_format = 'g:iA';
        $settings->thumbnail_max_h = '30';
        $settings->locale = 'en-US';
        $settings->version_footer = 'on';
        $settings->support_footer = 'on';
        $settings->pwd_secure_min = '8';
        $settings->default_avatar = 'default.png';
        $settings->save();

        if ($user = User::where('username', '=', 'admin')->first()) {
            $user->locale = 'en-US';
            $user->enable_sounds = 1;
            $user->enable_confetti = 1;
            $user->save();
        }

        // Copy the logos from the img/demo directory
        Storage::disk('local_public')->put('snipe-logo.png', file_get_contents(public_path('img/demo/snipe-logo.png')));
        Storage::disk('local_public')->put('snipe-logo-lg.png', file_get_contents(public_path('img/demo/snipe-logo-lg.png')));
    }
}
