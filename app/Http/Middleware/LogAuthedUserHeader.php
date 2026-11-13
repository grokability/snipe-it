<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\AccessToken;

class LogAuthedUserHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $response = $next($request);

        if ((config('app.authorized_user_header') === true) && ($request->bearerToken() != '')) {
            $response->headers->set('X-API-User-ID', auth()?->id());

            $token = $request->user()?->token();
            if ($token instanceof AccessToken) {
                $response->headers->set('X-API-Token-Name', $token->name);
                $response->headers->set('X-API-Token-ID', $token->id);
            }
        }

        return $response;
    }
}
