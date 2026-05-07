<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
            $response->headers->set('X-API-Token-Name', $request->user()?->token()?->name);
            $response->headers->set('X-API-Token-ID', $request->user()?->token()?->id);
        }

        return $response;
    }
}
