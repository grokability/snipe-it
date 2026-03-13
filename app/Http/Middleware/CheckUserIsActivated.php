<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

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
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // If there is a user AND the user is NOT activated, send them to the login page
        // This prevents people who still have active sessions logged in and their status gets toggled
        // to inactive (aka unable to login)
        if (($request->user()) && (!$request->user()->isActivated())) {
            Auth::logout();
            return redirect()->guest('login');
        }

        return $next($request);

    }
}
