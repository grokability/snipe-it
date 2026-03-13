<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Closure;

class CheckForTwoFactor
{
    /**
     * Routes to ignore for Two Factor Auth
     */
    public const IGNORE_ROUTES = ['two-factor', 'two-factor-enroll', 'setup', 'logout'];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Skip the logic if the user is on the two factor pages or the setup pages

        // TODO - what we have below only works because our ROUTE uri's look _exactly_ like the route *names*.
        // The problem is that, in the new(-ish) Laravel routing system, the route-name doesn't match if the route _verb_ is wrong.
        // so we can have a blade that POST's to a route('two-factor') - but that route *name* is only matched when the method is GET
        // because we attached the name to the GET, not to the POST (as route names *SHOULD* be unique in Laravel)
        // there has got to be a better way to do this, but this is the best I could come up with for now.
        if (in_array($request->route()->getName(), self::IGNORE_ROUTES) || in_array($request->route()->uri(), self::IGNORE_ROUTES)) {
            return $next($request);
        }

        // Two-factor is enabled (either optional or required)
        if ($settings = Setting::getSettings()) {
            if (Auth::check() && ($settings->two_factor_enabled != '')) {
                // This user is already 2fa-authed
                if ($request->session()->get('2fa_authed')==auth()->id()) {
                    return $next($request);
                }

                // Two-factor is optional and the user has NOT opted in, let them through
                if (($settings->two_factor_enabled == '1') && (auth()->user()->two_factor_optin != '1')) {
                    return $next($request);
                }

                // Otherwise make sure they're enrolled and show them the 2FA code screen
                if ((auth()->user()->two_factor_secret != '') && (auth()->user()->two_factor_enrolled == '1')) {
                    return redirect()->route('two-factor')->with('info', trans('auth/message.two_factor.enter_two_factor_code'));
                }

                return redirect()->route('two-factor-enroll')->with('success', trans('auth/message.two_factor.please_enroll'));
            }
        }

        return $next($request);
    }
}
