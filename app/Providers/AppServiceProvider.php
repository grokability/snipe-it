<?php

namespace App\Providers;

use App\Exceptions\SyncAdapterVendorException;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\Setting;
use App\Models\SnipeSCIMConfig;
use App\Models\User;
use App\Observers\AccessoryObserver;
use App\Observers\AssetModelObserver;
use App\Observers\AssetObserver;
use App\Observers\ComponentObserver;
use App\Observers\ConsumableObserver;
use App\Observers\LicenseObserver;
use App\Observers\LocationObserver;
use App\Observers\MaintenanceObserver;
use App\Observers\SettingObserver;
use App\Observers\UserObserver;
use App\View\Composers\ImpersonationBannerComposer;
use App\View\Composers\SidebarComposer;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Rollbar\Laravel\RollbarServiceProvider;

/**
 * This service provider handles setting the observers on models
 *
 * PHP version 5.5.9
 *
 * @version    v3.0
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        /**
         * This is a workaround for proxies/reverse proxies that don't always pass the proper headers.
         *
         * Here, we check if the APP_URL starts with https://, which we should always honor,
         * regardless of how well the proxy or network is configured.
         *
         * We'll force the https scheme if the APP_URL starts with https://, or if APP_FORCE_TLS is set to true.
         */
        if ((str_starts_with(config('app.url'), 'https://')) || config('app.force_tls')) {
            $url->forceScheme('https');
        }

        if (! config('app.allow_insecure_hosts')) {  // unless you set APP_ALLOW_INSECURE_HOSTS, you should PROHIBIT forging domain parts of URL via Host: headers
            $url_parts = parse_url(config('app.url'));
            if ($url_parts && array_key_exists('scheme', $url_parts) && array_key_exists('host', $url_parts)) { // check for the *required* parts of a bare-minimum URL
                URL::forceRootUrl(config('app.url'));
            } else {
                Log::error('Your APP_URL in your .env is misconfigured - it is: '.config('app.url').'. Many things will work strangely unless you fix it.');
            }
        }

        Paginator::useBootstrap();

        View::composer('layouts.default', SidebarComposer::class);
        View::composer('partials.impersonation-banner', ImpersonationBannerComposer::class);

        Schema::defaultStringLength(191);
        Accessory::observe(AccessoryObserver::class);
        Asset::observe(AssetObserver::class);
        AssetModel::observe(AssetModelObserver::class);
        Component::observe(ComponentObserver::class);
        Consumable::observe(ConsumableObserver::class);
        License::observe(LicenseObserver::class);
        Location::observe(LocationObserver::class);
        Maintenance::observe(MaintenanceObserver::class);
        Setting::observe(SettingObserver::class);
        User::observe(UserObserver::class);

        // Defense against sync-adapter base URLs pointing at the vendor's
        // web console instead of their API. A wrong URL commonly returns
        // 200 OK with an SPA shell (text/html), which slips past
        // ->throw() and decodes to an empty array in adapter clients,
        // producing a silent "Sync complete. 0 hosts, 0 errors" flash
        // instead of a visible failure. Adapter clients chain this
        // after ->throw() before ->json() so a wrong-URL response
        // surfaces as a real error the admin can act on.
        HttpClientResponse::macro('throwIfNotJson', function () {
            /** @var HttpClientResponse $this */
            $contentType = $this->header('Content-Type');
            if (! str_contains(strtolower($contentType), 'json')) {
                $received = $contentType !== '' ? '"'.$contentType.'"' : 'a response with no Content-Type header';
                throw new SyncAdapterVendorException(
                    "Expected a JSON response, got {$received}. Verify the adapter Base URL points at the vendor API, not their web console or dashboard."
                );
            }

            return $this;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend(
            \Illuminate\Console\Scheduling\ScheduleRunCommand::class,
            fn ($command, $app) => $app->make(\App\Console\Scheduling\ShelllessScheduleRunCommand::class),
        );

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // Only load rollbar if there is a rollbar key and the app is in production
        if (($this->app->environment('production')) && (config('logging.channels.rollbar.access_token'))) {
            $this->app->register(RollbarServiceProvider::class);
        }

        $this->app->singleton('ArieTimmerman\Laravel\SCIMServer\SCIMConfig', SnipeSCIMConfig::class); // this overrides the default SCIM configuration with our own

    }
}
