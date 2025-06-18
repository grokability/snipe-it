<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

/**
 * This service provider handles sharing the snipeSettings variable, and sets
 * some common upload path and image urls.
 *
 * PHP version 5.5.9
 * @version    v3.0
 */
class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Custom email array validation
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return void
     */
    public function boot()
    {

        // Share common setting variables with all views.
        view()->composer('*', function ($view) {
            $view->with('snipeSettings', Setting::getSettings());
        });


        // Make sure the limit is actually set, is an integer and does not exceed system limits
        \App::singleton('api_limit_value', function () {
            $limit = config('app.max_results');
            $int_limit = intval(request('limit'));

            if ((abs($int_limit) > 0) && ($int_limit <= config('app.max_results'))) {
                $limit = abs($int_limit);
            }

            return $limit;
        });

        // Make sure the offset is actually set and is an integer
        \App::singleton('api_offset_value', function () {
            $offset = intval(request('offset'));
            return $offset;
        });


        /**
         * Set some common variables so that they're globally available.
         * The paths should always be public (versus private uploads)
         */

        // Model paths and URLs


        \App::singleton('eula_pdf_path', function () {
            return 'eula_pdf_path/';
        });

        \App::singleton('assets_upload_path', function () {
            return 'assets/';
        });

        \App::singleton('audits_upload_path', function () {
            return 'audits/';
        });

        \App::singleton('accessories_upload_path', function () {
            return 'public/uploads/accessories/';
        });

        \App::singleton('models_upload_path', function () {
            return 'models/';
        });

        \App::singleton('models_upload_url', function () {
            return 'models/';
        });

        // Categories
        \App::singleton('categories_upload_path', function () {
            return 'categories/';
        });

        \App::singleton('categories_upload_url', function () {
            return 'categories/';
        });

        // Locations
        \App::singleton('locations_upload_path', function () {
            return 'locations/';
        });

        \App::singleton('locations_upload_url', function () {
            return 'locations/';
        });

        // Users
        \App::singleton('users_upload_path', function () {
            return 'avatars/';
        });

        \App::singleton('users_upload_url', function () {
            return 'users/';
        });

        // Manufacturers
        \App::singleton('manufacturers_upload_path', function () {
            return 'manufacturers/';
        });

        \App::singleton('manufacturers_upload_url', function () {
            return 'manufacturers/';
        });

        // Suppliers
        \App::singleton('suppliers_upload_path', function () {
            return 'suppliers/';
        });

        \App::singleton('suppliers_upload_url', function () {
            return 'suppliers/';
        });

        // Departments
        \App::singleton('departments_upload_path', function () {
            return 'departments/';
        });

        \App::singleton('departments_upload_url', function () {
            return 'departments/';
        });

        // Company paths and URLs
        \App::singleton('companies_upload_path', function () {
            return 'companies/';
        });

        \App::singleton('companies_upload_url', function () {
            return 'companies/';
        });

        // Accessories paths and URLs
        \App::singleton('accessories_upload_path', function () {
            return 'accessories/';
        });

        \App::singleton('accessories_upload_url', function () {
            return 'accessories/';
        });

        // Consumables paths and URLs
        \App::singleton('consumables_upload_path', function () {
            return 'consumables/';
        });

        \App::singleton('consumables_upload_url', function () {
            return 'consumables/';
        });

        // Components paths and URLs
        \App::singleton('components_upload_path', function () {
            return 'components/';
        });

        \App::singleton('components_upload_url', function () {
            return 'components/';
        });

        // Set the monetary locale to the configured locale to make helper::parseFloat work.
        setlocale(LC_MONETARY, config('app.locale'));
        setlocale(LC_NUMERIC, config('app.locale'));
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
