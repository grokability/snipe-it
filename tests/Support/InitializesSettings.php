<?php

namespace Tests\Support;

use App\Models\Setting;

trait InitializesSettings
{
    protected Settings $settings;

    public function initializeSettings()
    {
        if (app()->environment('testing')) {
            if (class_exists(\Database\Seeders\DemoSeeder::class)) {
                putenv('SEED_DEMO=false');
                config(['snipeit.seed_demo' => false]);
            }
        }

        $this->settings = Settings::initialize();

        $this->beforeApplicationDestroyed(fn() => Setting::$_cache = null);
    }
}