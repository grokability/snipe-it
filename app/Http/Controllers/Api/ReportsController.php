<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Transformers\ActionlogsTransformer;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
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
        // returns the canonical fully-qualified class strings (array)
        // or null. Toolbar multi-selects submit as ?item_type[]=X&item_type[]=Y
        // and FilterRequest::prepareForValidation normalizes a scalar
        // into a single-element array, so downstream handling is
        // uniformly array-shaped.
        $targetClasses = null;
        $itemClasses = null;

        if ($request->filled('target_type')) {
            $targetClasses = $this->resolveActivityReportTypes((array) $request->input('target_type'));
            if ($targetClasses === null) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Invalid target_type'), 400);
            }
        }
        if ($request->filled('item_type')) {
            $itemClasses = $this->resolveActivityReportTypes((array) $request->input('item_type'));
            if ($itemClasses === null) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Invalid item_type'), 400);
            }
        }

        // Per-row view authz applies when the caller is drilling into
        // a specific record (item_id / target_id). Those paths only
        // make sense with a single type, so take the first type for the
        // view-authz find() call.

        if ((! Gate::allows('activity.view')) && (($targetClasses && $request->filled('target_id')) || ($itemClasses && $request->filled('item_id')))) {

            // Authorize EVERY resolved type submitted.
            if ($targetClasses && $request->filled('target_id')) {
                foreach ($targetClasses as $targetClass) {
                    $target = $targetClass::withTrashed()->find(request()->input('target_id'));
                    $this->authorize('view', $target ?? $targetClass);
                }
            }

            if ($itemClasses && $request->filled('item_id')) {
                foreach ($itemClasses as $itemClass) {
                    $item = $itemClass::withTrashed()->find(request()->input('item_id'));
                    $this->authorize('view', $item ?? $itemClass);
                }
            }

        } else {
            $this->authorize('activity.view');
        }

        $actionlogs = Actionlog::with('item', 'user', 'adminuser', 'target', 'location');

        if ($targetClasses && $request->filled('target_id')) {
            $actionlogs = $actionlogs->where('target_id', '=', $request->input('target_id'))
                ->whereIn('target_type', $targetClasses);
        } elseif ($targetClasses) {
            // Toolbar type-only filter (no specific id): narrow to all
            // rows whose target is of any chosen class. #17288.
            $actionlogs = $actionlogs->whereIn('target_type', $targetClasses);
        }

        if ($itemClasses && $request->filled('item_id')) {
            $actionlogs = $actionlogs->where(function ($query) use ($request, $itemClasses) {
                $query->where('item_id', '=', $request->input('item_id'))
                    ->whereIn('item_type', $itemClasses)
                    ->orWhere(function ($query) use ($request, $itemClasses) {
                        $query->where('target_id', '=', $request->input('item_id'))
                            ->whereIn('target_type', $itemClasses);
                    });
            });
        } elseif ($itemClasses) {
            // Toolbar type-only filter (no specific id): narrow to all
            // rows whose item is of any chosen class. #17288.
            $actionlogs = $actionlogs->whereIn('item_type', $itemClasses);
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $actionlogs->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('action_type')) {
            $actionlogs = $actionlogs->whereIn('action_type', (array) $request->input('action_type'));
        }

        if ($request->filled('created_by')) {
            $actionlogs = $actionlogs->whereIn('created_by', (array) $request->input('created_by'));
        }

        // Toolbar date range on action_logs.created_at. _from gates
        // start-of-day inclusive, _to gates end-of-day inclusive by
        // appending the day's last second. #17288.
        if ($request->filled('created_at_from')) {
            $actionlogs = $actionlogs->where('action_logs.created_at', '>=', $request->input('created_at_from').' 00:00:00');
        }
        if ($request->filled('created_at_to')) {
            $actionlogs = $actionlogs->where('action_logs.created_at', '<=', $request->input('created_at_to').' 23:59:59');
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
                // Three paths, cheapest first:
                //   1. Audit report (action_type=audit) is asset-only
                //      and uses the simple asset-name sort.
                //   2. Toolbar type filter pins a single item_type, so
                //      we only need to join that one table. Only applies
                //      when exactly one type is selected (multi-select
                //      sends an array).
                //   3. Everything else is polymorphic across every
                //      entity on the install and uses the per-type
                //      COALESCE sort.
                // #17288.
                $actionTypeInput = (array) $request->input('action_type');
                if (count($actionTypeInput) === 1 && $actionTypeInput[0] === 'audit') {
                    $actionlogs->OrderByItemName($order);
                } elseif ($itemClasses && count($itemClasses) === 1) {
                    $actionlogs->OrderByFilteredItemName($itemClasses[0], $order);
                } else {
                    $actionlogs->OrderByPolymorphicItemName($order);
                }
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'action_logs.created_at';
                $actionlogs = $actionlogs->orderBy($sort, $order);
                break;
        }

        $actionlogs = $actionlogs->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($actionlogs, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);

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
     * Delegates to ActivityItemType::tryFromAny which accepts either
     * shape (short name or FQCN) and recovers from the ucwords mangling
     * that used to turn `assetmodel` into the nonexistent
     * App\Models\Assetmodel. Null result means the caller should 400.
     */
    private function resolveActivityReportType(string $type): ?string
    {
        return \App\Enums\ActivityItemType::tryFromAny($type)?->class();
    }

    /**
     * Resolve an array of caller-supplied type inputs to fully qualified
     * class strings. Returns null if any input is invalid so the caller
     * can 400 the whole request rather than silently dropping bad values.
     *
     * @param  array<int, mixed>  $inputs
     * @return list<class-string>|null
     */
    private function resolveActivityReportTypes(array $inputs): ?array
    {
        $resolved = [];
        foreach ($inputs as $input) {
            if (! is_string($input) || $input === '') {
                continue;
            }
            $class = $this->resolveActivityReportType($input);
            if ($class === null) {
                return null;
            }
            $resolved[] = $class;
        }

        return $resolved === [] ? null : array_values(array_unique($resolved));
    }
}
