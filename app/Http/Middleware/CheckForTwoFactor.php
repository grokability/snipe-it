<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckForTwoFactor
{
    /**
     * Routes to ignore for Two Factor Auth
     */
    public const IGNORE_ROUTES = ['two-factor', 'two-factor-enroll', 'setup', 'logout'];

    /**
     * Whether the *session* attached to this request has cleared 2FA — i.e.
     * the user is allowed to act beyond the password+2FA-prompt screens.
     *
     * Sharing this check lets other middleware (the Passport cookie issuer)
     * and the API-token endpoints refuse to mint or expose tokens for a
     * password-only session that never entered a 2FA code. Without it a
     * session that landed on /two-factor (which is in IGNORE_ROUTES) could
     * pick up the Passport cookie, hit POST /api/v1/account/personal-access-
     * tokens and walk away with a long-lived bearer token.
     */
    public static function isComplete(Request $request): bool
    {
        if (! Auth::check()) {
            return true;
        }

        $settings = Setting::getSettings();
        if (! $settings) {
            return true;
        }

        // Normalize to the only two "on" values (optional/required). Anything
        // else — '', '0', null, the integer 0 SQLite occasionally hands back
        // for an empty tinyInteger column — counts as disabled. Loose `== ''`
        // would miscount '0' as enabled under PHP 8's stricter comparison
        // rules and trigger spurious 403s, which is how this manifested on
        // CI's SQLite even though local SQLite stored the value as ''.
        $mode = (string) $settings->two_factor_enabled;
        if ($mode !== '1' && $mode !== '2') {
            return true;
        }

        // 2FA is optional and this user has not opted in.
        if ($mode === '1' && auth()->user()->two_factor_optin != '1') {
            return true;
        }

        // 2FA required (or opted in): session must carry the authed marker
        // set by Auth\TwoFactorAuthController after a valid code is entered.
        return $request->hasSession()
            && $request->session()->get('2fa_authed') == auth()->id();
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
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
                if ($request->session()->get('2fa_authed') == auth()->id()) {
                    return $next($request);
                }

                // Two-factor is optional and the user has NOT opted in, let them through
                if (($settings->two_factor_enabled == '1') && (auth()->user()->two_factor_optin != '1')) {
                    return $next($request);
                }

                redirect()->setIntendedUrl(url()->full()); // save the 'current' URL so we can send the user back to it?
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
