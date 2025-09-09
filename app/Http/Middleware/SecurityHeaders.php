<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    // See https://securityheaders.com/
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];

    public function handle($request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Ugh. Feature-Policy is dumb and clumsy and mostly irrelevant for Snipe-IT,
        // since we don't provide any way to IFRAME anything in in the first place.
        // There is currently no easy way to default ALL THE THINGS to 'none', but
        // security audits will still ding you if you don't have this header, even
        // though we don't allow IFRAMING in the first place.
        //
        // So for security and compliance sake, here we are. Sigh.
        //
        // See also:
        //           - https://developers.google.com/web/updates/2018/06/feature-policy
        //           - https://scotthelme.co.uk/a-new-security-header-feature-policy/
        //           - https://github.com/w3c/webappsec-feature-policy/issues/189

        $feature_policy[] = "accelerometer 'none'";
        $feature_policy[] = "autoplay 'none'";
        $feature_policy[] = "camera 'none'";
        $feature_policy[] = "display-capture 'none'";
        $feature_policy[] = "document-domain 'none'";
        $feature_policy[] = "encrypted-media 'none'";
        $feature_policy[] = "fullscreen 'none'";
        $feature_policy[] = "geolocation 'none'";
        $feature_policy[] = "sync-xhr 'none'";
        $feature_policy[] = "usb 'none'";
        $feature_policy[] = "xr-spatial-tracking 'none'";

        $feature_policy = implode(';', $feature_policy);
        $response->headers->set('Feature-Policy', $feature_policy);

        // Defaults to same-origin if REFERRER_POLICY is not set in the .env
        $response->headers->set('Referrer-Policy', config('app.referrer_policy'));

        // The .env var ALLOW_IFRAMING  defaults to false (which disallows IFRAMING)
        // if not present, but some unique cases require this to be enabled.
        // For example, some IT depts have IFRAMED Snipe-IT into their IT portal
        // for convenience so while it is normally disallowed, there is
        // an override that exists.

        if (config('app.allow_iframing') == false) {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        // This defaults to false to maintain backwards compatibility for
        // people who are not running Snipe-IT over TLS (shame, shame, shame!)
        // Seriously though, please run Snipe-IT over TLS. Let's Encrypt is free.
        // https://letsencrypt.org

        if (config('app.enable_hsts') === true) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // We have to exclude debug mode here because debugbar pulls from a CDN or two
        // and it will break things.

        if ((config('app.debug') != 'true') && (config('app.enable_csp') == 'true')) {
            $csp_policy[] = "default-src 'self'";
            $csp_policy[] = "style-src 'self' 'unsafe-inline'";
            $csp_policy[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval'";
            $csp_policy[] = "connect-src 'self'";
            $csp_policy[] = "object-src 'none'";
            $csp_policy[] = "font-src 'self' data:";
            $csp_policy[] = "img-src 'self' data: ".config('app.url').' '.config('app.additional_csp_urls').' '.env('PUBLIC_AWS_URL').' https://secure.gravatar.com http://gravatar.com maps.google.com maps.gstatic.com *.googleapis.com';
	          
            if (config('filesystems.disks.public.driver') == 's3') {
               $csp_policy[] = "img-src 'self' data:  ".config('filesystems.disks.public.url');
            }
            $csp_policy = join(';', $csp_policy);

            $response->headers->set('Content-Security-Policy', $csp_policy);
        }

        return $response;
    }

    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header) {
            header_remove($header);
        }
    }
}
