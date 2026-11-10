<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wrapper around Laravel Passport's CreateFreshApiToken that refuses to
 * mint the `snipeit_passport_token` cookie for a session that hasn't
 * cleared 2FA. Without this, a password-only session that landed on
 * /two-factor (in CheckForTwoFactor::IGNORE_ROUTES) would still get the
 * cookie issued by the web middleware group, giving it session-based
 * access to the API and the personal-access-token endpoints.
 *
 * See CheckForTwoFactor::isComplete() for the actual rule.
 */
class IssueFreshApiTokenIfTwoFactorComplete extends CreateFreshApiToken
{
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (! CheckForTwoFactor::isComplete($request)) {
            return $next($request);
        }

        return parent::handle($request, $next, $guard);
    }
}
