<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoSessionStore
{
    protected $except = [
        'health',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                config()->set('session.driver', 'array');
            }
        }

        return $next($request);
    }
}
