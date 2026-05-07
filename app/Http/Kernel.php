<?php

namespace App\Http;

use App\Http\Middleware\AssetCountForSidebar;
use App\Http\Middleware\CheckColorSettings;
use App\Http\Middleware\CheckForDebug;
use App\Http\Middleware\CheckForSetup;
use App\Http\Middleware\CheckForTwoFactor;
use App\Http\Middleware\CheckLocale;
use App\Http\Middleware\CheckPermissions;
use App\Http\Middleware\CheckUserIsActivated;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\LogAuthedUserHeader;
use App\Http\Middleware\NoSessionStore;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAPIResponseHeaders;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        TrustProxies::class,
        NoSessionStore::class,
        PreventRequestsDuringMaintenance::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        CheckForSetup::class,
        CheckForDebug::class,
        ConvertEmptyStringsToNull::class,
        TrimStrings::class,
        SecurityHeaders::class,
        PreventBackHistory::class,
        HandleCors::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            VerifyCsrfToken::class,
            CheckLocale::class,
            CheckUserIsActivated::class,
            CheckForTwoFactor::class,
            CreateFreshApiToken::class,
            AssetCountForSidebar::class,
            CheckColorSettings::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
        ],

        'api' => [
            'auth:api',
            CheckLocale::class,
            LogAuthedUserHeader::class,
            SubstituteBindings::class,
        ],

        'health' => [

        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'authorize' => CheckPermissions::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'api-throttle' => SetAPIResponseHeaders::class,
        'health' => null,
    ];
}
