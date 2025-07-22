<?php

namespace App\Providers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\PredefinedKit;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\AccessoryPolicy;
use App\Policies\AssetModelPolicy;
use App\Policies\AssetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ComponentPolicy;
use App\Policies\ConsumablePolicy;
use App\Policies\CustomFieldPolicy;
use App\Policies\CustomFieldsetPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DepreciationPolicy;
use App\Policies\LicensePolicy;
use App\Policies\LocationPolicy;
use App\Policies\ManufacturerPolicy;
use App\Policies\PredefinedKitPolicy;
use App\Policies\StatuslabelPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        Component::class => ComponentPolicy::class,
        Consumable::class => ConsumablePolicy::class,
        CustomField::class => CustomFieldPolicy::class,
        CustomFieldset::class => CustomFieldsetPolicy::class,
        Department::class => DepartmentPolicy::class,
        Depreciation::class => DepreciationPolicy::class,
        License::class => LicensePolicy::class,
        Location::class => LocationPolicy::class,
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
     */
    public function boot()
    {
        $this->commands([
            \Laravel\Passport\Console\InstallCommand::class,
            \Laravel\Passport\Console\ClientCommand::class,
            \Laravel\Passport\Console\KeysCommand::class,
        ]);

        $this->registerPolicies();
        Passport::tokensExpireIn(Carbon::now()->addYears((int)config('passport.expiration_years')));
        Passport::refreshTokensExpireIn(Carbon::now()->addYears((int)config('passport.expiration_years')));
        Passport::personalAccessTokensExpireIn(Carbon::now()->addYears((int)config('passport.expiration_years')));

        Passport::cookie(config('passport.cookie_name'));


        /**
         * BEFORE ANYTHING ELSE
         *
         * If this condition is true, ANYTHING else below will be assumed to be true.
         * This is where we set the superadmin permission to allow superadmins to be able to do everything within the system.
         *
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
                if (($user->hasAccess('users.edit')) && (!$user->isAdmin()) && (!$user->isAdmin())) {

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

        Gate::define('accessories.files', function ($user) {
            if ($user->hasAccess('accessories.files')) {
                return true;
            }
        });

        Gate::define('components.files', function ($user) {
            if ($user->hasAccess('components.files')) {
                return true;
            }
        });

        Gate::define('consumables.files', function ($user) {
            if ($user->hasAccess('consumables.files')) {
                return true;
            }
        });

        // Can the user import CSVs?
        Gate::define('import', function ($user) {
            if ($user->hasAccess('import')) {
                return true;
            }
        });


        Gate::define('licenses.files', function ($user) {
            if ($user->hasAccess('licenses.files')) {
                return true;
            }
        });

        Gate::define('assets.view.encrypted_custom_fields', function ($user) {
            if($user->hasAccess('assets.view.encrypted_custom_fields')){
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


        // This  determines whether or not an API user should be able to get the selectlists.
        // This can seem a little confusing, since view properties may not have been granted
        // to the logged in API user, but creating assets, licenses, etc won't work 
        // if the user can't view and interact with the select lists.
        Gate::define('view.selectlists', function ($user) {
            return $user->can('update', Asset::class) 
                || $user->can('create', Asset::class)    
                || $user->can('checkout', Asset::class)
                || $user->can('checkin', Asset::class)
                || $user->can('audit', Asset::class)       
                || $user->can('update', License::class)   
                || $user->can('create', License::class)   
                || $user->can('update', Component::class)
                || $user->can('create', Component::class)   
                || $user->can('update', Consumable::class)   
                || $user->can('create', Consumable::class)   
                || $user->can('update', Accessory::class)
                || $user->can('create', Accessory::class)   
                || $user->can('update', User::class)
                || $user->can('create', User::class)
                || ($user->hasAccess('reports.view'));
        });


        // This determines whether the user can edit their profile based on the setting in Admin > General
        Gate::define('self.profile', function ($user) {
            return $user->canEditProfile();
        });


    }
}
