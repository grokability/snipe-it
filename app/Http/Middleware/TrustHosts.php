<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as BaseTrustHosts;
use Illuminate\Http\Request;

/**
 * Skip Host-header validation for /health so Kubernetes, Docker,
 * and load-balancer probes that connect by pod IP or loopback don't
 * get HTTP 400.
 */
class TrustHosts extends BaseTrustHosts
{
    public function handle(Request $request, $next)
    {
        if ($request->is('health')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
