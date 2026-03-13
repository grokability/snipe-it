<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('snipeit:travisci-install', function () {
    if (! Setting::setupCompleted()) {
        $settings = new Setting();
        $settings->site_name = 'test-ci';
        $settings->alert_email = 'test@example.com';
        $settings->alerts_enabled = 1;
        $settings->brand = 1;
        $settings->locale = 'en';
        $settings->default_currency = 'USD';
        $settings->user_id = 1;
        $settings->email_domain = 'example.com';
        $settings->email_format = 'filastname';
        $settings->save();
    } else {
        $this->comment('Setup already ran');
    }
})->purpose('Travis-cli install script for unit tests');
