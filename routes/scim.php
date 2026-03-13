<?php

use ArieTimmerman\Laravel\SCIMServer\RouteProvider as SCIMRouteProvider;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| SCIM Routes
|--------------------------------------------------------------------------
|
| These are the routes that we have to explicitly inject from the 
| laravel-scim-server project, which gives Snipe-IT SCIM support
|
*/

SCIMRouteProvider::publicRoutes(); // Make sure to load public routes *FIRST*

Route::middleware(['auth:api','authorize:superadmin'])->group(function () {
    SCIMRouteProvider::routes(
        [
            /*
             * If we leave public_routes as 'true', the public routes will load *now* and
             * be jammed into the same middleware that these private routes are loaded
             * with. That's bad, because these routes are *supposed* to be public.
             *
             * We loaded them a few lines above, *first*, otherwise the various
             * fallback routes in the library defined within these *secured* routes
             * will "take over" the above routes - and then you will end up losing
             * like 4 hours of your life trying to figure out why the public routes
             * aren't quite working right. Ask me how I know (BMW, 3/19/2022)
             */
            'public_routes' => false
        ]
    );

    SCIMRouteProvider::meRoutes();
}); // ->can('superuser');
