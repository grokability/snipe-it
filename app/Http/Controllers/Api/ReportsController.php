<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\ExpiringItemsTransformer;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportsController extends Controller
{
    /**
     * Returns Activity Report JSON.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function index(FilterRequest $request): JsonResponse|array
    {
        // Resolve incoming item_type / target_type against the
        // allowlist BEFORE any downstream use. This is both an authz
        // gate (denies probing arbitrary classes) and a correctness
        // fix (Helper::normalizeFullModelName uses ucwords() which
        // mangles CamelCase names, so `licenseseat` used to Fatal on
        // App\Models\Licenseseat::withTrashed()). resolveActivityReportType
        // returns the canonical fully-qualified class string or null.
        $targetClass = null;
        $itemClass = null;

        if ($request->filled('target_type')) {
            $targetClass = $this->resolveActivityReportType($request->input('target_type'));
            if ($targetClass === null) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Invalid target_type'), 400);
            }
        }
        if ($request->filled('item_type')) {
            $itemClass = $this->resolveActivityReportType($request->input('item_type'));
            if ($itemClass === null) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Invalid item_type'), 400);
            }
        }

        // If the user doesn't have permission to view the item or the target,
        // then they shouldn't be able to see the activity log for that item or target,
        // but if they have the general activity view permission,
        // then they can see all activity logs regardless of the item or target.
        if ((! Gate::allows('activity.view')) && (($targetClass && $request->filled('target_id')) || ($itemClass && $request->filled('item_id')))) {

            if ($targetClass && $request->filled('target_id')) {
                $target = $targetClass::withTrashed()->find(request()->input('target_id'));
                $this->authorize('view', $target ?? $targetClass);
            }

            if ($itemClass && $request->filled('item_id')) {
                $item = $itemClass::withTrashed()->find(request()->input('item_id'));
                $this->authorize('view', $item ?? $itemClass);
            }

        } else {
            $this->authorize('activity.view');
        }

        $actionlogs = Actionlog::with('item', 'user', 'adminuser', 'target', 'location');

        if ($targetClass && $request->filled('target_id')) {
            $actionlogs = $actionlogs->where('target_id', '=', $request->input('target_id'))
                ->where('target_type', '=', $targetClass);
        }

        if ($itemClass && $request->filled('item_id')) {
            $actionlogs = $actionlogs->where(function ($query) use ($request, $itemClass) {
                $query->where('item_id', '=', $request->input('item_id'))
                    ->where('item_type', '=', $itemClass)
                    ->orWhere(function ($query) use ($request, $itemClass) {
                        $query->where('target_id', '=', $request->input('item_id'))
                            ->where('target_type', '=', $itemClass);
                    });
            });
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $actionlogs->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('action_type')) {
            $actionlogs = $actionlogs->where('action_type', '=', $request->input('action_type'));
        }

        if ($request->filled('created_by')) {
            $actionlogs = $actionlogs->where('created_by', '=', $request->input('created_by'));
        }

        if ($request->filled('action_source')) {
            $actionlogs = $actionlogs->where('action_source', '=', $request->input('action_source'));
        }

        if ($request->filled('remote_ip')) {
            $actionlogs = $actionlogs->where('remote_ip', '=', $request->input('remote_ip'));
        }

        if ($request->filled('uploads')) {
            $actionlogs = $actionlogs->whereNotNull('filename');
        }

        $allowed_columns = [
            'id',
            'created_at',
            'target_id',
            'created_by',
            'accept_signature',
            'action_type',
            'note',
            'remote_ip',
            'user_agent',
            'target_type',
            'item_type',
            'action_source',
            'action_date',
            'location',
            'next_audit_date',
            'days_to_next_audit',
            'item',
        ];

        $total = $actionlogs->count();
        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = ($request->input('order') == 'asc') ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'created_by':
                $actionlogs->OrderByCreatedBy($order);
                break;
            case 'location':
                $actionlogs->OrderByLocation($order);
                break;
            case 'next_audit_date':
            case 'days_to_next_audit':
                // days_to_next_audit = next_audit_date - today, so it
                // sorts in the same order as next_audit_date. One
                // scope covers both cases; no separate expression.
                $actionlogs->OrderByAssetNextAuditDate($order);
                break;
            case 'item':
                $actionlogs->OrderByItemName($order);
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'action_logs.created_at';
                $actionlogs = $actionlogs->orderBy($sort, $order);
                break;
        }

        $actionlogs = $actionlogs->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($actionlogs, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);

    }

    public function expiringAssetsReport(Request $request)
    {
        $this->authorize('reports.view');
        $days = (int)$request->input('days', 30);
        $search = $request->input('search');
        $sort = $request->input('sort', 'asset_eol_date');
        $order = $request->input('order', 'asc');
        $offset = (int)$request->input('offset', 0);
        $limit = (int)$request->input('limit', 50);

        $query = Asset::getExpiringWarrantyOrEol($days);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('asset_tag', 'LIKE', "%{$search}%")
                    ->orWhereHas('model', function ($query) use ($search) {
                        $query
                            ->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('model_number', 'LIKE', "%{$search}%");
                    });
            });
        }

        $sortMap = [
            'id' => 'assets.id',
            'asset_tag' => 'assets.asset_tag',
            'purchase_date' => 'assets.purchase_date',
            'eol_date' => 'assets.asset_eol_date',
            'warranty_expires' => 'assets.warranty_expires',
        ];

        $query->orderBy(
            $sortMap[$sort] ?? 'assets.asset_eol_date',
            $order === 'desc' ? 'desc' : 'asc'
        );

        $total = $query->count();

        $assets = $query
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json(
            (new ExpiringItemsTransformer)->transformAssets($assets, $total)
        );
    }

    public function expiringLicensesReport(Request $request)
    {
        $this->authorize('reports.view');

        $days = (int)$request->input('days', 30);
        $includeExpired = $request->boolean('include_expired', false);
        $offset = (int)$request->input('offset', 0);
        $limit = (int)$request->input('limit', 50);
        $search = $request->input('search');
        $sort = $request->input('sort', 'expiration');
        $order = $request->input('order', 'asc');

        $query = License::query()->expiringLicenses($days, $includeExpired);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('licenses.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('licenses.serial', 'LIKE', '%' . $search . '%');
            });
        }
        $total = $query->count();

        $sortMap = [
            'id' => 'licenses.id',
            'name' => 'licenses.name',
            'purchase_date' => 'licenses.purchase_date',
            'expiration' => 'licenses.expiration_date',
            'termination_date' => 'licenses.termination_date',
        ];
        $sortColumn = $sortMap[$sort] ?? 'licenses.expiration_date';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? $order : 'desc';

        $licenses = $query
            ->orderBy($sortColumn, $order)
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json(
            (new ExpiringItemsTransformer)->transformLicenses($licenses, $total)
        );
    }

    /**
     * Returns time-series data for the reports overview charts.
     *
     * Accepts ?days=N (preset, default 30) OR ?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD.
     * Also returns the immediately preceding period of equal length for comparison lines.
     */
    public function activityChart(Request $request): JsonResponse
    {
        $this->authorize('reports.view');

        $allowedDays = [7, 14, 30, 60, 90, 180, 365];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $curStart = Carbon::parse($request->input('start_date'))->startOfDay();
            $curEnd = Carbon::parse($request->input('end_date'))->endOfDay();
            if ($curEnd->lt($curStart)) {
                [$curStart, $curEnd] = [$curEnd, $curStart];
            }
            $days = max(1, (int) $curStart->diffInDays($curEnd) + 1);
        } else {
            $days = in_array((int) $request->input('days'), $allowedDays) ? (int) $request->input('days') : 30;
            $curEnd = Carbon::today()->endOfDay();
            $curStart = Carbon::today()->subDays($days - 1)->startOfDay();
        }

        $prevEnd = $curStart->copy()->subSecond()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1)->startOfDay();

        $buildDates = function (Carbon $start, Carbon $end): array {
            $dates = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dates[] = $d->toDateString();
            }

            return $dates;
        };

        $curDates = $buildDates($curStart, $curEnd);
        $prevDates = $buildDates($prevStart, $prevEnd);

        $pluckAction = function (string $actionType, Carbon $start, Carbon $end): array {
            return Actionlog::where('action_type', $actionType)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        };

        // withTrashed() ensures records deleted after creation still appear in their creation-period counts.
        $pluckCreated = function (string $modelClass, Carbon $start, Carbon $end): array {
            return $modelClass::withTrashed()
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        };

        // Maintenance has no company_id column and no CompanyableTrait, so scope through
        // its asset relationship — whereHas('asset') applies Asset's FMCS global scope.
        $pluckMaintenances = function (Carbon $start, Carbon $end): array {
            return Maintenance::withTrashed()
                ->whereHas('asset')
                ->whereBetween('maintenances.created_at', [$start, $end])
                ->selectRaw('DATE(maintenances.created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        };

        // Filters by both action_type and item_type for per-category checkout/checkin counts.
        $pluckActionByType = function (string $actionType, string $modelClass, Carbon $start, Carbon $end): array {
            return Actionlog::where('action_type', $actionType)
                ->where('item_type', $modelClass)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        };

        $pluckDeletedUsers = function (Carbon $start, Carbon $end): array {
            return User::withTrashed()
                ->whereNotNull('deleted_at')
                ->whereBetween('deleted_at', [$start, $end])
                ->selectRaw('DATE(deleted_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        };

        // Catches both 'checkin' and 'checkin from' action types used across different item types.
        $pluckCheckinsByType = function (string $modelClass, Carbon $start, Carbon $end): array {
            return Actionlog::whereIn('action_type', ['checkin', 'checkin from'])
                ->where('item_type', $modelClass)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        };

        $fill = fn (array $raw, array $dates) => array_map(fn ($d) => (int) ($raw[$d] ?? 0), $dates);

        $datasets = [];
        foreach ([
            'new_users' => fn ($s, $e) => $pluckCreated(User::class, $s, $e),
            'deleted_users' => fn ($s, $e) => $pluckDeletedUsers($s, $e),
            'asset_checkouts' => fn ($s, $e) => $pluckActionByType('checkout', Asset::class, $s, $e),
            'asset_checkins' => fn ($s, $e) => $pluckCheckinsByType(Asset::class, $s, $e),
            'new_assets' => fn ($s, $e) => $pluckCreated(Asset::class, $s, $e),
            'new_maintenances' => fn ($s, $e) => $pluckMaintenances($s, $e),
            'new_audits' => fn ($s, $e) => $pluckAction('audit', $s, $e),
            'component_checkouts' => fn ($s, $e) => $pluckActionByType('checkout', Component::class, $s, $e),
            'component_checkins' => fn ($s, $e) => $pluckCheckinsByType(Component::class, $s, $e),
            'new_components' => fn ($s, $e) => $pluckCreated(Component::class, $s, $e),
            'consumable_checkouts' => fn ($s, $e) => $pluckActionByType('checkout', Consumable::class, $s, $e),
            'consumable_checkins' => fn ($s, $e) => $pluckCheckinsByType(Consumable::class, $s, $e),
            'new_consumables' => fn ($s, $e) => $pluckCreated(Consumable::class, $s, $e),
            'license_checkouts' => fn ($s, $e) => $pluckActionByType('checkout', LicenseSeat::class, $s, $e),
            'license_checkins' => fn ($s, $e) => $pluckCheckinsByType(LicenseSeat::class, $s, $e),
            'new_licenses' => fn ($s, $e) => $pluckCreated(License::class, $s, $e),
            'accessory_checkouts' => fn ($s, $e) => $pluckActionByType('checkout', Accessory::class, $s, $e),
            'accessory_checkins' => fn ($s, $e) => $pluckCheckinsByType(Accessory::class, $s, $e),
            'new_accessories' => fn ($s, $e) => $pluckCreated(Accessory::class, $s, $e),
        ] as $key => $query) {
            $datasets[$key] = $fill($query($curStart, $curEnd), $curDates);
            $datasets['prev_'.$key] = $fill($query($prevStart, $prevEnd), $prevDates);
        }

        return response()->json(array_merge([
            'labels' => array_map(fn ($d) => Carbon::parse($d)->format('M j'), $curDates),
            'prev_label' => $prevStart->format('M j').' – '.$prevEnd->format('M j'),
        ], $datasets));
    }

    /**
     * Match caller-supplied item_type / target_type input against the
     * activity-report allowlist and return the canonical class string.
     * Delegates short-name → FQCN to Helper::normalizeFullModelName so
     * this endpoint keeps accepting either shape (`asset` or
     * `App\Models\Asset`), same as every other call site of that helper.
     * The final match is case-insensitive against the allowlist to
     * recover from normalizeFullModelName's ucwords() step, which
     * mangles CamelCase names (`licenseseat` becomes the nonexistent
     * App\Models\Licenseseat and would have fatal'd downstream).
     * Null result means the caller should 400.
     */
    private function resolveActivityReportType(string $type): ?string
    {
        $candidate = Helper::normalizeFullModelName($type);
        foreach (self::getActivityReportClassAllowlist() as $allowed) {
            if (strcasecmp($candidate, $allowed) === 0) {
                return $allowed;
            }
        }

        return null;
    }
}
