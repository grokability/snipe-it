<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Consumables\BulkConsumablesController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustQuantityRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\StoreConsumableRequest;
use App\Http\Traits\HandlesAdjustQuantity;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\ConsumablesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Actionlog;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Setting;
use App\Models\User;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ConsumablesController extends Controller
{
    use HandlesAdjustQuantity;

    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function index(FilterRequest $request): array
    {
        $this->authorize('index', Consumable::class);

        // See ComponentsController for the orderItems.order.supplier eager-load rationale.
        // consumables_users_count feeds the stockroom-remaining math (qty minus total ever
        // distributed, unchanged by mark-consumed). outstanding_assignments_count feeds
        // isDeletable() and the archive guard so neither fires a per-row count query.
        //
        // Both counts walk the consumableAssignments hasMany directly (which wraps
        // consumables_users) rather than the unconstrainedUsers belongsToMany, so the
        // subquery is a single-table COUNT against consumables_users.consumable_id
        // instead of INNER JOIN users. The users JOIN was dead weight here anyway
        // (we only want a count of pivot rows), and dropping it is a measurable win
        // on hundreds-of-thousands-of-consumables fleets.
        $consumables = Consumable::with('company', 'location', 'category', 'defaultSupplier', 'manufacturer', 'orderItems.order.supplier')
            ->withCount('consumableAssignments as consumables_users_count')
            ->withCount('outstandingAssignments as outstanding_assignments_count');

        // This array is what determines which fields should be allowed to be sorted on ON the table itself.
        // These must match a column on the consumables table directly.
        $allowed_columns = [
            'id',
            'name',
            'min_amt',
            'purchase_date',
            'purchase_cost',
            'company',
            'category',
            'model_number',
            'item_no',
            'manufacturer',
            'location',
            'qty',
            'image',
            // These are *relationships* so we wouldn't normally include them in this array,
            // since they would normally create a `column not found` error,
            // BUT we account for them in the ordering switch down at the end of this method
            // DO NOT ADD ANYTHING TO THIS LIST WITHOUT CHECKING THE ORDERING SWITCH BELOW!
            'company',
            'location',
            'category',
            'supplier',
            'manufacturer',
        ];

        // Archived / soft-deleted scope.
        switch ($request->input('status')) {
            case 'Archived':
                $consumables->archived();
                break;
            case 'Deleted':
                $consumables->onlyTrashed();
                break;
            default:
                $consumables->consumablesForShow();
                break;
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $consumables->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('name')) {
            $consumables->where('consumables.name', '=', $request->input('name'));
        }

        if ($request->input('requestable') == 'true') {
            $consumables->where('consumables.requestable', '=', '1');
        }

        if ($request->input('archived') == 'true') {
            $consumables->archived();
        }

        if ($request->filled('company_id')) {
            // expand_company_hierarchy=1 opts the company show-page tabs into the
            // parent/child rollup so a child shows items inherited from its parent.
            if ($request->boolean('expand_company_hierarchy')) {
                $consumables->whereIn('consumables.company_id', Company::reachableCompanyIds($request->input('company_id')));
            } else {
                $consumables->where('consumables.company_id', '=', $request->input('company_id'));
            }
        }

        if ($request->filled('order_number')) {
            // Reroute through the HasOrders orders() HasManyThrough since
            // the parent consumables.order_number column no longer exists.
            $orderNumber = $request->input('order_number');
            $consumables->whereHas('orders', function ($query) use ($orderNumber) {
                $query->where('orders.order_number', '=', $orderNumber);
            });
        }

        if ($request->filled('category_id')) {
            $consumables->where('consumables.category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('model_number')) {
            $consumables->where('consumables.model_number', '=', $request->input('model_number'));
        }

        if ($request->filled('manufacturer_id')) {
            $consumables->where('consumables.manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $consumables->where('consumables.default_supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $consumables->where('consumables.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $consumables->where('consumables.notes', '=', $request->input('notes'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $total = $consumables->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'category':
                $consumables = $consumables->OrderCategory($order);
                break;
            case 'location':
                $consumables = $consumables->OrderLocation($order);
                break;
            case 'manufacturer':
                $consumables = $consumables->OrderManufacturer($order);
                break;
            case 'company':
                $consumables = $consumables->OrderCompany($order);
                break;
            case 'remaining':
                $consumables = $consumables->OrderRemaining($order);
                break;
            case 'percent_remaining':
                $consumables = $consumables->OrderPercentRemaining($order);
                break;
            case 'supplier':
                $consumables = $consumables->OrderSupplier($order);
                break;
            case 'created_by':
                $consumables = $consumables->OrderByCreatedBy($order);
                break;
            case 'purchase_cost':
                // See AccessoriesController for the rationale — these
                // three sorts walk order_items rather than removed
                // parent columns.
                $consumables = $consumables->OrderByLastPurchaseCost($order);
                break;
            case 'purchase_date':
                $consumables = $consumables->OrderByLastPurchaseDate($order);
                break;
            case 'total_cost':
                $consumables = $consumables->OrderByTotalOrderCost($order);
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
                $consumables = $consumables->orderBy($sort, $order);
                break;
        }

        $consumables = $consumables->skip($offset)->take($limit)->get();

        return (new ConsumablesTransformer)->transformConsumables($consumables, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  ImageUploadRequest  $request
     */
    public function store(StoreConsumableRequest $request): JsonResponse
    {
        $this->authorize('create', Consumable::class);
        $consumable = new Consumable;
        $consumable->fill($request->all());
        $consumable->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        // See AccessoriesController::store for the default-supplier seeding rationale.
        if (! $request->filled('default_supplier_id') && $request->filled('supplier_id')) {
            $consumable->default_supplier_id = $request->input('supplier_id');
        }
        $consumable = $request->handleImages($consumable);

        if ($consumable->save()) {
            $this->enrichInitialOrderFromRequest($request, $consumable);

            return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $id
     */
    public function show($id): array
    {
        $this->authorize('view', Consumable::class);
        $consumable = Consumable::with('users')->findOrFail($id);

        return (new ConsumablesTransformer)->transformConsumable($consumable);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  ImageUploadRequest  $request
     * @param  int  $id
     */
    public function update(StoreConsumableRequest $request, $id): JsonResponse
    {
        $this->authorize('update', Consumable::class);
        $consumable = Consumable::findOrFail($id);

        // See Api\AccessoriesController::update for the qty / order_number
        // / supplier_id contract. Same logic mirrored here.
        $qtyBefore = (int) $consumable->qty;
        $qtyRequested = $request->has('qty') ? (int) $request->input('qty') : $qtyBefore;
        $qtyDelta = $qtyRequested - $qtyBefore;

        // supplier_id, purchase_date, purchase_cost, and order_number
        // are create-only on the parent. Post-create acquisitions live
        // as Orders + OrderItems, so update-mode drops all four.
        $consumable->fill($request->except([
            'qty',
            'order_number',
            'purchase_cost',
            'purchase_date',
            'supplier_id',
        ]));
        $consumable->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $consumable = $request->handleImages($consumable);

        if (! $consumable->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
        }

        if ($qtyDelta !== 0) {
            $orderId = $this->resolveOrderForAdjustment($request, $consumable, $qtyDelta);
            try {
                $consumable->adjustQuantity(
                    $qtyDelta,
                    $request->input('note') ?: "API qty change: {$qtyBefore} → {$qtyRequested}",
                    $orderId,
                );
            } catch (DomainException) {
                return response()->json(
                    Helper::formatStandardApiResponse('error', null, trans('general.adjust_quantity_below_zero')),
                    422,
                );
            }
        }

        return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.update.success')));
    }

    /**
     * See Api\AccessoriesController::adjustQuantity for the shape/contract.
     */
    public function adjustQuantity(AdjustQuantityRequest $request, Consumable $consumable): JsonResponse
    {
        return $this->adjustQuantityAsJson($request, $consumable);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        $this->authorize('delete', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        $this->authorize('delete', $consumable);

        // Mirror the web + bulk paths. Outstanding distributions
        // block destroy the same way they block archive.
        $consumable->loadCount('outstandingAssignments as outstanding_assignments_count');
        if (! $consumable->isDeletable()) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $consumable->name,
                    'item' => trans('general.consumable'),
                ]),
            ), 422);
        }

        $consumable->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.delete.success')));
    }

    /**
     * Restore a soft-deleted consumable. API parity for the web
     * ConsumablesController::postRestore endpoint.
     */
    public function restore($id): JsonResponse
    {
        $this->authorize('delete', Consumable::class);
        $consumable = Consumable::withTrashed()->findOrFail($id);
        $this->authorize('delete', $consumable);

        if ($consumable->deleted_at === null) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.not_deleted', ['item_type' => trans('general.consumable')]),
            ), 422);
        }

        if (! $consumable->restore()) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.could_not_restore', ['item_type' => trans('general.consumable'), 'error' => $consumable->getErrors()->first()]),
            ), 422);
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            null,
            trans('admin/consumables/message.restore.success'),
        ));
    }

    /**
     * Flip archived off. API parity for the web
     * ConsumablesController::postUnarchive endpoint.
     */
    public function unarchive(Consumable $consumable): JsonResponse
    {
        $this->authorize('update', $consumable);

        if (! $consumable->archived) {
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.update.success')));
        }

        $consumable->archived = false;
        if (! $consumable->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()), 422);
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.update.success')));
    }

    /**
     * Returns a JSON response containing details on the users associated with this consumable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see \App\Http\Controllers\Consumables\ConsumablesController::getView() method that returns the form.
     * @since [v1.0]
     *
     * @param  int  $consumableId
     */
    public function getDataView($consumableId): array
    {
        // Only outstanding pivot rows surface on the Assigned tab.
        // Rows marked consumed drop out of the checkouts view because
        // they are no longer "currently distributed." The audit trail
        // for each consume still lives on the item's History tab.
        $consumable = Consumable::with(['consumableAssignments' => function ($query) {
            $query->outstanding()->orderBy($query->getModel()->getTable().'.created_at', 'DESC');
        },
            'consumableAssignments.adminuser' => function ($query) {},
            'consumableAssignments.user' => function ($query) {},
        ])->find($consumableId);

        if (! Company::isCurrentUserHasAccess($consumable)) {
            return ['total' => 0, 'rows' => []];
        }
        $this->authorize('view', Consumable::class);
        $rows = [];

        $canConsume = Gate::allows('checkout', $consumable);

        foreach ($consumable->consumableAssignments as $consumable_assignment) {
            $rows[] = [
                'id' => (int) $consumable_assignment->id,
                'consumable_id' => (int) $consumable_assignment->consumable_id,
                'avatar' => ($consumable_assignment->user) ? e($consumable_assignment->user->present()->gravatar) : '',
                'user' => ($consumable_assignment->user) ? [
                    'id' => (int) $consumable_assignment->user->id,
                    'name' => e($consumable_assignment->user->display_name),
                ] : null,
                'created_at' => Helper::getFormattedDateObject($consumable_assignment->created_at, 'datetime'),
                'note' => ($consumable_assignment->note) ? e($consumable_assignment->note) : null,
                'created_by' => ($consumable_assignment->adminuser) ? [
                    'id' => (int) $consumable_assignment->adminuser->id,
                    'name' => e($consumable_assignment->adminuser->display_name),
                ] : null,
                'available_actions' => [
                    'consume' => $canConsume,
                    'bulk_selectable' => [
                        'consume' => $canConsume,
                    ],
                ],
            ];
        }

        return ['total' => count($rows), 'rows' => $rows];
    }

    /**
     * Checkout a consumable
     *
     * @author [A. Gutierrez] [<andres@baller.tv>]
     *
     * @param  int  $id
     *
     * @since [v4.9.5]
     */
    public function checkout(Request $request, $id): JsonResponse
    {
        // Check if the consumable exists
        if (! $consumable = Consumable::with('users')->find($id)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.does_not_exist')));
        }

        $this->authorize('checkout', $consumable);

        $consumable->checkout_qty = $request->input('checkout_qty', 1);

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.checkout.unavailable')));
        }

        // Make sure there is a valid category
        if (! $consumable->category) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.invalid_item_category_single', ['type' => trans('general.consumable')])));
        }

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0 || $consumable->checkout_qty > $consumable->numRemaining()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.checkout.unavailable', ['requested' => $consumable->checkout_qty, 'remaining' => $consumable->numRemaining()])));
        }

        // Resolve the raw target first, then enforce FMCS explicitly.
        // Scoped lookup can hide cross-company users and make failures ambiguous.
        $user = User::withoutGlobalScopes()->find($request->input('assigned_to'));

        // withoutGlobalScopes bypasses SoftDeletes so we can tell "no such
        // user" from "user in another company" for FMCS messaging. Trashed
        // users must not be treated as valid checkout targets.
        if ($user && ! empty($user->deleted_at)) {
            $user = null;
        }

        if (! $user) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'No user found'));
        }

        if ((Setting::getSettings()->full_multiple_companies_support == '1') && (! $user->companies()->where('companies.id', $consumable->company_id)->exists())) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));
        }

        // Update the consumable data
        $consumable->assigned_to = $request->input('assigned_to');

        // Concurrency guard. The unlocked numRemaining() check above is
        // advisory only — two simultaneous checkout requests can both read
        // "1 remaining", both pass the check, both attach a pivot row, and
        // land the register at -1. Re-fetch the parent row under
        // lockForUpdate INSIDE the transaction, re-check availability
        // against the locked snapshot, and only then write. Any concurrent
        // checkout blocks on the row lock until this transaction commits.
        // Mirrors the pattern already used by License checkout (which locks
        // LicenseSeat rows).
        $errorResponse = null;

        DB::transaction(function () use ($consumable, $request, $user, &$errorResponse): void {
            $locked = Consumable::whereKey($consumable->id)->lockForUpdate()->first();

            if (! $locked || $locked->numRemaining() < $consumable->checkout_qty) {
                $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.checkout.unavailable', [
                    'requested' => $consumable->checkout_qty,
                    'remaining' => $locked ? $locked->numRemaining() : 0,
                ])));

                return;
            }

            for ($i = 0; $i < $consumable->checkout_qty; $i++) {
                $consumable->users()->attach($consumable->id,
                    [
                        'consumable_id' => $consumable->id,
                        // The pivot's created_by is the operator recording the
                        // checkout, not the checkout target. Sibling paths
                        // (web ConsumableCheckoutController, web + API
                        // ComponentCheckoutController) all key off auth()->id().
                        'created_by' => auth()->id(),
                        'assigned_to' => $request->input('assigned_to'),
                        'note' => $request->input('note'),
                    ]
                );
            }

            event(new CheckoutableCheckedOut(
                $consumable,
                $user,
                auth()->user(),
                $request->input('note'),
                [],
                $consumable->checkout_qty,
            ));
        });

        if ($errorResponse) {
            return $errorResponse;
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.checkout.success')));

    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @see SelectlistTransformer
     */
    public function selectlist(Request $request): array
    {
        $this->authorize('view.selectlists');

        $consumables = Consumable::select([
            'consumables.id',
            'consumables.name',
        ]);

        if ($request->filled('search')) {
            $consumables = $consumables->where('consumables.name', 'LIKE', '%'.$request->input('search').'%');
        }

        $consumables = $consumables->orderBy('name', 'ASC')->paginate(50);

        return (new SelectlistTransformer)->transformSelectlist($consumables);
    }

    public function history(Request $request, Consumable $consumable): JsonResponse|array
    {
        $this->authorize('history', $consumable);
        $historyQuery = $consumable->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * List consumables that are requestable AND reachable by the
     * current caller (per FMCS + location scoping). Hydrates the
     * consumables tab on /account/requestable. See the sibling
     * AccessoriesController::requestable for design rationale.
     */
    public function requestable(Request $request): array
    {
        $this->authorize('viewRequestable', Consumable::class);

        $query = Consumable::with('category', 'location', 'company', 'manufacturer', 'requests')
            ->withCount('consumableAssignments as consumables_users_count')
            ->withCount('outstandingAssignments as outstanding_assignments_count')
            ->Requestable();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('consumables.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('consumables.model_number', 'LIKE', '%'.$search.'%');
            });
        }

        $total = $query->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), ['name', 'created_at'], true) ? $request->input('sort') : 'name';

        $rows = $query->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        return (new ConsumablesTransformer)->transformRequestedConsumables($rows, $total);
    }

    /**
     * Mark one pivot row consumed. API parity for the web
     * ConsumableCheckoutController::consume endpoint. One-way: an
     * already-consumed row no-ops.
     */
    public function consume(Consumable $consumable, ConsumableAssignment $assignment): JsonResponse
    {
        $this->authorize('checkout', $consumable);

        if ($assignment->consumable_id !== $consumable->id) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.does_not_exist')), 404);
        }

        if ($assignment->isConsumed()) {
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.consumed.already')));
        }

        $assignment->consumed_at = now();
        if (! $assignment->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.consumed.error')), 422);
        }

        $this->writeConsumeLog($consumable, $assignment);

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.consumed.success')));
    }

    /**
     * Bulk mark pivot rows consumed. Mixed-parent submissions are
     * rejected because the show-page flow guarantees a single parent
     * and the API honors that invariant too.
     */
    public function bulkConsume(Request $request): JsonResponse
    {
        $pivotIds = (array) $request->input('ids', []);
        if ($pivotIds === []) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.consumed.no_rows_selected')), 422);
        }

        $assignments = ConsumableAssignment::with('consumable')
            ->whereIn('id', $pivotIds)
            ->whereNull('consumed_at')
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.consumed.no_rows_selected')), 422);
        }

        $consumableIds = $assignments->pluck('consumable_id')->unique();
        if ($consumableIds->count() !== 1) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.consumed.no_rows_selected')), 422);
        }

        $consumable = $assignments->first()->consumable;
        $this->authorize('checkout', $consumable);

        $now = now();
        $consumed = 0;
        foreach ($assignments as $assignment) {
            $assignment->consumed_at = $now;
            if ($assignment->save()) {
                $consumed++;
                $this->writeConsumeLog($consumable, $assignment);
            }
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            ['consumed' => $consumed],
            trans_choice('admin/consumables/message.consumed.bulk_success', $consumed, ['count' => $consumed]),
        ));
    }

    /**
     * Bulk edit. Mirror of BulkConsumablesController::update.
     * Returns JSON instead of a flash redirect, otherwise identical
     * semantics: writes only non-blank fields, routes company_id
     * through FMCS, supports null_{field}=1 opt-in null-outs, per-row
     * Gate check, surfaces partial-success detail in the payload.
     */
    public function bulkEdit(Request $request): JsonResponse
    {
        $this->authorize('update', Consumable::class);

        if (! $request->filled('ids')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.bulk.no_consumables_selected')), 422);
        }

        // Only a superuser may null the company_id in bulk. For non-
        // superusers, Company::getIdForCurrentUser() in the single-item
        // update path coerces a null submission back to the actor's own
        // company and never allows the field to be cleared. The bulk
        // null-out loop below unconditionally wipes company_id AFTER the
        // FMCS validation runs, so this gate has to come up front.
        if ($request->boolean('null_company_id') && ! auth()->user()->isSuperUser()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.insufficient_permissions')), 422);
        }

        $consumables = Consumable::whereIn('id', $request->input('ids'))->get();
        if ($consumables->isEmpty()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.bulk.no_consumables_selected')), 422);
        }

        $updateData = [];
        foreach (BulkConsumablesController::EDITABLE_FIELDS as $field) {
            if (! $request->has($field)) {
                continue;
            }
            $value = $request->input($field);
            if ($value === '' || $value === null) {
                continue;
            }
            $updateData[$field] = $value;
        }

        if (array_key_exists('company_id', $updateData)) {
            try {
                $updateData['company_id'] = Company::getIdForCurrentUser($updateData['company_id']);
            } catch (ValidationException $e) {
                return response()->json(Helper::formatStandardApiResponse('error', null, $e->errors()), 422);
            }
        }

        foreach (['category_id', 'company_id', 'manufacturer_id', 'location_id', 'default_supplier_id', 'min_amt'] as $field) {
            if ($request->boolean('null_'.$field)) {
                if ($field === 'category_id') {
                    continue;
                }
                $updateData[$field] = null;
            }
        }

        if (empty($updateData)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.bulk.no_fields_updated')), 422);
        }

        $authorized = $consumables->filter(fn (Consumable $c) => Gate::allows('update', $c));
        $skipped = $consumables->count() - $authorized->count();

        $updated = 0;
        $failed_rows = [];
        foreach ($authorized as $consumable) {
            $consumable->fill($updateData);
            if ($consumable->save()) {
                $updated++;

                continue;
            }
            $failed_rows[] = $consumable->name.': '.$consumable->getErrors()->first();
        }

        $failed = count($failed_rows);
        $payload = ['updated' => $updated, 'skipped_unauthorized' => $skipped, 'failed_rows' => $failed_rows];

        if ($updated === 0 && $failed > 0) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                $payload,
                trans_choice('admin/consumables/message.bulk.update.none_updated', $failed, ['count' => $failed]),
            ), 422);
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            $payload,
            trans_choice('admin/consumables/message.bulk.update.success', $updated, ['count' => $updated]),
        ));
    }

    /**
     * Bulk soft-delete. Per-row isDeletable + Gate check mirror the
     * web controller.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $this->authorize('delete', Consumable::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            $consumable = Consumable::find($id);
            if (is_null($consumable)) {
                $errors[] = trans('admin/consumables/message.does_not_exist', ['id' => $id]);

                continue;
            }

            if (! Gate::allows('delete', $consumable)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            $consumable->loadCount('outstandingAssignments as outstanding_assignments_count');
            if (! $consumable->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $consumable->name,
                    'item' => trans('general.consumable'),
                ]);

                continue;
            }

            $consumable->delete();
            $success_count++;
        }

        $payload = ['deleted' => $success_count, 'errors' => $errors];
        $message = $success_count > 0
            ? trans_choice('admin/consumables/message.delete.bulk_success', $success_count, ['count' => $success_count])
            : trans('admin/consumables/message.bulk.no_consumables_selected');

        return response()->json(
            Helper::formatStandardApiResponse($success_count > 0 ? 'success' : 'error', $payload, $message),
            $success_count > 0 ? 200 : 422,
        );
    }

    /**
     * Bulk restore. Already-live rows are skipped without erroring so
     * a stale selection doesn't blow up the whole batch.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $this->authorize('delete', Consumable::class);

        $ids = (array) $request->input('ids', []);
        if ($ids === []) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.bulk.no_consumables_selected')), 422);
        }

        $consumables = Consumable::withTrashed()->whereIn('id', $ids)->get();

        $restored = 0;
        $errors = [];

        foreach ($consumables as $consumable) {
            if (! Gate::allows('delete', $consumable)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if ($consumable->deleted_at === null) {
                continue;
            }

            if ($consumable->restore()) {
                $restored++;
            }
        }

        return response()->json(Helper::formatStandardApiResponse(
            $restored > 0 ? 'success' : 'error',
            ['restored' => $restored, 'errors' => $errors],
            trans_choice('admin/consumables/message.bulk.restore.success', $restored, ['count' => $restored]),
        ), $restored > 0 ? 200 : 422);
    }

    private function writeConsumeLog(Consumable $consumable, ConsumableAssignment $assignment): void
    {
        $log = new Actionlog;
        $log->item_type = Consumable::class;
        $log->item_id = $consumable->id;
        $log->target_type = User::class;
        $log->target_id = $assignment->assigned_to;
        $log->created_at = date('Y-m-d H:i:s');
        $log->created_by = auth()->id();
        $log->logaction('consumed');
    }
}
