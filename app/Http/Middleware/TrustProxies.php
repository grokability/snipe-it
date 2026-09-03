<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as TrustedProxyMiddleware;
use Illuminate\Http\Request;

class TrustProxies extends TrustedProxyMiddleware
{
    /**
     * The trusted proxy headers computed from config('trustedproxy.headers').
     *
     * Overrides the parent's $headers property/headers() fallback so that an
     * empty or entirely invalid APP_TRUSTED_HEADERS results in trusting *no*
     * headers, rather than silently falling back to the framework's default
     * header set. See headers().
     *
     * @var int
     */
    protected int $headerBitmask = 0;

    public function __construct()
    {
        $header_bitmask = 0;

        foreach (explode(",", config('trustedproxy.headers')) as $header) {
            $header = trim($header);

            if (!$header) {
                continue;
            }

            try {
                $header_bitmask |= constant(Request::class . "::$header"); // once we get to PHP 8.3, this can become Request::{$header}
            } catch (\Throwable $e) {
                \Log::error("Error parsing APP_TRUSTED_HEADERS: " . $header . " is not a valid setting, ignoring");
            }
        }
        \Log::debug("Final header bitmask: $header_bitmask");

        $this->headerBitmask = $header_bitmask;
        // note, we do *not* need to also set the Proxies themselves since the middleware does that itself.
    }

    /**
     * Get the trusted headers.
     *
     * Deliberately does not fall back to the parent's hardcoded default
     * header set: an empty/unconfigured APP_TRUSTED_HEADERS must mean *no*
     * headers are trusted, not "trust everything".
     *
     * @return int
     */
    protected function headers()
    {
        return $this->headerBitmask;
    }
}