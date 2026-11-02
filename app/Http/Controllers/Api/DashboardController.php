<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\ActionlogsTransformer;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CalendarEvent;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Endpoints for the non-admin dashboard widgets.
 *
 * The general-purpose Blah::index endpoints stay gated on their
 * original per-domain permissions.
 */
class DashboardController extends Controller
{
    /**
     * Actionlog rows the caller is allowed to see, latest first.
     * Backs the Recent Activity widget on the non-admin dashboard
     * AND the full reports/activity page when the caller lacks
     * activity.view (a scoped viewer arrives at the report via the
     * widget's View-all button).
     *
     * Accepted query params:
     *   - limit (capped at 500)
     *   - offset
     *   - search (free-text across Actionlog's searchable columns)
     *
     * search runs AFTER the viewable-type filter, so a scoped viewer
     * can only search within the row set they were already allowed
     * to see. Per-attribute probe params like `action_type`,
     * `created_by`, `remote_ip`, `item_type`, and `target_type`
     * are still ignored so callers cannot narrow to a specific
     * attribute the widget / report does not surface.
     */
    public function activity(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('canViewUsersAndCheckoutables'), 403);

        // Restrict feed to actionlogs whose item_type or target_type
        // the caller has explicit view on. HasCalendarEvents sources
        // plus the three non-calendar checkoutables (Accessory /
        // Consumable / Component) covers every polymorphic subject
        // the widget shows.
        $candidateTypes = array_merge(
            CalendarEvent::sourceModels(),
            [Accessory::class, Consumable::class, Component::class],
        );
        $viewableTypes = array_values(array_filter(
            $candidateTypes,
            fn ($class) => Gate::allows('view', $class),
        ));

        // `activity.view` holders (admins / reports users) hit this
        // widget from the same dashboard, so let them through without
        // the type filter. They would see everything on the general
        // report page anyway.
        $hasFullActivityView = Gate::allows('activity.view');

        $query = Actionlog::with('item', 'user', 'adminuser', 'target', 'location');

        if (! $hasFullActivityView) {
            $query->where(function ($sub) use ($viewableTypes) {
                $sub->whereIn('item_type', $viewableTypes)
                    ->orWhereIn('target_type', $viewableTypes);
            });
        }

        // Free-text search runs against Actionlog's Searchable trait
        // AFTER the viewable-type filter above, so it can only match
        // rows the caller was already allowed to see. Safe to expose
        // even to scoped viewers.
        if ($request->filled('search')) {
            $query->TextSearch($request->input('search'));
        }

        $limit = max(1, min(500, (int) $request->input('limit', 25)));
        $offset = max(0, (int) $request->input('offset', 0));

        $total = $query->count();
        $rows = $query->orderByDesc('action_logs.created_at')
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json(
            (new ActionlogsTransformer)->transformActionlogs($rows, $total),
            200,
            ['Content-Type' => 'application/json;charset=utf8'],
            JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * Category summary rows.
     */
    public function categories(Request $request): JsonResponse
    {
        abort_unless(
            Gate::allows('view', Category::class) || Gate::allows('canViewUsersAndCheckoutables'),
            403,
        );

        $viewableTypes = [
            'asset' => Asset::class,
            'accessory' => Accessory::class,
            'consumable' => Consumable::class,
            'component' => Component::class,
            'license' => License::class,
        ];
        $viewableTypeKeys = array_keys(array_filter(
            $viewableTypes,
            fn (string $modelClass) => Gate::allows('view', $modelClass),
        ));

        $query = Category::select('id', 'name', 'category_type', 'tag_color');

        // Per-type withCount blocks. Assets go through showableAssets
        // to honor show_archived_in_list. Every count query FMCS-scopes
        // through each target model's CompanyableTrait, so a scoped
        // viewer only counts items in their own company. The sort
        // allowlist is built alongside so a caller can only ORDER BY
        // count columns that were actually added to the SELECT list.
        // Otherwise ORDER BY assets_count hits an unknown-column
        // error for viewers who cannot view Asset.
        $countSortColumns = [];
        if (Gate::allows('view', Asset::class)) {
            $query->withCount('showableAssets as assets_count');
            $countSortColumns[] = 'assets_count';
        }
        // Accessory / Consumable / Component counts are sum(qty) over
        // live non-archived children to match the top-box semantic on
        // the main dashboard. SoftDeletes auto-excludes trashed rows,
        // notArchived() excludes retired-but-live ones. License stays
        // as a parent-row count because licensesController hasn't been
        // audited for the same semantic yet.
        if (Gate::allows('view', Accessory::class)) {
            $query->withSum(['accessories as accessories_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'accessories_count';
        }
        if (Gate::allows('view', Consumable::class)) {
            $query->withSum(['consumables as consumables_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'consumables_count';
        }
        if (Gate::allows('view', Component::class)) {
            $query->withSum(['components as components_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'components_count';
        }
        if (Gate::allows('view', License::class)) {
            $query->withCount('licenses as licenses_count');
            $countSortColumns[] = 'licenses_count';
        }

        // Scoped viewers see only category rows whose category_type
        // matches a checkoutable they can view. Callers with explicit
        // categories.view stay unfiltered.
        if (! Gate::allows('view', Category::class)) {
            $query->whereIn('category_type', $viewableTypeKeys);
        }

        if ($request->filled('search')) {
            $query->TextSearch($request->input('search'));
        }

        $this->applyBoundedSort($query, $request, array_merge(['name', 'category_type'], $countSortColumns), 'name');

        // category_type value → withCount alias. English pluralization
        // is irregular enough (accessory → accessories, not accessorys)
        // that a lookup is safer than a `{$type}s_count` template.
        $countAliasFor = [
            'asset' => 'assets_count',
            'accessory' => 'accessories_count',
            'consumable' => 'consumables_count',
            'component' => 'components_count',
            'license' => 'licenses_count',
        ];

        return response()->json($this->paginateRows($query, $request, function ($category) use ($viewableTypeKeys, $countAliasFor) {
            $row = [
                'id' => (int) $category->id,
                'name' => e($category->name),
                'category_type' => e($category->category_type),
                'tag_color' => $category->tag_color ? e($category->tag_color) : null,
                'available_actions' => [
                    'view' => Gate::allows('view', $category),
                ],
            ];
            foreach ($viewableTypeKeys as $type) {
                $alias = $countAliasFor[$type];
                $row[$alias] = (int) ($category->{$alias} ?? 0);
            }

            return $row;
        }));
    }

    /**
     * Company summary rows.
     */
    public function companies(Request $request): JsonResponse
    {
        abort_unless(
            Gate::allows('view', Company::class) || Gate::allows('canViewUsersAndCheckoutables'),
            403,
        );

        // Build the withCount list and the sortable-column allowlist
        // in lockstep so a caller can only ORDER BY a count column
        // that actually made it into the SELECT list. See categories
        // above for the unknown-column error this guards against.
        // tag_color goes on the SELECT list so the row link
        // formatter can prepend the color square whether the caller
        // has view on this company or not.
        $query = Company::select('id', 'name', 'tag_color');
        $countSortColumns = [];

        if (Gate::allows('view', Asset::class)) {
            $query->withCount(['assets as assets_count' => fn ($q) => $q->AssetsForShow()]);
            $countSortColumns[] = 'assets_count';
        }
        // See categories() above for the sum(qty) rationale on the
        // three checkoutable types.
        if (Gate::allows('view', Accessory::class)) {
            $query->withSum(['accessories as accessories_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'accessories_count';
        }
        if (Gate::allows('view', Consumable::class)) {
            $query->withSum(['consumables as consumables_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'consumables_count';
        }
        if (Gate::allows('view', Component::class)) {
            $query->withSum(['components as components_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'components_count';
        }
        if (Gate::allows('view', License::class)) {
            $query->withCount('licenses as licenses_count');
            $countSortColumns[] = 'licenses_count';
        }
        if (Gate::allows('view', User::class)) {
            $query->withCount('users as users_count');
            $countSortColumns[] = 'users_count';
        }

        if ($request->filled('search')) {
            $query->TextSearch($request->input('search'));
        }

        $this->applyBoundedSort($query, $request, array_merge(['name'], $countSortColumns), 'name');

        return response()->json($this->paginateRows($query, $request, function ($company) {
            $row = [
                'id' => (int) $company->id,
                'name' => e($company->name),
                'tag_color' => $company->tag_color ? e($company->tag_color) : null,
                'available_actions' => [
                    'view' => Gate::allows('view', $company),
                ],
            ];
            foreach (['users_count', 'assets_count', 'accessories_count', 'consumables_count', 'components_count', 'licenses_count'] as $key) {
                if (array_key_exists($key, $company->getAttributes()) || isset($company->{$key})) {
                    $row[$key] = (int) $company->{$key};
                }
            }

            return $row;
        }));
    }

    /**
     * Location summary rows.
     */
    public function locations(Request $request): JsonResponse
    {
        abort_unless(
            Gate::allows('view', Location::class) || Gate::allows('canViewUsersAndCheckoutables'),
            403,
        );

        // See categories/companies above: allowlist must track the
        // withCount list so a caller can only ORDER BY count columns
        // that were actually SELECTed. tag_color goes on the SELECT
        // so the row link formatter can render the color square
        // whether or not the caller can view this location.
        $query = Location::select('id', 'name', 'tag_color');
        $countSortColumns = [];

        if (Gate::allows('view', Asset::class)) {
            $query->withCount(['assets as assets_count' => fn ($q) => $q->AssetsForShow()])
                ->withCount(['assignedAssets as assigned_assets_count' => fn ($q) => $q->AssetsForShow()]);
            $countSortColumns[] = 'assets_count';
            $countSortColumns[] = 'assigned_assets_count';
        }
        // See categories() above for the sum(qty) rationale on the
        // three checkoutable types.
        if (Gate::allows('view', Accessory::class)) {
            $query->withSum(['accessories as accessories_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'accessories_count';
        }
        if (Gate::allows('view', Consumable::class)) {
            $query->withSum(['consumables as consumables_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'consumables_count';
        }
        if (Gate::allows('view', Component::class)) {
            $query->withSum(['components as components_count' => fn ($q) => $q->notArchived()], 'qty');
            $countSortColumns[] = 'components_count';
        }
        if (Gate::allows('view', User::class)) {
            $query->withCount('users as users_count');
            $countSortColumns[] = 'users_count';
        }

        if ($request->filled('search')) {
            $query->TextSearch($request->input('search'));
        }

        $this->applyBoundedSort($query, $request, array_merge(['name'], $countSortColumns), 'name');

        return response()->json($this->paginateRows($query, $request, function ($location) {
            $row = [
                'id' => (int) $location->id,
                'name' => e($location->name),
                'tag_color' => $location->tag_color ? e($location->tag_color) : null,
                'available_actions' => [
                    'view' => Gate::allows('view', $location),
                ],
            ];
            // array_key_exists on getAttributes() rather than isset
            // because withSum sets the alias to null when the relation
            // has no matching children, and isset would drop the key
            // in that case (yielding a row with a missing column that
            // the admin reads as "zero but hidden"). The key must stay
            // visible as 0 so the dashboard column always renders.
            foreach (['assets_count', 'assigned_assets_count', 'accessories_count', 'consumables_count', 'components_count', 'users_count'] as $key) {
                if (array_key_exists($key, $location->getAttributes())) {
                    $row[$key] = (int) $location->{$key};
                }
            }

            return $row;
        }));
    }

    /**
     * Apply the request's `sort` / `order` params against an
     * allowlist. Anything outside the allowlist falls back to
     * $defaultColumn. `order` defaults to desc, matching bs-table's
     * assets_count-first widget config.
     */
    private function applyBoundedSort($query, Request $request, array $allowed, string $defaultColumn): void
    {
        $sort = $request->input('sort');
        $column = in_array($sort, $allowed, true) ? $sort : $defaultColumn;
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($column, $order);
    }

    /**
     * Slice the query with request-provided limit / offset (limit
     * capped at 50) and run the row mapper over the resulting page.
     * Returns the standard datatables shape: total + rows.
     */
    private function paginateRows($query, Request $request, callable $rowMapper): array
    {
        $total = (clone $query)->count();
        $limit = max(1, min(50, (int) $request->input('limit', 25)));
        $offset = max(0, (int) $request->input('offset', 0));

        $items = $query->skip($offset)->take($limit)->get();

        return [
            'total' => $total,
            'rows' => array_map($rowMapper, $items->all()),
        ];
    }
}
