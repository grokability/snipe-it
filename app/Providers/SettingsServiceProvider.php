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
        app()->singleton('api_limit_value', function () {
            $limit = config('app.max_results');
            $int_limit = intval(request('limit'));

            if ((abs($int_limit) > 0) && ($int_limit <= config('app.max_results'))) {
                $limit = abs($int_limit);
            }

            return $limit;
        });

        // Make sure the offset is actually set and is an integer
        app()->singleton('api_offset_value', function () {
            $offset = intval(request('offset'));
            return $offset;
        });


        /**
         * Set some common variables so that they're globally available.
         * The paths should always be public (versus private uploads)
         */

        // Model paths and URLs


        app()->singleton('eula_pdf_path', function () {
            return 'eula_pdf_path/';
        });

        app()->singleton('assets_upload_path', function () {
            return 'assets/';
        });

        app()->singleton('maintenances_path', function () {
            return 'maintenances/';
        });

        app()->singleton('audits_upload_path', function () {
            return 'audits/';
        });

        app()->singleton('accessories_upload_path', function () {
            return 'public/uploads/accessories/';
        });

        app()->singleton('models_upload_path', function () {
            return 'models/';
        });

        app()->singleton('models_upload_url', function () {
            return 'models/';
        });

        // Categories
        app()->singleton('categories_upload_path', function () {
            return 'categories/';
        });

        app()->singleton('categories_upload_url', function () {
            return 'categories/';
        });

        // Locations
        app()->singleton('locations_upload_path', function () {
            return 'locations/';
        });

        app()->singleton('locations_upload_url', function () {
            return 'locations/';
        });

        // Users
        app()->singleton('users_upload_path', function () {
            return 'avatars/';
        });

        app()->singleton('users_upload_url', function () {
            return 'users/';
        });

        // Manufacturers
        app()->singleton('manufacturers_upload_path', function () {
            return 'manufacturers/';
        });

        app()->singleton('manufacturers_upload_url', function () {
            return 'manufacturers/';
        });

        // Suppliers
        app()->singleton('suppliers_upload_path', function () {
            return 'suppliers/';
        });

        app()->singleton('suppliers_upload_url', function () {
            return 'suppliers/';
        });

        // Departments
        app()->singleton('departments_upload_path', function () {
            return 'departments/';
        });

        app()->singleton('departments_upload_url', function () {
            return 'departments/';
        });

        // Company paths and URLs
        app()->singleton('companies_upload_path', function () {
            return 'companies/';
        });

        app()->singleton('companies_upload_url', function () {
            return 'companies/';
        });

        // Accessories paths and URLs
        app()->singleton('accessories_upload_path', function () {
            return 'accessories/';
        });

        app()->singleton('accessories_upload_url', function () {
            return 'accessories/';
        });

        // Consumables paths and URLs
        app()->singleton('consumables_upload_path', function () {
            return 'consumables/';
        });

        app()->singleton('consumables_upload_url', function () {
            return 'consumables/';
        });

        // Components paths and URLs
        app()->singleton('components_upload_path', function () {
            return 'components/';
        });

        app()->singleton('components_upload_url', function () {
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
