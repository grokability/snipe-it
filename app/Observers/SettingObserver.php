<?php

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    /**
     * Listen to the Setting saved event.
     *
     *
     * @return void
     */
    public function saved(Setting $setting)
    {
        Cache::forget(Setting::SETUP_CHECK_KEY);
    }
}
