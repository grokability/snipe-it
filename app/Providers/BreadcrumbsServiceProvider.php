<?php namespace App\Providers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\Group;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\PredefinedKit;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Tabuna\Breadcrumbs\Breadcrumbs;
use Tabuna\Breadcrumbs\Trail;

class BreadcrumbsServiceProvider extends ServiceProvider
{
/**
* Handles the resource routes for first-class objects
*
* @return void
*/
    public function boot()
    {

        // Default home
        Breadcrumbs::for('home', fn (Trail $trail) =>
        $trail->push('<x-icon type="home" /><span class="sr-only">'.trans('general.dashboard').'</span>', route('home'))
        );

        /**
         * Asset Breadcrumbs
         */


            if ((request()->is('hardware*')) && (request()->status!='')) {
                Breadcrumbs::for('hardware.index', fn (Trail $trail) =>
                $trail->parent('home', route('home'))
                    ->push(trans('general.assets'), route('hardware.index'))
                    ->push(request()->status.' '.trans('general.assets'), route('hardware.index', ['status' => request()->status]))
                );

            } else {
                Breadcrumbs::for('hardware.index', fn (Trail $trail) =>
                $trail->parent('home', route('home'))
                    ->push(trans('general.assets'), route('hardware.index'))
                );
            }




        Breadcrumbs::for('hardware.create', fn (Trail $trail) =>
        $trail->parent('hardware.index', route('hardware.index'))
        ->push(trans('general.create'), route('hardware.create'))
        );

        Breadcrumbs::for('clone/hardware', fn (Trail $trail) =>
        $trail->parent('hardware.index', route('hardware.index'))
            ->push(trans('admin/hardware/general.clone'), route('hardware.create'))
        );

        Breadcrumbs::for('hardware.show', fn (Trail $trail, Asset $asset) =>
        $trail->parent('hardware.index', route('hardware.index'))
            ->push($asset->display_name, route('hardware.show', $asset))
        );

        Breadcrumbs::for('hardware.edit', fn (Trail $trail, Asset $asset) =>
        $trail->parent('hardware.index', route('hardware.index'))
            ->push($asset->display_name, route('hardware.show', $asset))
            ->push(trans('admin/hardware/general.edit'))
        );


        /**
         * Asset Model Breadcrumbs
         */

        if ((request()->is('models*')) && (request()->status=='deleted')) {
            Breadcrumbs::for('models.index', fn (Trail $trail) =>
            $trail->parent('hardware.index', route('hardware.index'))
                ->push(trans('general.asset_models'), route('models.index'))
                ->push(trans('general.deleted_models'), route('models.index', ['status' => request()->status]))
            );
        } else {
            Breadcrumbs::for('models.index', fn (Trail $trail) =>
            $trail->parent('hardware.index', route('hardware.index'))
                ->push(trans('general.asset_models'), route('models.index'))
            );
        }


        Breadcrumbs::for('models.create', fn (Trail $trail) =>
        $trail->parent('models.index', route('models.index'))
            ->push(trans('general.create'), route('models.create'))
        );

        Breadcrumbs::for('models.show', fn (Trail $trail, AssetModel $model) =>
        $trail->parent('models.index', route('models.index'))
            ->push($model->name, route('models.show', $model))
        );

        Breadcrumbs::for('models.edit', fn (Trail $trail, AssetModel $model) =>
        $trail->parent('models.index', route('models.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $model->name]), route('models.edit', $model))
        );


        /**
         * Accessories Breadcrumbs
         */
        Breadcrumbs::for('accessories.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.accessories'), route('accessories.index'))
        );

        Breadcrumbs::for('accessories.create', fn (Trail $trail) =>
        $trail->parent('accessories.index', route('accessories.index'))
            ->push(trans('general.create'), route('accessories.create'))
        );

        Breadcrumbs::for('accessories.show', fn (Trail $trail, Accessory $accessory) =>
        $trail->parent('accessories.index', route('accessories.index'))
            ->push($accessory->name, route('accessories.show', $accessory))
        );

        Breadcrumbs::for('accessories.edit', fn (Trail $trail, Accessory $accessory) =>
        $trail->parent('accessories.index', route('accessories.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $accessory->name]), route('accessories.edit', $accessory))
        );


        /**
         * Categories Breadcrumbs
         */
        Breadcrumbs::for('categories.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.categories'), route('categories.index'))
        );

        Breadcrumbs::for('categories.create', fn (Trail $trail) =>
        $trail->parent('categories.index', route('categories.index'))
            ->push(trans('general.create'), route('categories.create'))
        );

        Breadcrumbs::for('categories.show', fn (Trail $trail, Category $category) =>
        $trail->parent('categories.index', route('categories.index'))
            ->push($category->name, route('categories.show', $category))
        );

        Breadcrumbs::for('categories.edit', fn (Trail $trail, Category $category) =>
        $trail->parent('categories.index', route('categories.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $category->name]), route('categories.edit', $category))
        );



        /**
         * Company Breadcrumbs
         */
        Breadcrumbs::for('companies.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.companies'), route('companies.index'))
        );

        Breadcrumbs::for('companies.create', fn (Trail $trail) =>
        $trail->parent('companies.index', route('companies.index'))
            ->push(trans('general.create'), route('companies.create'))
        );

        Breadcrumbs::for('companies.show', fn (Trail $trail, Company $company) =>
        $trail->parent('companies.index', route('companies.index'))
            ->push($company->name, route('companies.show', $company))
        );

        Breadcrumbs::for('companies.edit', fn (Trail $trail, Company $company) =>
        $trail->parent('companies.index', route('companies.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $company->name]), route('companies.edit', $company))
        );


        /**
         * Components Breadcrumbs
         */
        Breadcrumbs::for('components.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.components'), route('components.index'))
        );

        Breadcrumbs::for('components.create', fn (Trail $trail) =>
        $trail->parent('components.index', route('components.index'))
            ->push(trans('general.create'), route('components.create'))
        );

        Breadcrumbs::for('components.show', fn (Trail $trail, Component $component) =>
        $trail->parent('components.index', route('components.index'))
            ->push($component->name, route('components.show', $component))
        );

        Breadcrumbs::for('components.edit', fn (Trail $trail, Component $component) =>
        $trail->parent('components.index', route('components.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $component->name]), route('components.edit', $component))
        );


        /**
         * Consumables Breadcrumbs
         */
        Breadcrumbs::for('consumables.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.consumables'), route('consumables.index'))
        );

        Breadcrumbs::for('consumables.create', fn (Trail $trail) =>
        $trail->parent('consumables.index', route('consumables.index'))
            ->push(trans('general.create'), route('consumables.create'))
        );

        Breadcrumbs::for('consumables.show', fn (Trail $trail, Consumable $consumable) =>
        $trail->parent('consumables.index', route('consumables.index'))
            ->push($consumable->name, route('consumables.show', $consumable))
        );

        Breadcrumbs::for('consumables.edit', fn (Trail $trail, Consumable $consumable) =>
        $trail->parent('consumables.index', route('consumables.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $consumable->name]), route('consumables.edit', $consumable))
        );

        /**
         * Custom fields Breadcrumbs
         */
        Breadcrumbs::for('fields.index', fn (Trail $trail) =>
        $trail->parent('models.index', route('models.index'))
            ->push(trans('admin/custom_fields/general.custom_fields'), route('fields.index'))
        );

        Breadcrumbs::for('fields.create', fn (Trail $trail) =>
        $trail->parent('fields.index', route('fields.index'))
            ->push(trans('general.create'), route('fields.create'))
        );

        Breadcrumbs::for('fields.edit', fn (Trail $trail, CustomField $field) =>
        $trail->parent('fields.index', route('fields.index'))
            ->push($field->name, route('fields.edit', $field))
        );

        /**
         * Custom fieldsets Breadcrumbs
         */

        Breadcrumbs::for('fieldsets.create', fn (Trail $trail) =>
        $trail->parent('fields.index', route('fields.index'))
            ->push(trans('general.create'), route('fieldsets.create'))
        );

        Breadcrumbs::for('fieldsets.show', fn (Trail $trail, CustomFieldset $fieldset) =>
        $trail->parent('fields.index', route('fields.index'))
            ->push($fieldset->name, route('fields.index'))
        );

        Breadcrumbs::for('fieldsets.edit', fn (Trail $trail, CustomFieldset $fieldset) =>
        $trail->parent('fields.index', route('fields.index'))
            ->push($fieldset->name, route('fieldsets.edit', $fieldset))
        );

        /**
         * Department Breadcrumbs
         */
        Breadcrumbs::for('departments.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.departments'), route('departments.index'))
        );

        Breadcrumbs::for('departments.create', fn (Trail $trail) =>
        $trail->parent('departments.index', route('departments.index'))
            ->push(trans('general.create'), route('departments.create'))
        );

        Breadcrumbs::for('departments.show', fn (Trail $trail, Department $department) =>
        $trail->parent('departments.index', route('departments.index'))
            ->push($department->name, route('home'))
        );

        Breadcrumbs::for('departments.edit', fn (Trail $trail, Department $department) =>
        $trail->parent('departments.index', route('departments.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $department->name]), route('departments.edit', $department))
        );


        /**
         * Department Breadcrumbs
         */
        Breadcrumbs::for('depreciations.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.depreciations'), route('depreciations.index'))
        );

        Breadcrumbs::for('depreciations.create', fn (Trail $trail) =>
        $trail->parent('depreciations.index', route('depreciations.index'))
            ->push(trans('general.create'), route('depreciations.create'))
        );

        Breadcrumbs::for('depreciations.show', fn (Trail $trail, Depreciation $depreciation) =>
        $trail->parent('depreciations.index', route('depreciations.index'))
            ->push($depreciation->name, route('depreciations.show', $depreciation))
        );

        Breadcrumbs::for('depreciations.edit', fn (Trail $trail, Depreciation $depreciation) =>
        $trail->parent('depreciations.index', route('depreciations.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $depreciation->name]), route('depreciations.edit', $depreciation))
        );

        /**
         * Groups Breadcrumbs
         */
        Breadcrumbs::for('groups.index', fn (Trail $trail) =>
        $trail->parent('settings.index', route('settings.index'))
            ->push(trans('general.groups'), route('groups.index'))
        );

        Breadcrumbs::for('groups.create', fn (Trail $trail) =>
        $trail->parent('groups.index', route('groups.index'))
            ->push(trans('general.create'), route('groups.create'))
        );

        Breadcrumbs::for('groups.show', fn (Trail $trail, Group $group) =>
        $trail->parent('groups.index', route('groups.index'))
            ->push($group->name, route('groups.show', $group))
        );

        Breadcrumbs::for('groups.edit', fn (Trail $trail, Group $group) =>
        $trail->parent('groups.index', route('groups.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $group->name]), route('groups.edit', $group))
        );


        /**
         * Licenses Breadcrumbs
         */
        if ((request()->is('licenses*')) && (request()->status=='inactive')) {
            Breadcrumbs::for('licenses.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.licenses'), route('licenses.index'))
                ->push(trans('general.show_inactive'), route('licenses.index'))
            );
        } elseif ((request()->is('licenses*')) && (request()->status=='expiring')) {
            Breadcrumbs::for('licenses.index', fn (Trail $trail) =>
            $trail->parent('home', route('home'))
                ->push(trans('general.licenses'), route('licenses.index'))
                ->push(trans('general.show_expiring'), route('licenses.index'))
            );
        } else {
            Breadcrumbs::for('licenses.index', fn (Trail $trail) =>
            $trail->parent('home', route('home'))
                ->push(trans('general.licenses'), route('licenses.index'))
            );
        }


        Breadcrumbs::for('licenses.create', fn (Trail $trail) =>
        $trail->parent('licenses.index', route('licenses.index'))
            ->push(trans('general.create'), route('licenses.create'))
        );

        Breadcrumbs::for('licenses.show', fn (Trail $trail, License $license) =>
        $trail->parent('licenses.index', route('licenses.index'))
            ->push($license->name, route('licenses.show', $license))
        );

        Breadcrumbs::for('licenses.edit', fn (Trail $trail, License $license) =>
        $trail->parent('licenses.index', route('licenses.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $license->name]), route('licenses.edit', $license))
        );


        /**
         * Locations Breadcrumbs
         */
        Breadcrumbs::for('locations.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.locations'), route('locations.index'))
        );

        Breadcrumbs::for('locations.create', fn (Trail $trail) =>
        $trail->parent('locations.index', route('locations.index'))
            ->push(trans('general.create'), route('locations.create'))
        );

        Breadcrumbs::for('clone/location', fn (Trail $trail) =>
        $trail->parent('locations.index', route('locations.index'))
            ->push(trans('admin/locations/table.clone'), route('locations.create'))
        );


        Breadcrumbs::for('locations.show', fn (Trail $trail, Location $location) =>
        $trail->parent('locations.index', route('locations.index'))
            ->push($location->name, route('locations.show', $location))
        );

        Breadcrumbs::for('locations.edit', fn (Trail $trail, Location $location) =>
        $trail->parent('locations.index', route('locations.index'))
            ->push($location->name, route('locations.show', $location))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $location->name]), route('locations.edit', $location))
        );

        /**
         * Maintenances Breadcrumbs
         */
        Breadcrumbs::for('maintenances.index', fn (Trail $trail) =>
        $trail->parent('hardware.index', route('hardware.index'))
            ->push(trans('general.maintenances'), route('maintenances.index'))
        );

        Breadcrumbs::for('maintenances.create', fn (Trail $trail) =>
        $trail->parent('maintenances.index', route('maintenances.index'))
            ->push(trans('general.create'), route('maintenances.create'))
        );

        Breadcrumbs::for('maintenances.show', fn (Trail $trail, Maintenance $maintenance) =>
        $trail->parent('maintenances.index', route('maintenances.index'))
            ->push($maintenance->name, route('maintenances.show', $maintenance))
        );

        Breadcrumbs::for('maintenances.edit', fn (Trail $trail, Maintenance $maintenance) =>
        $trail->parent('maintenances.index', route('maintenances.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $maintenance->name]), route('maintenances.edit', $maintenance))
        );


        /**
         * Manufacturers Breadcrumbs
         */
        Breadcrumbs::for('manufacturers.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.manufacturers'), route('manufacturers.index'))
        );

        Breadcrumbs::for('manufacturers.create', fn (Trail $trail) =>
        $trail->parent('manufacturers.index', route('manufacturers.index'))
            ->push(trans('general.create'), route('manufacturers.create'))
        );

        Breadcrumbs::for('manufacturers.show', fn (Trail $trail, Manufacturer $manufacturer) =>
        $trail->parent('manufacturers.index', route('manufacturers.index'))
            ->push($manufacturer->name, route('home'))
        );

        Breadcrumbs::for('manufacturers.edit', fn (Trail $trail, Manufacturer $manufacturer) =>
        $trail->parent('manufacturers.index', route('manufacturers.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $manufacturer->name]), route('manufacturers.edit', $manufacturer))
        );


        /**
         * Predefined Kits Breadcrumbs
         */
        Breadcrumbs::for('kits.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.kits'), route('kits.index'))
        );

        Breadcrumbs::for('kits.create', fn (Trail $trail) =>
        $trail->parent('kits.index', route('kits.index'))
            ->push(trans('general.create'), route('kits.create'))
        );

        Breadcrumbs::for('kits.show', fn (Trail $trail, PredefinedKit $kit) =>
        $trail->parent('kits.index', route('kits.index'))
            ->push($kit->name, route('kits.show', $kit))
        );

        Breadcrumbs::for('kits.edit', fn (Trail $trail, PredefinedKit $kit) =>
        $trail->parent('kits.index', route('kits.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $kit->name]), route('kits.edit', $kit))
        );


        /**
         * Status Labels Breadcrumbs
         */
        Breadcrumbs::for('statuslabels.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.status_labels'), route('statuslabels.index'))
        );

        Breadcrumbs::for('statuslabels.create', fn (Trail $trail) =>
        $trail->parent('statuslabels.index', route('statuslabels.index'))
            ->push(trans('general.create'), route('statuslabels.create'))
        );

        Breadcrumbs::for('statuslabels.show', fn (Trail $trail, Statuslabel $statuslabel) =>
        $trail->parent('statuslabels.index', route('statuslabels.index'))
            ->push($statuslabel->name, route('statuslabels.show', $statuslabel))
        );

        Breadcrumbs::for('statuslabels.edit', fn (Trail $trail, Statuslabel $statuslabel) =>
        $trail->parent('statuslabels.index', route('statuslabels.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $statuslabel->name]), route('statuslabels.edit', $statuslabel))
        );


        /**
         * Settings Breadcrumbs
         */
        Breadcrumbs::for('settings.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.admin'), route('settings.index'))
        );



        /**
         * Suppliers Breadcrumbs
         */
        Breadcrumbs::for('suppliers.index', fn (Trail $trail) =>
        $trail->parent('home', route('home'))
            ->push(trans('general.suppliers'), route('suppliers.index'))
        );

        Breadcrumbs::for('suppliers.create', fn (Trail $trail) =>
        $trail->parent('suppliers.index', route('suppliers.index'))
            ->push(trans('general.create'), route('suppliers.create'))
        );

        Breadcrumbs::for('suppliers.show', fn (Trail $trail, Supplier $supplier) =>
        $trail->parent('suppliers.index', route('suppliers.index'))
            ->push($supplier->name, route('home'))
        );

        Breadcrumbs::for('suppliers.edit', fn (Trail $trail, Supplier $supplier) =>
        $trail->parent('suppliers.index', route('suppliers.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $supplier->name]), route('suppliers.edit', $supplier))
        );



        /**
         * Users Breadcrumbs
         */
        if ((request()->is('users*')) && (request()->status=='deleted')) {
            Breadcrumbs::for('users.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.users'), route('users.index'))
                ->push(trans('general.deleted_users'), route('users.index'))
            );
        } elseif ((request()->is('users*')) && (request()->admins=='true')) {
            Breadcrumbs::for('users.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.users'), route('users.index'))
                ->push(trans('general.show_admins'), route('users.index'))
            );
        } elseif ((request()->is('users*')) && (request()->superadmins=='true')) {
            Breadcrumbs::for('users.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.users'), route('users.index'))
                ->push(trans('general.show_superadmins'), route('users.index'))
            );
        } elseif ((request()->is('users*')) && (request()->activated=='0')) {
            Breadcrumbs::for('users.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.users'), route('users.index'))
                ->push(trans('general.login_disabled'), route('users.index'))
            );
        } elseif ((request()->is('users*')) && (request()->activated=='1')) {
            Breadcrumbs::for('users.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.users'), route('users.index'))
                ->push(trans('general.login_enabled'), route('users.index'))
            );
        } else {
            Breadcrumbs::for('users.index', fn(Trail $trail) => $trail->parent('home', route('home'))
                ->push(trans('general.users'), route('users.index'))
            );
        }

        Breadcrumbs::for('users.create', fn (Trail $trail) =>
        $trail->parent('users.index', route('users.index'))
            ->push(trans('general.create'), route('users.create'))
        );

        Breadcrumbs::for('users.clone.show', fn (Trail $trail) =>
        $trail->parent('users.index', route('users.index'))
            ->push(trans('admin/users/general.clone'), route('users.create'))
        );



        Breadcrumbs::for('users.show', fn (Trail $trail, User $user) =>
        $trail->parent('users.index', route('users.index'))
            ->push($user->display_name ?? 'Missing Username!', route('users.show', $user))
        );

        Breadcrumbs::for('users.edit', fn (Trail $trail, User $user) =>
        $trail->parent('users.index', route('users.index'))
            ->push(trans('general.breadcrumb_button_actions.edit_item', ['name' => $user->name]), route('users.edit', $user))
        );



    }


}