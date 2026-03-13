<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;

class CheckForSetup
{

    protected $except = [
        '_debugbar*',
        'health'
    ];

    public function handle($request, Closure $next, $guard = null)
    {

        /**
         * Skip this middleware for the debugbar and health check
         */
        if ($request->is($this->except))  {
            return $next($request);
        }

        if (Setting::setupCompleted()) {
            if ($request->is('setup*')) {
                return redirect(config('app.url'));
            } else {
                return $next($request);
            }
        } else {
            if (! ($request->is('setup*')) && ! ($request->is('.env'))) {
                return redirect(config('app.url').'/setup');
            }

            return $next($request);
        }
    }
}
