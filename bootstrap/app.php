<?php

use App\Http\Middleware\CheckColorSettings;
use App\Http\Middleware\CheckForDebug;
use App\Http\Middleware\CheckForSetup;
use App\Http\Middleware\CheckForTwoFactor;
use App\Http\Middleware\CheckLocale;
use App\Http\Middleware\CheckPermissions;
use App\Http\Middleware\CheckUserIsActivated;
use App\Http\Middleware\EnforceApiTwoFactorEnrollment;
use App\Http\Middleware\EnforceApiUserAgent;
use App\Http\Middleware\EnforceMcpScopeHandshake;
use App\Http\Middleware\EnsureOAuthClientApproved;
use App\Http\Middleware\IssueFreshApiTokenIfTwoFactorComplete;
use App\Http\Middleware\LogAuthedUserHeader;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RecoverLegacyPassportSession;
use App\Http\Middleware\RejectMcpOnlyTokensOnRestApi;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAPIResponseHeaders;
use App\Http\Middleware\SetPaginationDefaults;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;

$app = Application::configure(basePath: dirname(__DIR__))
    // Auto-discovers plain handle()-style listeners in app/Listeners (e.g. LogSuccessfulLogin,
    ->withEvents(true)
    ->withMiddleware(function (Middleware $middleware) {
        // --- Global stack ---
        // Keep Laravel's default global middleware, including TrustProxies and
        // TrimStrings, so future framework additions are inherited automatically.
        // Allow health probes by pod IP or loopback without rejecting their Host header.
        $middleware->trustHosts(fn () => config('app.allow_insecure_hosts') || request()->is('health') ? ['.*'] : []);
        $middleware->append([
            CheckForSetup::class,
            SecurityHeaders::class,
            PreventBackHistory::class,
            // Blocks OAuth authorize + token for pending/rejected clients.
            EnsureOAuthClientApproved::class,
        ]);

        $middleware->authenticateSessions();

        $middleware->web(
            append: [
                CheckForDebug::class,
                CheckLocale::class,
                CheckUserIsActivated::class,
                CheckForTwoFactor::class,
                IssueFreshApiTokenIfTwoFactorComplete::class,
                CheckColorSettings::class,
            ],
            replace: [
                StartSession::class => RecoverLegacyPassportSession::class,
            ],
        );

        $middleware->api(
            prepend: [
                // oidc first: Passport's TokenGuard clears the Authorization header
                // when it fails, so it must run AFTER the OIDC guard (which never
                // clears it) -- otherwise an OIDC bearer would be wiped before the
                // oidc guard sees it. A Passport token falls through cleanly.
                'auth:oidc,api',
                CheckUserIsActivated::class,
                EnforceApiTwoFactorEnrollment::class,
                EnforceApiUserAgent::class,
                CheckLocale::class,
                LogAuthedUserHeader::class,
                SetPaginationDefaults::class,
            ],
            append: [
                // Rejects tokens scoped for MCP-only use against /api/v1 routes.
                RejectMcpOnlyTokensOnRestApi::class,
            ],
        );

        $middleware->alias([
            'authorize' => CheckPermissions::class,
            'api-throttle' => SetAPIResponseHeaders::class,
            'mcp.scope.handshake' => EnforceMcpScopeHandshake::class,
        ]);
    })
    ->create();

// Keep Snipe-IT's own Console Kernel (custom schedule + command/route loading) and
// Exception Handler (SCIM, 2FA, API-JSON rendering) instead of Laravel's closure-based
// withSchedule()/withExceptions() APIs - there's no "silently missing default" risk for
// either of these the way there was for HTTP middleware, so rewriting them adds risk
// without fixing anything.
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

return $app;
