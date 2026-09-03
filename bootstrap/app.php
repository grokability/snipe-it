<?php

use App\Http\Middleware\CheckColorSettings;
use App\Http\Middleware\CheckForDebug;
use App\Http\Middleware\CheckForSetup;
use App\Http\Middleware\CheckForTwoFactor;
use App\Http\Middleware\CheckLocale;
use App\Http\Middleware\CheckPermissions;
use App\Http\Middleware\CheckUserIsActivated;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnforceApiTwoFactorEnrollment;
use App\Http\Middleware\EnforceApiUserAgent;
use App\Http\Middleware\IssueFreshApiTokenIfTwoFactorComplete;
use App\Http\Middleware\LogAuthedUserHeader;
use App\Http\Middleware\NoSessionStore;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAPIResponseHeaders;
use App\Http\Middleware\SetPaginationDefaults;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

$app = Application::configure(basePath: dirname(__DIR__))
    // Auto-discovers plain handle()-style listeners in app/Listeners (e.g. LogSuccessfulLogin,
    // LogFailedLogin). Subscriber-pattern listeners (LogListener, FulfillCheckoutRequestListener,
    // CheckoutableListener, CheckoutablesCheckedOutInBulkListener) are unaffected - discovery has
    // no concept of subscribers - and stay registered via $subscribe in
    // App\Providers\EventServiceProvider (config/app.php), which is otherwise untouched.
    ->withEvents(false)
    ->withMiddleware(function (Middleware $middleware) {
        // --- Global stack ---
        // ValidatePathEncoding, InvokeDeferredCallbacks, *NOT* TrustProxies, HandleCors,
        // PreventRequestsDuringMaintenance, ValidatePostSize, ConvertEmptyStringsToNull
        // all come from Laravel's own defaults now, so future additions there show up
        // automatically. Only Snipe-IT's own additions are listed explicitly below.
        $middleware->trustHosts();
        $middleware->prepend(TrustProxies::class);
        // this was overridden, inherits from the parents but makes some changes.
        // to keep this change small enough, we keep it for now
        $middleware->trimStrings(except: [
            'current_password',
            'password',
            'password_confirmation',
        ]);

        // Order matters: NoSessionStore must run before StartSession (it may force
        // the array session driver for /health); CheckForSetup/CheckForDebug need
        // the session/auth state StartSession sets up. These run globally (not just
        // in the 'web' group) because /health uses Route::withoutMiddleware(['web'])
        // and still needs them.
        $middleware->append([
            NoSessionStore::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            CheckForSetup::class,
            CheckForDebug::class,
            SecurityHeaders::class,
            PreventBackHistory::class,
        ]);

        // --- Groups (explicit, not merged with Laravel's group defaults: Snipe-IT's
        // web/api groups intentionally diverge - e.g. StartSession/ShareErrorsFromSession
        // are global instead of web-group-only, and VerifyCsrfToken is a custom subclass
        // incompatible with the new validateCsrfTokens() helper) ---
        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            VerifyCsrfToken::class,
            CheckLocale::class,
            CheckUserIsActivated::class,
            CheckForTwoFactor::class,
            IssueFreshApiTokenIfTwoFactorComplete::class,
            CheckColorSettings::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            'auth:api',
            CheckUserIsActivated::class,
            EnforceApiTwoFactorEnrollment::class,
            EnforceApiUserAgent::class,
            CheckLocale::class,
            LogAuthedUserHeader::class,
            SetPaginationDefaults::class,
            SubstituteBindings::class,
        ]);

        $middleware->group('health', []);

        $middleware->alias([
            'auth' => Authenticate::class,
            'authorize' => CheckPermissions::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'throttle' => ThrottleRequests::class,
            'api-throttle' => SetAPIResponseHeaders::class,
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
