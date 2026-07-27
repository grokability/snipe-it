<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsActivated
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * Registered in both the `web` and `api` middleware groups. The web
     * group applied this from the start (the middleware was added to
     * terminate active sessions when a user's activated flag flips off).
     * The api group was missing it, so a deactivated user's already-issued
     * Passport token continued to authenticate and grant full access -
     * defeating "Activated" as an offboarding control and letting a
     * deactivated account with users.edit re-activate itself via the
     * API. This handler now returns the appropriate response for each
     * context: a JSON 401 for API/JSON clients, a session logout +
     * redirect-to-login for browser sessions.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If there is a user AND the user is NOT activated, send them to the login page
        // This prevents people who still have active sessions logged in and their status gets toggled
        // to inactive (aka unable to login)
        if (($request->user()) && (! $request->user()->isActivated())) {
            // API clients can't act on a redirect. Bearer tokens are
            // stateless so there's no session to Auth::logout(); this
            // request and every subsequent one from the same token fail
            // here until the account is re-activated. Generic
            // unauthorized message on purpose - do not confirm that the
            // token is otherwise valid and the account is specifically
            // deactivated, since that helps an attacker distinguish a
            // "known token, disabled account" case from an unknown
            // token. From the client's point of view this reads
            // identically to a rejected / expired token.
            if ($request->expectsJson()) {
                return response()->json(
                    Helper::formatStandardApiResponse('error', null, trans('general.unauthorized')),
                    Response::HTTP_UNAUTHORIZED,
                );
            }

            Auth::logout();

            return redirect()->guest('login');
        }

        return $next($request);
    }
}
