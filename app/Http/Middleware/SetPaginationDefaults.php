<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetPaginationDefaults
{
    public function handle(Request $request, Closure $next)
    {
        $limit = config('app.max_results');
        $intLimit = intval($request->input('limit'));

        if (abs($intLimit) > 0 && $intLimit <= config('app.max_results')) {
            $limit = abs($intLimit);
        }

        app()->instance('api_limit_value', $limit);

        if ($request->filled('page') && ! $request->filled('offset')) {
            $page = max(1, intval($request->input('page')));
            $offset = ($page - 1) * $limit;
        } else {
            $offset = intval($request->input('offset'));
            $page = $limit > 0 ? (int) floor($offset / $limit) + 1 : 1;
        }

        app()->instance('api_offset_value', $offset);
        app()->instance('api_current_page', $page);

        return $next($request);
    }
}
