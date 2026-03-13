<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Gate;

class CheckPermissions
{
    /**
     * Handle the ACLs for permissions.
     *
     * The $section variable is passed via the route middleware,
     * 'middleware' => [authorize:superadmin']
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $section
     * @return mixed
     */
    public function handle($request, Closure $next, $section = null)
    {
        if (Gate::allows($section)) {
            return $next($request);
        }

        return response()->view('layouts/basic', [
            'content' => view('errors/403'),
        ], 403);
    }
}
