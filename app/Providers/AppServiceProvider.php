<?php

namespace App\Providers;

use App\Exceptions\SyncAdapterVendorException;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Setting;
use App\Models\SnipeSCIMConfig;
use App\Observers\AssetObserver;
use App\Observers\MaintenanceObserver;
use App\Observers\SettingObserver;
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
use Laravel\Passport\Passport;
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

        Passport::enablePasswordGrant();

        // Route OAuth consent to the MCP-specific view only when the
        // authorization request actually targets an MCP scope.
        Passport::authorizationView(function (array $parameters) {
            $requestsMcpScope = collect($parameters['scopes'] ?? [])
                ->contains(fn ($scope) => str_starts_with($scope->id, 'mcp:'));

            return view($requestsMcpScope ? 'mcp.authorize' : 'passport.authorize', $parameters);
        });

        if (! config('app.allow_insecure_hosts')) {  // unless you set APP_ALLOW_INSECURE_HOSTS, you should PROHIBIT forging domain parts of URL via Host: headers
            $url_parts = parse_url(config('app.url'));
            if ($url_parts && array_key_exists('scheme', $url_parts) && array_key_exists('host', $url_parts)) { // check for the *required* parts of a bare-minimum URL
                URL::forceRootUrl(config('app.url'));
            } else {
                Log::error('Your APP_URL in your .env is misconfigured - it is: '.config('app.url').'. Many things will work strangely unless you fix it.');
            }
        }

        Paginator::useBootstrap();

        $this->mergeMcpTrustedListsFromSettings();

        View::composer('layouts.default', SidebarComposer::class);
        View::composer('partials.impersonation-banner', ImpersonationBannerComposer::class);

        Schema::defaultStringLength(191);

        Asset::observe(AssetObserver::class);
        Maintenance::observe(MaintenanceObserver::class);
        Setting::observe(SettingObserver::class);

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

    /**
     * Merge the admin-configured MCP trusted lists from the settings
     * table into the mcp config so laravel/mcp's OAuthRegisterController
     * reads a unified view (hardcoded safe defaults + admin additions)
     * without a config:cache rebuild when the admin updates the
     * Settings -> MCP page.
     *
     * Guarded against (1) the settings table not yet existing on a
     * fresh install pre-migration and (2) the mcp_trusted_* columns
     * not yet existing on an upgrading install between
     * composer-update and migrate. Either case silently falls back to
     * the config-file defaults, so a half-upgraded install still has
     * a safe baseline.
     */
    private function mergeMcpTrustedListsFromSettings(): void
    {
        try {
            $settings = Setting::getSettings();
        } catch (\Throwable) {
            return;
        }
        if ($settings === null) {
            return;
        }

        $schemes = $this->splitTrustedList($settings->mcp_trusted_custom_schemes ?? null);
        if ($schemes !== []) {
            config([
                'mcp.custom_schemes' => array_values(array_unique(array_merge(
                    (array) config('mcp.custom_schemes', []),
                    $schemes,
                ))),
            ]);
        }

        $domains = $this->splitTrustedList($settings->mcp_trusted_redirect_domains ?? null);
        if ($domains !== []) {
            config([
                'mcp.redirect_domains' => array_values(array_unique(array_merge(
                    (array) config('mcp.redirect_domains', []),
                    $domains,
                ))),
            ]);
        }
    }

    /**
     * Normalize a newline-separated textarea value into a trimmed, non-
     * empty string list. Commented lines starting with # are skipped so
     * admins can document why they trust a given entry inline.
     *
     * @return array<int, string>
     */
    private function splitTrustedList(?string $raw): array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            $out[] = $trimmed;
        }

        return $out;
    }
}
