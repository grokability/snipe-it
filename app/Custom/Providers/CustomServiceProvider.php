<?php

namespace App\Custom\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * This is the SINGLE entry point for all FOM (Fabrika Operasyon Merkezi)
     * customizations. Only config/app.php references this provider.
     */
    public function register(): void
    {
        $this->app->register(FomEventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerSidebarComposer();
    }

    /**
     * Load custom web and API routes under /fom and /api/v1/fom prefixes.
     */
    protected function loadRoutes(): void
    {
        $routesPath = __DIR__ . '/../routes';

        // Web routes: /fom/*
        $this->loadRoutesFrom($routesPath . '/web.php');

        // API routes: /api/v1/fom/*
        $this->loadRoutesFrom($routesPath . '/api.php');
    }

    /**
     * Register custom view namespace: custom::
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'custom');
    }

    /**
     * Register custom translation namespace: custom::
     * Usage in views/PHP: __('custom::fom.key')
     */
    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'custom');
    }

    /**
     * Load migrations from app/Custom/database/migrations/
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Inject FOM sidebar menu into the default layout via View::composer.
     *
     * Since we cannot modify the core layout blade, we inject a small JS
     * snippet that appends our menu items to the sidebar. This runs on
     * every page load where the default layout is used.
     */
    protected function registerSidebarComposer(): void
    {
        View::composer('layouts.default', function ($view) {
            $view->with('fom_sidebar_items', $this->getSidebarItems());
        });
    }

    /**
     * Define the FOM sidebar menu structure.
     * Labels use translations so the sidebar respects the active locale.
     */
    protected function getSidebarItems(): array
    {
        return [
            'label' => __('custom::fom.nav_module'),
            'icon'  => 'fas fa-industry',
            'children' => [
                ['label' => __('custom::fom.nav_dashboard'),         'url' => '/fom',                       'icon' => 'fas fa-tachometer-alt'],
                ['label' => __('custom::fom.nav_work_orders'),       'url' => '/fom/work-orders',           'icon' => 'fas fa-wrench'],
                ['label' => __('custom::fom.nav_new_report'),        'url' => '/fom/work-orders/create',    'icon' => 'fas fa-plus-circle'],
                ['label' => __('custom::fom.nav_kanban'),            'url' => '/fom/work-orders/board',     'icon' => 'fas fa-columns'],
                ['label' => __('custom::fom.nav_shift_handover'),    'url' => '/fom/shift/handover',        'icon' => 'fas fa-exchange-alt'],
                ['label' => __('custom::fom.nav_shift_history'),     'url' => '/fom/shift/history',         'icon' => 'fas fa-history'],
                ['label' => __('custom::fom.nav_spare_parts'),       'url' => '/fom/spare-parts',           'icon' => 'fas fa-cogs'],
                ['label' => __('custom::fom.nav_low_stock'),         'url' => '/fom/spare-parts/low-stock', 'icon' => 'fas fa-exclamation-triangle'],
                ['label' => __('custom::fom.nav_purchase_requests'), 'url' => '/fom/purchase-requests',     'icon' => 'fas fa-shopping-cart'],
            ],
        ];
    }
}
