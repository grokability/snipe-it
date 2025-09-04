<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use \App\Helpers\Helper;
use \App\Models\Setting;
use \App\Models\User;

class FixLanguageDirs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Update the settings table
         */
        $settings = Setting::getSettings();
        if (($settings) && ($settings->locale != '')) {
            DB::table('settings')->update(['locale' => Helper::mapLegacyLocale($settings->locale)]);
        }

        /**
         * Update the users table
         */
        $users = User::whereNotNull('locale')->get();
        // Skip the model in case the validation rules have changed
        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update(['locale' => Helper::mapLegacyLocale($user->locale)]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $settings = Setting::getSettings();
        if (($settings) && ($settings->locale != '')) {
            DB::table('settings')->update(['locale' => Helper::mapBackToLegacyLocale($settings->locale)]);
        }

        /**
         * Update the users table
         */
        $users = User::whereNotNull('locale')->whereNull('deleted_at')->get();
        // Skip the model in case the validation rules have changed
        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update(['locale' => Helper::mapBackToLegacyLocale($user->locale)]);
        }

    }
}
