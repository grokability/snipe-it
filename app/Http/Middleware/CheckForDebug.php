<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckForDebug
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        view()->share('debug_in_production', false);

        if (((Auth::check() && (auth()->user()->isSuperUser()))) && (app()->environment() == 'production') && (config('app.warn_debug') === true) && (config('app.debug') === true)) {
            view()->share('debug_in_production', true);
        }

        return $next($request);
    }
}
