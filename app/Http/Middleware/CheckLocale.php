<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use \App\Helpers\Helper;
use Illuminate\Support\Facades\Log;
class CheckLocale
{
    private function warn_legacy_locale($language, $source)
    {
        if ($language != Helper::mapLegacyLocale($language)) {
            Log::warning("$source $language and should be updated to be ".Helper::mapLegacyLocale($language));
        }
    }
    /**
     * Handle the locale for the user, default to settings otherwise.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        // Default app settings from config
        $language = config('app.locale');
        $this->warn_legacy_locale($language, "APP_LOCALE in .env is set to");

        if ($settings = Setting::getSettings()) {

            // User's preference
            if (($request->user()) && ($request->user()->locale)) {
                $language = $request->user()->locale;
                $this->warn_legacy_locale($language, "username ".$request->user()->username." (".$request->user()->id.") has a language");

            // App setting preference
            } elseif ($settings->locale != '') {
                $language = $settings->locale;
                $this->warn_legacy_locale($language, "App Settings is set to");
            }

        }
        
        app()->setLocale(Helper::mapLegacyLocale($language));
        return $next($request);
    }
}
