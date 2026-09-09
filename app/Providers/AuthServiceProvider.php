<?php

namespace App\Providers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Manufacturer;
use App\Models\PredefinedKit;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\AccessoryPolicy;
use App\Policies\AssetModelPolicy;
use App\Policies\AssetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CheckoutRequestPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ComponentPolicy;
use App\Policies\ConsumablePolicy;
use App\Policies\CustomFieldPolicy;
use App\Policies\CustomFieldsetPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DepreciationPolicy;
use App\Policies\LicensePolicy;
use App\Policies\LocationPolicy;
use App\Policies\MaintenancePolicy;
use App\Policies\MaintenanceTypePolicy;
use App\Policies\ManufacturerPolicy;
use App\Policies\PredefinedKitPolicy;
use App\Policies\StatuslabelPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * See SnipePermissionsPolicy for additional information.
     *
     * @var array
     */
    protected $policies = [
        Accessory::class => AccessoryPolicy::class,
        Asset::class => AssetPolicy::class,
        AssetModel::class => AssetModelPolicy::class,
        Category::class => CategoryPolicy::class,
        CheckoutRequest::class => CheckoutRequestPolicy::class,
        Component::class => ComponentPolicy::class,
        Consumable::class => ConsumablePolicy::class,
        CustomField::class => CustomFieldPolicy::class,
        CustomFieldset::class => CustomFieldsetPolicy::class,
        Department::class => DepartmentPolicy::class,
        Depreciation::class => DepreciationPolicy::class,
        License::class => LicensePolicy::class,
        Location::class => LocationPolicy::class,
        Maintenance::class => MaintenancePolicy::class,
        MaintenanceType::class => MaintenanceTypePolicy::class,
        PredefinedKit::class => PredefinedKitPolicy::class,
        Statuslabel::class => StatuslabelPolicy::class,
        Supplier::class => SupplierPolicy::class,
        User::class => UserPolicy::class,
        Manufacturer::class => ManufacturerPolicy::class,
        Company::class => CompanyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function boot()
    {
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);

        $this->registerPolicies();
        $expirationYears = (int) config('passport.expiration_years');
        Passport::tokensExpireIn(CarbonInterval::years($expirationYears));
        Passport::refreshTokensExpireIn(CarbonInterval::years($expirationYears));
        Passport::personalAccessTokensExpireIn(CarbonInterval::years($expirationYears));

        Passport::cookie(config('passport.cookie_name'));

        // Federated identity: a provider-agnostic OIDC bearer guard, layered
        // alongside Passport via the `auth:oidc,api` multi-guard. Inert until
        // config('oidc.enabled') is true, so this is purely additive.
        // `$name` is unused: Laravel fixes the driver-closure signature as
        // ($app, $name, $config), and the guard is named by config/auth.php.
        Auth::extend('oidc', function ($app, $name, array $config) {
            return new \App\Auth\OidcGuard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(\App\Services\Oidc\OidcTokenValidator::class),
                $app->make(\App\Services\Oidc\OidcUserResolver::class),
            );
        });

        /**
         * BEFORE ANYTHING ELSE
         *
         * If this condition is true, ANYTHING else below will be assumed to be true.
         * This is where we set the superadmin permission to allow superadmins to be able to do everything within the system.
         */
        Gate::before(function ($user, $ability) {

            // Disallow even superadmins to edit non-editable things when in demo mode.
            // (We have to do this to prevent jerks from trying to break the demo by editing things they shouldn't.)
            if (($ability == 'editableOnDemo') && (config('app.lock_passwords'))) {
                return false;
            }
            if ($user->isSuperUser()) {
                return true;
            }
        });

        /**
         * GENERAL GATES
         *
         * These control general sections of the admin. These definitions are used in our blades via @can('blah) and also
         * use in our controllers to determine if a user has access to a certain area.
         */
        Gate::define('canEditAuthFields', function ($user, $item) {

            if ($item instanceof User) {

                // if they can only edit users, deny them if the user is admin or superadmin
                if (! $user->isAdmin()) {

                    if ($item->isAdmin() || $item->isSuperUser()) {
                        return false;
                    }

                    return true;
                }

                // if they are an admin, deny them only if the user is a superadmin
                if ($user->hasAccess('admin')) {
                    if ($item->isSuperUser()) {
                        return false;
                    }

                    return true;
                }

                return false;
            }

            return false;
        });

        /**
         * Define the demo mode gate so we have an easy way to use @can and Gate::allows()
         */
        Gate::define('editableOnDemo', function () {
            if (config('app.lock_passwords')) {
                return false;
            }

            return true;
        });

        Gate::define('admin', function ($user) {
            if ($user->hasAccess('admin')) {
                return true;
            }
        });

        // Can the user import CSVs?
        Gate::define('import', function ($user) {
            if ($user->hasAccess('import')) {
                return true;
            }
        });

        // True when the user has checkout permission on at least
        // one of the five checkoutable types. Named for the
        // underlying question ("can this user check out anything
        // at all?") rather than a specific consumer surface, so
        // it reads sensibly at every callsite:
        //   - /requests page + API endpoint + nav link + bulk-
        //     cancel bulk-actions widget: gate on this
        //   - future consumers (any per-type checkout screen that
        //     wants a "can this admin fulfill anything?" check)
        //     inherit the same shape
        // AssetModel isn't in the list because it has no
        // models.checkout permission - model requests fulfill via
        // checking out an Asset, so admins with assets.checkout
        // implicitly cover both.
        Gate::define('canCheckoutAtLeastOneItemType', function ($user) {
            return $user->can('checkout', Asset::class)
                || $user->can('checkout', Accessory::class)
                || $user->can('checkout', Consumable::class)
                || $user->can('checkout', Component::class)
                || $user->can('checkout', License::class);
        });

        Gate::define('assets.view.encrypted_custom_fields', function ($user) {
            if ($user->hasAccess('assets.view.encrypted_custom_fields')) {
                return true;
            }
        });

        // -----------------------------------------
        // Reports
        // -----------------------------------------
        Gate::define('reports.view', function ($user) {
            if ($user->hasAccess('reports.view')) {
                return true;
            }
        });

        // -----------------------------------------
        // Activity
        // -----------------------------------------
        Gate::define('activity.view', function ($user) {
            if (($user->hasAccess('reports.view')) || ($user->hasAccess('admin'))) {
                return true;
            }
        });

        // -----------------------------------------
        // Self
        // -----------------------------------------
        Gate::define('self.two_factor', function ($user) {
            if (($user->hasAccess('self.two_factor')) || ($user->hasAccess('admin'))) {
                return true;
            }
        });

        Gate::define('self.api', function ($user) {
            return $user->hasAccess('self.api');
        });

        Gate::define('self.edit_location', function ($user) {
            return $user->hasAccess('self.edit_location');
        });

        Gate::define('self.checkout_assets', function ($user) {
            return $user->hasAccess('self.checkout_assets');
        });

        Gate::define('self.view_purchase_cost', function ($user) {
            return $user->hasAccess('self.view_purchase_cost');
        });

        // This is largely used to determine whether to display the gear icon sidenav
        // in the left-side navigation
        Gate::define('backend.interact', function ($user) {
            return $user->can('view', Statuslabel::class)
                || $user->can('view', AssetModel::class)
                || $user->can('view', Category::class)
                || $user->can('view', Manufacturer::class)
                || $user->can('view', Supplier::class)
                || $user->can('view', Department::class)
                || $user->can('view', Location::class)
                || $user->can('view', Company::class)
                || $user->can('view', Manufacturer::class)
                || $user->can('view', CustomField::class)
                || $user->can('view', CustomFieldset::class)
                || $user->can('view', Depreciation::class);
        });

        // This  determines whether or not a user should be able to get the selectlists.
        // This can seem a little confusing, since view properties may not have been granted
        // to the logged in user, but creating assets, licenses, etc won't work
        // if the user can't view and interact with the select lists.
        // Selectlists back form dropdowns across the app. A caller who
        // can update, create, or check out any resource needs the
        // dropdowns those forms depend on. A pure view-only caller
        // does not: they browse tables via the index endpoints, not
        // the selectlist endpoints. The pre-existing gate covered
        // Asset / License / Component / Consumable / Accessory / User
        // but silently 403'd anyone whose only permission was on one
        // of the other admin resources (locations.edit ran into this on
        // the parent + manager pickers). Extending the same
        // update-or-create shape to every listable resource fixes
        // that without granting view-only callers dropdowns they'd
        // never have to use.
        Gate::define('view.selectlists', function ($user) {
            $resources = [
                Asset::class,
                License::class,
                Component::class,
                Consumable::class,
                Accessory::class,
                User::class,
                Location::class,
                Category::class,
                Manufacturer::class,
                Supplier::class,
                Company::class,
                Department::class,
                Statuslabel::class,
                Depreciation::class,
                AssetModel::class,
            ];

            foreach ($resources as $resource) {
                if ($user->can('update', $resource) || $user->can('create', $resource)) {
                    return true;
                }
            }

            $checkoutables = [
                Asset::class,
                Accessory::class,
                Consumable::class,
                Component::class,
                License::class,
            ];

            foreach ($checkoutables as $checkoutable) {
                if ($user->can('checkout', $checkoutable) || $user->can('checkin', $checkoutable)) {
                    return true;
                }
            }

            return $user->can('audit', Asset::class)
                || $user->hasAccess('reports.view');
        });

        // This determines whether the user can edit their profile based on the setting in Admin > General
        Gate::define('self.profile', function ($user) {
            return $user->canEditProfile();
        });

    }
}
