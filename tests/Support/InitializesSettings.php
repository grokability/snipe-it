<?php

namespace Tests\Support;

use App\Models\Setting;

trait InitializesSettings
{
    protected Settings $settings;

    public function initializeSettings()
    {
        $this->settings = Settings::initialize();

        $this->beforeApplicationDestroyed(fn() => Setting::$_cache = null);
    }
}
