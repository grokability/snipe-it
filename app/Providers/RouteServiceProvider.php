<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapApiRoutes();

            $this->mapWebRoutes();

            require base_path('routes/scim.php');
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
//            'namespace' => $this->namespace, //okay, I don't know what this means, but somehow this might be a problem for us?
        ], function ($router) {
            require base_path('routes/web/hardware.php');
            require base_path('routes/web/models.php');
            require base_path('routes/web/accessories.php');
            require base_path('routes/web/licenses.php');
            require base_path('routes/web/locations.php');
            require base_path('routes/web/consumables.php');
            require base_path('routes/web/fields.php');
            require base_path('routes/web/components.php');
            require base_path('routes/web/users.php');
            require base_path('routes/web/kits.php');
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'auth:api',
//            'namespace' => $this->namespace, // this might also be a problem? I don't really know :/
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * This ONLY fires on 429 responses.
     *
     * https://laravel.com/docs/8.x/routing#rate-limiting
     *
     * @return void
     */
    protected function configureRateLimiting()
    {

        // Rate limiter for API calls - this sends the correct API headers to show the user the remaining time they have to wait and gives them the 429 status code if they are throttled
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('app.api_throttle_per_minute'))->by(optional($request->user())->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->json([
                        'status' => 'error',
                        'messages' => 'Too many requests. Try again in '.$headers['Retry-After'].' seconds.',
                        'status_code' => 429,
                        'retryAfter' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // Rate limiter for forgotten password requests
        RateLimiter::for('forgotten_password', function (Request $request) {
            return Limit::perMinute(config('auth.password_reset.max_attempts_per_min'))->by(optional($request->user())->id ?: $request->ip());
        });

    }
}
