<?php

namespace App\Models\Builders;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @template TModel of Asset
 *
 * @extends Builder<TModel>
 */
class AssetQueryBuilder extends Builder
{
    public function hardware(): self
    {
        return $this->where('physical', '=', '1');
    }

    public function pending(): self
    {
        // Pluck IDs then whereIn. Do NOT replace with whereHas. whereHas
        // generates a correlated EXISTS per row and causes severe
        // slowdowns in withCount contexts.
        $ids = Statuslabel::idsFor('pending');

        return $this->whereIn('assets.status_id', $ids->isEmpty() ? [0] : $ids);
    }

    public function assetsByLocation(Location $location): self
    {
        return $this->where(function ($query) use ($location) {
            $query->whereHas('assignedTo', function ($query) use ($location) {
                $query->where([
                    ['users.location_id', '=', $location->id],
                    ['assets.assigned_type', '=', User::class],
                ])->orWhere([
                    ['locations.id', '=', $location->id],
                    ['assets.assigned_type', '=', Location::class],
                ])->orWhere([
                    ['assets.rtd_location_id', '=', $location->id],
                    ['assets.assigned_type', '=', Asset::class],
                ]);
            })->orWhere(function ($query) use ($location) {
                $query->where('assets.rtd_location_id', '=', $location->id);
                $query->whereNull('assets.assigned_to');
            });
        });
    }

    public function rtd(): self
    {
        $ids = Statuslabel::idsFor('deployable');

        return $this->whereNull('assets.assigned_to')
            ->whereIn('assets.status_id', $ids->isEmpty() ? [0] : $ids);
    }

    public function undeployable(): self
    {
        $ids = Statuslabel::idsFor('undeployable');

        return $this->whereIn('assets.status_id', $ids->isEmpty() ? [0] : $ids);
    }

    public function notArchived(): self
    {
        $ids = Statuslabel::idsFor('not_archived');

        return $this->whereIn('assets.status_id', $ids->isEmpty() ? [0] : $ids);
    }

    public function dueForAudit(Setting $settings): self
    {
        $interval = (int) $settings->audit_warning_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval)->format('Y-m-d');

        return $this->whereNotNull('assets.next_audit_date')
            ->whereBetween('assets.next_audit_date', [$today->format('Y-m-d'), $interval_date])
            ->where('assets.archived', '=', 0)
            ->notArchived();
    }

    public function overdueForAudit(): self
    {
        return $this->whereNotNull('assets.next_audit_date')
            ->where('assets.next_audit_date', '<', Carbon::now()->format('Y-m-d'))
            ->where('assets.archived', '=', 0)
            ->notArchived();
    }

    public function pastEol(): self
    {
        return $this->whereNotNull('assets.asset_eol_date')
            ->where('assets.asset_eol_date', '<', Carbon::now()->format('Y-m-d'))
            ->notArchived();
    }

    public function dueOrOverdueForAudit(Setting $settings): self
    {
        return $this->where(function ($query) {
            $query->overdueForAudit();
        })->orWhere(function ($query) use ($settings) {
            $query->dueForAudit($settings);
        });
    }

    public function dueForCheckin(Setting $settings): self
    {
        $interval = (int) $settings->due_checkin_days ?? 0;
        // expected_checkin is a DATETIME. Use startOfDay / endOfDay
        // explicitly so an asset due back on the last day of the
        // interval at (say) 2:30 PM still falls inside the range.
        // Formatting only Y-m-d silently widens to midnight and would
        // exclude anything later that day.
        $windowStart = Carbon::now()->startOfDay();
        $windowEnd = Carbon::now()->addDays($interval)->endOfDay();

        return $this->whereNotNull('assets.expected_checkin')
            ->whereBetween('assets.expected_checkin', [$windowStart, $windowEnd])
            ->where('assets.archived', '=', 0)
            ->whereNotNull('assets.assigned_to')
            ->notArchived();
    }

    public function overdueForCheckin(): self
    {
        // expected_checkin is a DATETIME, so "overdue" is time-aware: an
        // asset due back this morning at 09:00 is overdue by 09:01.
        // Compare against Carbon::now() instead of today's midnight so
        // mid-day dashboard views reflect what's actually late right
        // now.
        return $this->whereNotNull('assets.expected_checkin')
            ->where('assets.expected_checkin', '<', Carbon::now())
            ->where('assets.archived', '=', 0)
            ->whereNotNull('assets.assigned_to')
            ->notArchived();
    }

    public function dueOrOverdueForCheckin(Setting $settings): self
    {
        return $this->where(function ($query) {
            $query->overdueForCheckin();
        })->orWhere(function ($query) use ($settings) {
            $query->dueForCheckin($settings);
        });
    }

    public function assetsForShow(): self
    {
        // Pluck IDs then whereIn. Do NOT replace with whereHas.
        if (Setting::getSettings()->show_archived_in_list != 1) {
            $validStatusIds = Statuslabel::idsFor('not_archived');

            return $this->whereIn('assets.status_id', $validStatusIds->isEmpty() ? [0] : $validStatusIds);
        }

        return $this;
    }

    public function archived(): self
    {
        $ids = Statuslabel::idsFor('archived');

        return $this->whereIn('assets.status_id', $ids->isEmpty() ? [0] : $ids);
    }

    public function deployed(): self
    {
        return $this->where('assigned_to', '>', '0');
    }

    public function requestable(): self
    {
        $table = $this->getModel()->getTable();

        return Company::scopeCompanyables($this->where($table.'.requestable', '=', 1))
            ->whereHas('status', function ($query) {
                $query->where(function ($query) {
                    $query->where('deployable', '=', 1)
                        ->where('archived', '=', 0); // you definitely can't request something that's archived
                })->orWhere('pending', '=', 1); // we've decided that even though an asset may be 'pending', you can still request it
            });
    }

    /**
     * @param  array<int, int>  $modelIdListing
     */
    public function inModelList(array $modelIdListing): self
    {
        return $this->whereIn('assets.model_id', $modelIdListing);
    }

    public function notYetAccepted(): self
    {
        return $this->where('accepted', '=', 'pending');
    }

    public function rejected(): self
    {
        return $this->where('accepted', '=', 'rejected');
    }

    public function accepted(): self
    {
        return $this->where('accepted', '=', 'accepted');
    }

    public function assignedSearch(string $search): self
    {
        $search = explode(' OR ', $search);

        return $this->leftJoin('users as assets_users', function ($leftJoin) {
            $leftJoin->on('assets_users.id', '=', 'assets.assigned_to')
                ->where('assets.assigned_type', '=', User::class);
        })->leftJoin('locations as assets_locations', function ($leftJoin) {
            $leftJoin->on('assets_locations.id', '=', 'assets.assigned_to')
                ->where('assets.assigned_type', '=', Location::class);
        })->leftJoin('assets as assigned_assets', function ($leftJoin) {
            $leftJoin->on('assigned_assets.id', '=', 'assets.assigned_to')
                ->where('assets.assigned_type', '=', Asset::class);
        })->where(function ($query) use ($search) {
            foreach ($search as $search) {
                $query->whereHas('model', function ($query) use ($search) {
                    $query->whereHas('category', function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('categories.name', 'LIKE', '%'.$search.'%')
                                ->orWhere('models.name', 'LIKE', '%'.$search.'%')
                                ->orWhere('models.model_number', 'LIKE', '%'.$search.'%');
                        });
                    });
                })->orWhereHas('model', function ($query) use ($search) {
                    $query->whereHas('manufacturer', function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                        });
                    });
                })->orWhere(function ($query) use ($search) {
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
                })->orWhere('assets.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('assets.asset_tag', 'LIKE', '%'.$search.'%')
                    ->orWhere('assets.serial', 'LIKE', '%'.$search.'%')
                    ->orWhere('assets.order_number', 'LIKE', '%'.$search.'%')
                    ->orWhere('assets.notes', 'LIKE', '%'.$search.'%');
            }
        })->withTrashed()->whereNull('assets.deleted_at'); // workaround for laravel bug
    }

    /**
     * Query builder scope to search the department ID of users
     * assigned to assets.
     *
     * @param  array<int, int>  $search
     */
    public function checkedOutToTargetInDepartment(array $search): self
    {
        return $this->leftJoin('users as assets_dept_users', function ($leftJoin) {
            $leftJoin->on('assets_dept_users.id', '=', 'assets.assigned_to')
                ->where('assets.assigned_type', '=', User::class);
        })->where(function ($query) use ($search) {
            $query->whereIn('assets_dept_users.department_id', $search);
        })->withTrashed()->whereNull('assets.deleted_at'); // workaround for laravel bug
    }

    public function orderModels(string $order): self
    {
        return $this
            ->join('models as asset_models', 'assets.model_id', '=', 'asset_models.id')
            ->orderBy('asset_models.name', $order);
    }

    public function orderModelNumber(string $order): self
    {
        return $this
            ->leftJoin('models as model_number_sort', 'assets.model_id', '=', 'model_number_sort.id')
            ->orderBy('model_number_sort.model_number', $order);
    }

    public function orderByCreatedByName(string $order): self
    {
        // addSelect rather than select, so prior withCount subqueries
        // (which append their count-expressions to the select list)
        // don't get wiped by the join and trigger an N+1 on the
        // reload.
        return $this
            ->leftJoin('users as admin_sort', 'assets.created_by', '=', 'admin_sort.id')
            ->addSelect('assets.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    public function orderAssigned(string $order): self
    {
        return $this
            ->leftJoin('users as users_sort', function ($join) {
                $join->on('assets.assigned_to', '=', 'users_sort.id')
                    ->where('assets.assigned_type', '=', User::class);
            })
            ->addSelect('assets.*')
            ->orderBy('users_sort.first_name', $order)
            ->orderBy('users_sort.last_name', $order);
    }

    public function orderStatus(string $order): self
    {
        return $this
            ->join('status_labels as status_sort', 'assets.status_id', '=', 'status_sort.id')
            ->orderBy('status_sort.name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('companies as company_sort', 'assets.company_id', '=', 'company_sort.id')
            ->orderBy('company_sort.name', $order);
    }


    /*
     * Sort by the number of license seats currently assigned
     */
    public function orderLicensesCount(string $order): self
    {
        $sub = DB::table('license_seats')
            ->select(DB::raw('count(*)'))
            ->whereColumn('license_seats.asset_id', 'assets.id');

        return $this->orderBy($sub, $order);
    }

    /*
     * Sort by the number of components currently checked out
     */
    public function orderComponentsCount(string $order): self
    {
        $sub = DB::table('components_assets')
            ->select(DB::raw('count(*)'))
            ->whereColumn('components_assets.asset_id', 'assets.id');

        return $this->orderBy($sub, $order);
    }

    /*
     * Sort by the number of maintenances
     */
    public function orderMaintenancesCount(string $order): self
    {
        $sub = DB::table('maintenances')
            ->select(DB::raw('count(*)'))
            ->whereColumn('maintenances.item_id', 'assets.id')
            ->where('maintenances.item_type', Asset::class);

        return $this->orderBy($sub, $order);
    }

    /*
     * Sort by the number of child assets
     */
    public function orderAssignedAssetsCount(string $order): self
    {
        $sub = DB::table('assets AS child_assets')
            ->select(DB::raw('count(*)'))
            ->whereColumn('child_assets.assigned_to', 'assets.id')
            ->where('child_assets.assigned_type', Asset::class)
            ->whereNull('child_assets.deleted_at');

        return $this->orderBy($sub, $order);
    }

    /**
     * Sort by a sync-adapter external-source column (primary_mac,
     * primary_ip, os, os_version, last_seen). LeftJoin so unsynced
     * assets sort as nulls rather than dropping out. Join is on the
     * unique asset_id index in asset_external_sources, so cost is an
     * index lookup per row.
     *
     * Column argument is whitelisted by the caller
     * (AssetsController's sort switch) so it's never user-controlled
     * at this layer, but we still validate against the known column
     * set as defense-in-depth against a caller regression.
     */
    public function orderExternalSource(string $order, string $column): self
    {
        if (! in_array($column, ['primary_mac', 'primary_ip', 'os', 'os_version', 'last_seen'], true)) {
            return $this;
        }

        return $this
            ->leftJoin('asset_external_sources as ext_src_sort', 'assets.id', '=', 'ext_src_sort.asset_id')
            ->orderBy('ext_src_sort.'.$column, $order);
    }

    public function inCategory(string|array $category_id): self
    {
        return $this
            ->join('models as category_models', 'assets.model_id', '=', 'category_models.id')
            ->join('categories', 'category_models.category_id', '=', 'categories.id')
            ->whereIn('category_models.category_id', (! is_array($category_id) ? explode(',', $category_id) : $category_id));
    }

    public function byManufacturer(string|array $manufacturer_id): self
    {
        return $this
            ->join('models', 'assets.model_id', '=', 'models.id')
            ->join('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')
            ->whereIn('models.manufacturer_id', (! is_array($manufacturer_id) ? explode(',', $manufacturer_id) : $manufacturer_id));
    }

    public function orderCategory(string $order): self
    {
        return $this
            ->join('models as order_model_category', 'assets.model_id', '=', 'order_model_category.id')
            ->join('categories as category_order', 'order_model_category.category_id', '=', 'category_order.id')
            ->orderBy('category_order.name', $order);
    }

    public function orderManufacturer(string $order): self
    {
        return $this
            ->join('models as order_asset_model', 'assets.model_id', '=', 'order_asset_model.id')
            ->leftJoin('manufacturers as manufacturer_order', 'order_asset_model.manufacturer_id', '=', 'manufacturer_order.id')
            ->orderBy('manufacturer_order.name', $order);
    }

    public function orderLocation(string $order): self
    {
        return $this
            ->leftJoin('locations as asset_locations', 'asset_locations.id', '=', 'assets.location_id')
            ->orderBy('asset_locations.name', $order);
    }

    public function orderRtdLocation(string $order): self
    {
        return $this
            ->leftJoin('locations as rtd_asset_locations', 'rtd_asset_locations.id', '=', 'assets.rtd_location_id')
            ->orderBy('rtd_asset_locations.name', $order);
    }

    public function orderSupplier(string $order): self
    {
        return $this
            ->leftJoin('suppliers as suppliers_assets', 'assets.supplier_id', '=', 'suppliers_assets.id')
            ->orderBy('suppliers_assets.name', $order);
    }

    public function orderByJobTitle(string $order): self
    {
        return $this
            ->leftJoin('users as users_sort', function ($join) {
                $join->on('assets.assigned_to', '=', 'users_sort.id')
                    ->where('assets.assigned_type', '=', User::class);
            })
            ->addSelect('assets.*')
            ->orderBy('users_sort.jobtitle', $order);
    }

    public function byLocationId(int $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->whereHas('location', function ($query) use ($search) {
                $query->where('locations.id', '=', $search);
            });
        });
    }

    public function byDepreciationId(int $search): self
    {
        return $this
            ->join('models', 'assets.model_id', '=', 'models.id')
            ->join('depreciations', 'models.depreciation_id', '=', 'depreciations.id')
            ->where('models.depreciation_id', '=', $search);
    }
}
