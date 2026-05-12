<?php

namespace App\Models\Builders;

use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AssetBuilder extends Builder
{
    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

    /**
     * Query builder scope for pending assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function pending()
    {
        return $this->whereHas(
            'status', function ($query) {
                $query->where('deployable', '=', 0)
                    ->where('pending', '=', 1)
                    ->where('archived', '=', 0);
            }
        );
    }

    /**
     * Query builder scope for searching location
     *
     * @return AssetBuilder Modified query builder
     */
    public function byLocation($location)
    {
        return $this->where(
            function ($query) use ($location) {
                $query->whereHas(
                    'assignedTo', function ($query) use ($location) {
                        $query->where(
                            [
                                ['users.location_id', '=', $location->id],
                                ['assets.assigned_type', '=', User::class],
                            ]
                        )->orWhere(
                            [
                                ['locations.id', '=', $location->id],
                                ['assets.assigned_type', '=', Location::class],
                            ]
                        )->orWhere(
                            [
                                ['assets.rtd_location_id', '=', $location->id],
                                ['assets.assigned_type', '=', self::class],
                            ]
                        );
                    }
                )->orWhere(
                    function ($query) use ($location) {
                        $query->where('assets.rtd_location_id', '=', $location->id);
                        $query->whereNull('assets.assigned_to');
                    }
                );
            }
        );
    }

    /**
     * Query builder scope for RTD assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function rtd()
    {
        return $this->whereNull('assets.assigned_to')
            ->whereHas(
                'status', function ($query) {
                    $query->where('deployable', '=', 1)
                        ->where('pending', '=', 0)
                        ->where('archived', '=', 0);
                }
            );
    }

    /**
     * Query builder scope for Undeployable assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function undeployable()
    {
        return $this->whereHas(
            'status', function ($query) {
                $query->where('deployable', '=', 0)
                    ->where('pending', '=', 0)
                    ->where('archived', '=', 0);
            }
        );
    }

    /**
     * Query builder scope for non-Archived assets
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @return AssetBuilder Modified query builder
     */
    public function notArchived()
    {
        return $this->whereHas(
            'status', function ($query) {
                $query->where('archived', '=', 0);
            }
        );
    }

    /**
     * Query builder scope for Assets that are due for auditing, based on the assets.next_audit_date
     * and settings.audit_warning_days.
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on assets that will need to be audited.
     *
     * Due for audit soon:
     * next_audit_date greater than or equal to now (must be in the future)
     * and (next_audit_date - threshold days) <= now ()
     *
     * Example:
     * next_audit_date = May 4, 2025
     * threshold for alerts = 30 days
     * now = May 4, 2019
     *
     * @param  Setting  $settings
     * @return AssetBuilder Modified query builder
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  v4.6.16
     */
    public function dueForAudit($settings)
    {
        $interval = (int) $settings->audit_warning_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval)->format('Y-m-d');

        return $this->whereNotNull('assets.next_audit_date')
            ->whereBetween('assets.next_audit_date', [$today->format('Y-m-d'), $interval_date])
            ->where('assets.archived', '=', 0)
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are OVERDUE for auditing, based on the assets.next_audit_date
     * and settings.audit_warning_days. It checks to see if assets.next audit_date is before now
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on overdue assets.
     *
     * @param  Setting  $settings
     * @return AssetBuilder Modified query builder
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  v4.6.16
     */
    public function overdueForAudit()
    {
        return $this->whereNotNull('assets.next_audit_date')
            ->where('assets.next_audit_date', '<', Carbon::now()->format('Y-m-d'))
            ->where('assets.archived', '=', 0)
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are due for auditing OR overdue, based on the assets.next_audit_date
     * and settings.audit_warning_days.
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on assets that will need to be audited.
     *
     * @param  Setting  $settings
     * @return AssetBuilder Modified query builder
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  v4.6.16
     */
    public function dueOrOverdueForAudit($settings)
    {

        return $this->where(
            function ($query) {
                $query->OverdueForAudit();
            }
        )->orWhere(
            function ($query) use ($settings) {
                $query->DueForAudit($settings);
            }
        );
    }

    /**
     * Query builder scope for Assets that are DUE for checkin, based on the assets.expected_checkin
     * and settings.audit_warning_days. It checks to see if assets.expected_checkin is now
     *
     * @return AssetBuilder Modified query builder
     *
     * @since  v6.4.0
     *
     * @author A. Gianotto <snipe@snipe.net>
     */
    public function dueForCheckin($settings)
    {
        $interval = (int) $settings->due_checkin_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval)->format('Y-m-d');

        return $this->whereNotNull('assets.expected_checkin')
            ->whereBetween('assets.expected_checkin', [$today->format('Y-m-d'), $interval_date])
            ->where('assets.archived', '=', 0)
            ->whereNotNull('assets.assigned_to')
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are overdue for checkin OR overdue
     *
     * @return AssetBuilder Modified query builder
     *
     * @since  v6.4.0
     *
     * @author A. Gianotto <snipe@snipe.net>
     */
    public function overdueForCheckin()
    {
        return $this->whereNotNull('assets.expected_checkin')
            ->where('assets.expected_checkin', '<', Carbon::now()->format('Y-m-d'))
            ->where('assets.archived', '=', 0)
            ->whereNotNull('assets.assigned_to')
            ->NotArchived();
    }

    /**
     * Query builder scope for Assets that are due for checkin OR overdue
     *
     * @return AssetBuilder Modified query builder
     *
     * @since  v6.4.0
     *
     * @author A. Gianotto <snipe@snipe.net>
     */
    public function dueOrOverdueForCheckin($settings)
    {
        return $this->where(
            function ($query) {
                $query->OverdueForCheckin();
            }
        )->orWhere(
            function ($query) use ($settings) {
                $query->DueForCheckin($settings);
            }
        );
    }

    /**
     * Query builder scope for Archived assets counting
     *
     * This is primarily used for the tab counters so that IF the admin
     * has chosen to not display archived assets in their regular lists
     * and views, it will return the correct number.
     *
     * @return AssetBuilder Modified query builder
     */
    public function forShow()
    {

        if (Setting::getSettings()->show_archived_in_list != 1) {
            return $this->whereHas(
                'status', function ($query) {
                    $query->where('archived', '=', 0);
                }
            );
        } else {
            return $this;
        }

    }

    /**
     * Query builder scope for Archived assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function archived()
    {
        return $this->whereHas(
            'status', function ($query) {
                $query->where('deployable', '=', 0)
                    ->where('pending', '=', 0)
                    ->where('archived', '=', 1);
            }
        );
    }

    /**
     * Query builder scope for Deployed assets
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @return AssetBuilder Modified query builder
     */
    public function deployed()
    {
        return $this->whereNotNull('assets.assigned_to');
    }

    /**
     * Query builder scope for Requestable assets
     *
     * @return AssetBuilder Modified query builder
     *
     * @todo probably refactor? This is allowing table names for some reason
     */
    public function requestable(): Builder
    {
        $table = $this->getModel()->getTable();

        return Company::scopeCompanyables($this->where($table.'.requestable', '=', 1))
            ->whereHas(
                'status', function ($query) {
                    $query->where(
                        function ($query) {
                            $query->where('deployable', '=', 1)
                                ->where('archived', '=', 0); // you definitely can't request something that's archived
                        }
                    )->orWhere('pending', '=', 1); // we've decided that even though an asset may be 'pending', you can still request it
                }
            );
    }

    /**
     * scopeInModelList
     * Get all assets in the provided listing of model ids
     *
     * @return AssetBuilder
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     */
    public function inModels(array $modelIdListing)
    {
        return $this->whereIn('assets.model_id', $modelIdListing);
    }

    /**
     * Query builder scope to get not-yet-accepted assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function unaccepted()
    {
        return $this->where('accepted', '=', 'pending');
    }

    /**
     * Query builder scope to get rejected assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function rejected()
    {
        return $this->where('accepted', '=', 'rejected');
    }

    /**
     * Query builder scope to get accepted assets
     *
     * @return AssetBuilder Modified query builder
     */
    public function accepted()
    {
        return $this->where('accepted', '=', 'accepted');
    }

    /**
     * Query builder scope to search on text for complex Bootstrap Tables API.
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $search  Search term
     * @return AssetBuilder Modified query builder
     */
    public function assignedSearch($search)
    {
        $search = explode(' OR ', $search);

        return $this->leftJoin(
            'users as assets_users', function ($leftJoin) {
                $leftJoin->on('assets_users.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', User::class);
            }
        )->leftJoin(
            'locations as assets_locations', function ($leftJoin) {
                $leftJoin->on('assets_locations.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', Location::class);
            }
        )->leftJoin(
            'assets as assigned_assets', function ($leftJoin) {
                $leftJoin->on('assigned_assets.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', self::class);
            }
        )->where(
            function ($query) use ($search) {
                foreach ($search as $search) {
                    $query->whereHas(
                        'model', function ($query) use ($search) {
                            $query->whereHas(
                                'category', function ($query) use ($search) {
                                    $query->where(
                                        function ($query) use ($search) {
                                            $query->where('categories.name', 'LIKE', '%'.$search.'%')
                                                ->orWhere('models.name', 'LIKE', '%'.$search.'%')
                                                ->orWhere('models.model_number', 'LIKE', '%'.$search.'%');
                                        }
                                    );
                                }
                            );
                        }
                    )->orWhereHas(
                        'model', function ($query) use ($search) {
                            $query->whereHas(
                                'manufacturer', function ($query) use ($search) {
                                    $query->where(
                                        function ($query) use ($search) {
                                            $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                                        }
                                    );
                                }
                            );
                        }
                    )->orWhere(
                        function ($query) use ($search) {
                            $query->where('assets_users.first_name', 'LIKE', '%'.$search.'%')
                                ->orWhere('assets_users.last_name', 'LIKE', '%'.$search.'%')
                                ->orWhere('assets_users.username', 'LIKE', '%'.$search.'%')
                                ->orWhere('assets_users.jobtitle', 'LIKE', '%'.$search.'%')
                                ->orWhereMultipleColumns(
                                    [
                                        'assets_users.first_name',
                                        'assets_users.last_name',
                                        'assets_users.jobtitle',
                                    ], $search
                                )
                                ->orWhere('assets_locations.name', 'LIKE', '%'.$search.'%')
                                ->orWhere('assigned_assets.name', 'LIKE', '%'.$search.'%');
                        }
                    )->orWhere('assets.name', 'LIKE', '%'.$search.'%')
                        ->orWhere('assets.asset_tag', 'LIKE', '%'.$search.'%')
                        ->orWhere('assets.serial', 'LIKE', '%'.$search.'%')
                        ->orWhere('assets.order_number', 'LIKE', '%'.$search.'%')
                        ->orWhere('assets.notes', 'LIKE', '%'.$search.'%');
                }

            }
        )->withTrashed()->whereNull('assets.deleted_at'); // workaround for laravel bug
    }

    /**
     * Query builder scope to search the department ID of users assigned to assets
     *
     * @return string | false
     * @return AssetBuilder Modified query builder
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v5.0]
     */
    public function inDepartment($search)
    {
        return $this->leftJoin(
            'users as assets_dept_users', function ($leftJoin) {
                $leftJoin->on('assets_dept_users.id', '=', 'assets.assigned_to')
                    ->where('assets.assigned_type', '=', User::class);
            }
        )->where(
            function ($query) use ($search) {
                $query->whereIn('assets_dept_users.department_id', $search);

            }
        )->withTrashed()->whereNull('assets.deleted_at'); // workaround for laravel bug
    }

    /**
     * Query builder scope to order on model
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByModel($order)
    {
        return $this->join('models as asset_models', 'assets.model_id', '=', 'asset_models.id')->orderBy('asset_models.name', $order);
    }

    /**
     * Query builder scope to order on model number
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByModelNumber($order)
    {
        return $this->leftJoin('models as model_number_sort', 'assets.model_id', '=', 'model_number_sort.id')->orderBy('model_number_sort.model_number', $order);
    }

    /**
     * Query builder scope to order on created_by name
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByCreatedByName($order)
    {
        return $this->leftJoin('users as admin_sort', 'assets.created_by', '=', 'admin_sort.id')->select('assets.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }

    /**
     * Query builder scope to order on assigned user
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByAssigned($order)
    {
        return $this->leftJoin('users as users_sort', 'assets.assigned_to', '=', 'users_sort.id')->select('assets.*')->orderBy('users_sort.first_name', $order)->orderBy('users_sort.last_name', $order);
    }

    /**
     * Query builder scope to order on status
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByStatus($order)
    {
        return $this->join('status_labels as status_sort', 'assets.status_id', '=', 'status_sort.id')->orderBy('status_sort.name', $order);
    }

    /**
     * Query builder scope to order on company
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByCompany($order)
    {
        return $this->leftJoin('companies as company_sort', 'assets.company_id', '=', 'company_sort.id')->orderBy('company_sort.name', $order);
    }

    /**
     * Query builder scope to return results of a category
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function inCategory($category_id)
    {
        return $this->join('models as category_models', 'assets.model_id', '=', 'category_models.id')
            ->join('categories', 'category_models.category_id', '=', 'categories.id')
            ->whereIn('category_models.category_id', (! is_array($category_id) ? explode(',', $category_id) : $category_id));
        // ->whereIn('category_models.category_id', $category_id);
    }

    /**
     * Query builder scope to return results of a manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function byManufacturer($manufacturer_id)
    {
        return $this->join('models', 'assets.model_id', '=', 'models.id')
            ->join('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')->whereIn('models.manufacturer_id', (! is_array($manufacturer_id) ? explode(',', $manufacturer_id) : $manufacturer_id));
    }

    /**
     * Query builder scope to order on category
     *
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByCategoryName($order)
    {
        return $this->join('models as order_model_category', 'assets.model_id', '=', 'order_model_category.id')
            ->join('categories as category_order', 'order_model_category.category_id', '=', 'category_order.id')
            ->orderBy('category_order.name', $order);
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByManufacturerName($query, $order)
    {
        return $this->join('models as order_asset_model', 'assets.model_id', '=', 'order_asset_model.id')
            ->leftjoin('manufacturers as manufacturer_order', 'order_asset_model.manufacturer_id', '=', 'manufacturer_order.id')
            ->orderBy('manufacturer_order.name', $order);
    }

    /**
     * Query builder scope to order on location
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByLocationName($query, $order)
    {
        return $this->leftJoin('locations as asset_locations', 'asset_locations.id', '=', 'assets.location_id')->orderBy('asset_locations.name', $order);
    }

    /**
     * Query builder scope to order on default
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByRTDLocationName($query, $order)
    {
        return $this->leftJoin('locations as rtd_asset_locations', 'rtd_asset_locations.id', '=', 'assets.rtd_location_id')->orderBy('rtd_asset_locations.name', $order);
    }

    /**
     * Query builder scope to order on supplier name
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderBySupplierName($query, $order)
    {
        return $this->leftJoin('suppliers as suppliers_assets', 'assets.supplier_id', '=', 'suppliers_assets.id')->orderBy('suppliers_assets.name', $order);
    }

    /**
     * Query builder scope to order on supplier name
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $order  Order
     * @return AssetBuilder Modified query builder
     */
    public function orderByJobTitle($query, $order)
    {
        return $this->leftJoin('users as users_sort', 'assets.assigned_to', '=', 'users_sort.id')->select('assets.*')->orderBy('users_sort.jobtitle', $order);
    }

    /**
     * Query builder scope to search on location ID
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $search  Search term
     * @return AssetBuilder Modified query builder
     */
    public function inLocation($query, $search)
    {
        return $this->where(
            function ($query) use ($search) {
                $query->whereHas(
                    'location', function ($query) use ($search) {
                        $query->where('locations.id', '=', $search);
                    }
                );
            }
        );

    }

    /**
     * Query builder scope to search on depreciation name
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string  $search  Search term
     * @return AssetBuilder Modified query builder
     */
    public function orderByDepreciationName($query, $search)
    {
        return $this->join('models', 'assets.model_id', '=', 'models.id')
            ->join('depreciations', 'models.depreciation_id', '=', 'depreciations.id')->where('models.depreciation_id', '=', $search);

    }
}
