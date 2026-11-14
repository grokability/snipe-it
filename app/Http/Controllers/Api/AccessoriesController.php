<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Accessories\BulkAccessoriesController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccessoryCheckoutRequest;
use App\Http\Requests\AdjustQuantityRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\StoreAccessoryRequest;
use App\Http\Traits\CheckInOutTrait;
use App\Http\Traits\HandlesAdjustQuantity;
use App\Http\Transformers\AccessoriesTransformer;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AccessoriesController extends Controller
{
    use CheckInOutTrait;
    use HandlesAdjustQuantity;

    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if ($request->user()->cannot('reports.view')) {
            $this->authorize('view', Accessory::class);
        }

        // This array is what determines which fields should be allowed to be sorted on ON the table itself, no relations
        // Relations will be handled in query scopes a little further down.
        $allowed_columns =
            [
                'id',
                'name',
                'model_number',
                'eol',
                'notes',
                'purchase_cost',
                'purchase_date',
                'created_at',
                'updated_at',
                'min_amt',
                'company_id',
                'notes',
                'checkouts_count',
                'image',
                'qty',
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

        // See ComponentsController for the orderItems.order.supplier eager-load rationale.
        $accessories = Accessory::select('accessories.*')
            ->with('category', 'company', 'manufacturer', 'checkouts', 'location', 'defaultSupplier', 'adminuser', 'orderItems.order.supplier')
            ->withCount('checkouts as checkouts_count');

        // Archived / soft-deleted scope.
        switch ($request->input('status')) {
            case 'Archived':
                $accessories->archived();
                break;
            case 'Deleted':
                $accessories->onlyTrashed();
                break;
            default:
                $accessories->accessoriesForShow();
                break;
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $accessories->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('company_id')) {
            // expand_company_hierarchy=1 opts the company show-page tabs into the
            // parent/child rollup so a child shows items inherited from its parent.
            if ($request->boolean('expand_company_hierarchy')) {
                $accessories->whereIn('accessories.company_id', Company::reachableCompanyIds($request->input('company_id')));
            } else {
                $accessories->where('accessories.company_id', '=', $request->input('company_id'));
            }
        }

        if ($request->filled('order_number')) {
            // Reroute through the HasOrders orders() HasManyThrough since
            // the parent accessories.order_number column no longer exists.
            $orderNumber = $request->input('order_number');
            $accessories->whereHas('orders', function ($query) use ($orderNumber) {
                $query->where('orders.order_number', '=', $orderNumber);
            });
        }

        if ($request->filled('category_id')) {
            $accessories->where('accessories.category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $accessories->where('accessories.manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->input('requestable') == 'true') {
            $accessories->where('accessories.requestable', '=', '1');
        }

        if ($request->filled('supplier_id')) {
            $accessories->where('accessories.default_supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $accessories->where('accessories.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $accessories->where('accessories.notes', '=', $request->input('notes'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $total = $accessories->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override = $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'category':
                $accessories = $accessories->OrderCategory($order);
                break;
            case 'company':
                $accessories = $accessories->OrderCompany($order);
                break;
            case 'location':
                $accessories = $accessories->OrderLocation($order);
                break;
            case 'manufacturer':
                $accessories = $accessories->OrderManufacturer($order);
                break;
            case 'supplier':
                $accessories = $accessories->OrderSupplier($order);
                break;
            case 'created_by':
                $accessories = $accessories->OrderByCreatedByName($order);
                break;
            case 'purchase_cost':
                // purchase_cost lives on order_items now; sort by the
                // last acquisition's price rather than the removed
                // parent column.
                $accessories = $accessories->OrderByLastPurchaseCost($order);
                break;
            case 'purchase_date':
                $accessories = $accessories->OrderByLastPurchaseDate($order);
                break;
            case 'total_cost':
                // total_cost is the sum of qty*price across every
                // OrderItem, mirroring the info-panel's total_cost
                // display so the column header sort matches.
                $accessories = $accessories->OrderByTotalOrderCost($order);
                break;
            case 'percent_remaining':
                $accessories = $accessories->OrderPercentRemaining($order);
                break;
            case 'remaining':
                $accessories = $accessories->OrderRemaining($order);
                break;
            default:
                $accessories = $accessories->orderBy($column_sort, $order);
                break;
        }

        $accessories = $accessories->skip($offset)->take($limit)->get();

        return (new AccessoriesTransformer)->transformAccessories($accessories, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ImageUploadRequest  $request
     * @return JsonResponse
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function store(StoreAccessoryRequest $request)
    {
        $accessory = new Accessory;
        $accessory->fill($request->all());
        $accessory->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        // Seed the parent's "typical supplier" template from the initial
        // acquisition supplier on create; editable afterwards. See the
        // UI store method for the parent-as-template rationale.
        if (! $request->filled('default_supplier_id') && $request->filled('supplier_id')) {
            $accessory->default_supplier_id = $request->input('supplier_id');
        }
        $accessory = $request->handleImages($accessory);

        if ($accessory->save()) {
            $this->enrichInitialOrderFromRequest($request, $accessory);

            return response()->json(Helper::formatStandardApiResponse('success', $accessory, trans('admin/accessories/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $accessory->getErrors()));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return array
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function show($id)
    {
        $this->authorize('view', Accessory::class);
        $accessory = Accessory::withCount('checkouts as checkouts_count')->findOrFail($id);

        return (new AccessoriesTransformer)->transformAccessory($accessory);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return array
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function accessory_detail($id)
    {
        $this->authorize('view', Accessory::class);
        $accessory = Accessory::findOrFail($id);

        return (new AccessoriesTransformer)->transformAccessory($accessory);
    }

    /**
     * Get the list of checkouts for a specific accessory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     * @return array<string, mixed>
     */
    public function checkedout(Request $request, $id)
    {
        $this->authorize('view', Accessory::class);

        $accessory = Accessory::with('lastCheckout')->findOrFail($id);
        $offset = request('offset', 0);
        $limit = request('limit', 50);

        // Total count of all checkouts for this asset
        $accessory_checkouts = $accessory->checkouts();

        // Check for search text in the request
        if ($request->filled('search')) {
            $accessory_checkouts = $accessory_checkouts->TextSearch($request->input('search'));
        }

        $total = $accessory_checkouts->count();
        $accessory_checkouts = $accessory_checkouts->skip($offset)->take($limit)->get();

        $accessory_checkouts->loadMorph('assignedTo', [
            User::class => ['companies'],
        ]);

        return (new AccessoriesTransformer)->transformCheckedoutAccessory($accessory_checkouts, $total);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(ImageUploadRequest $request, $id)
    {
        $this->authorize('update', Accessory::class);
        $accessory = Accessory::findOrFail($id);

        // Payload shape is preserved for API back-compat: `qty`,
        // `order_number`, and `supplier_id` all remain accepted keys.
        // supplier_id flows through fill() like any other field. `qty` gets
        // pulled off the fill and routed through adjustQuantity() below so
        // any change writes a QuantityAdjust action_log entry rather than
        // silently overwriting. `order_number` no longer lives on the
        // parent — resolveOrderForAdjustment turns it into an Order +
        // OrderItem pair and passes the Order's id to the trait.
        $qtyBefore = (int) $accessory->qty;
        $qtyRequested = $request->has('qty') ? (int) $request->input('qty') : $qtyBefore;
        $qtyDelta = $qtyRequested - $qtyBefore;

        // supplier_id, purchase_date, purchase_cost, and order_number
        // are create-only on the parent. Post-create acquisitions live
        // as Orders + OrderItems (each with its own supplier / date /
        // price / order number), so update-mode drops all four. API
        // consumers relying on the old "set these via PATCH" behavior
        // need to use the adjust-quantity endpoint instead.
        $accessory->fill($request->except([
            'qty',
            'order_number',
            'purchase_cost',
            'purchase_date',
            'supplier_id',
        ]));
        $accessory->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $accessory = $request->handleImages($accessory);

        if (! $accessory->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $accessory->getErrors()));
        }

        if ($qtyDelta !== 0) {
            $orderId = $this->resolveOrderForAdjustment($request, $accessory, $qtyDelta);
            try {
                $accessory->adjustQuantity(
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

        return response()->json(Helper::formatStandardApiResponse('success', $accessory, trans('admin/accessories/message.update.success')));
    }

    /**
     * Dedicated adjust-quantity endpoint. Signed delta semantics: positive
     * amount replenishes, negative decrements. Every call becomes a
     * QuantityAdjust action_log entry. Note is required (unlike the
     * general update path which synthesizes one). An optional file
     * attachment lands on the same log row via UploadFileRequest::handleFile.
     * See Api\AccessoriesController::update for the "qty inside PATCH"
     * alternative preserved for API back-compat.
     */
    public function adjustQuantity(AdjustQuantityRequest $request, Accessory $accessory): JsonResponse
    {
        return $this->adjustQuantityAsJson($request, $accessory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('delete', Accessory::class);
        $accessory = Accessory::withCount('checkouts as checkouts_count')->findOrFail($id);
        $this->authorize('delete', $accessory);

        // Mirror the web + bulk paths. Active checkouts block destroy
        // the same way they block archive.
        if (! $accessory->isDeletable()) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $accessory->name,
                    'item' => trans('general.accessory'),
                ]),
            ), 422);
        }

        $accessory->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/accessories/message.delete.success')));
    }

    /**
     * Restore a soft-deleted accessory. API parity for the web
     * AccessoriesController::postRestore endpoint.
     */
    public function restore($id): JsonResponse
    {
        $this->authorize('delete', Accessory::class);
        $accessory = Accessory::withTrashed()->findOrFail($id);
        $this->authorize('delete', $accessory);

        if ($accessory->deleted_at === null) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.not_deleted', ['item_type' => trans('general.accessory')]),
            ), 422);
        }

        if (! $accessory->restore()) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.could_not_restore', ['item_type' => trans('general.accessory'), 'error' => $accessory->getErrors()->first()]),
            ), 422);
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            null,
            trans('admin/accessories/message.restore.success'),
        ));
    }

    /**
     * Flip archived off. API parity for the web
     * AccessoriesController::postUnarchive endpoint.
     */
    public function unarchive(Accessory $accessory): JsonResponse
    {
        $this->authorize('update', $accessory);

        if (! $accessory->archived) {
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/accessories/message.update.success')));
        }

        $accessory->archived = false;
        if (! $accessory->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $accessory->getErrors()), 422);
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/accessories/message.update.success')));
    }

    /**
     * Bulk edit. Mirror of BulkAccessoriesController::update.
     * Returns JSON instead of a flash redirect, otherwise identical
     * semantics: writes only non-blank fields, routes company_id
     * through FMCS, supports null_{field}=1 opt-in null-outs, per-row
     * Gate check, surfaces partial-success detail in the payload.
     */
    public function bulkEdit(Request $request): JsonResponse
    {
        $this->authorize('update', Accessory::class);

        if (! $request->filled('ids')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.bulk.no_accessories_selected')), 422);
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

        $accessories = Accessory::whereIn('id', $request->input('ids'))->get();
        if ($accessories->isEmpty()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.bulk.no_accessories_selected')), 422);
        }

        $updateData = [];
        foreach (BulkAccessoriesController::EDITABLE_FIELDS as $field) {
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
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.bulk.no_fields_updated')), 422);
        }

        $authorized = $accessories->filter(fn (Accessory $a) => Gate::allows('update', $a));
        $skipped = $accessories->count() - $authorized->count();

        $updated = 0;
        $failed_rows = [];
        foreach ($authorized as $accessory) {
            $accessory->fill($updateData);
            if ($accessory->save()) {
                $updated++;

                continue;
            }
            $failed_rows[] = $accessory->name.': '.$accessory->getErrors()->first();
        }

        $failed = count($failed_rows);
        $payload = ['updated' => $updated, 'skipped_unauthorized' => $skipped, 'failed_rows' => $failed_rows];

        if ($updated === 0 && $failed > 0) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                $payload,
                trans_choice('admin/accessories/message.bulk.update.none_updated', $failed, ['count' => $failed]),
            ), 422);
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            $payload,
            trans_choice('admin/accessories/message.bulk.update.success', $updated, ['count' => $updated]),
        ));
    }

    /**
     * Bulk soft-delete. Per-row isDeletable + Gate check mirror the
     * web controller.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $this->authorize('delete', Accessory::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            $accessory = Accessory::find($id);
            if (is_null($accessory)) {
                $errors[] = trans('admin/accessories/message.does_not_exist', ['id' => $id]);

                continue;
            }

            if (! Gate::allows('delete', $accessory)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            $accessory->loadCount('checkouts as checkouts_count');
            if (! $accessory->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $accessory->name,
                    'item' => trans('general.accessory'),
                ]);

                continue;
            }

            $accessory->delete();
            $success_count++;
        }

        $payload = ['deleted' => $success_count, 'errors' => $errors];
        $message = $success_count > 0
            ? trans_choice('admin/accessories/message.delete.bulk_success', $success_count, ['count' => $success_count])
            : trans('admin/accessories/message.bulk.no_accessories_selected');

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
        $this->authorize('delete', Accessory::class);

        $ids = (array) $request->input('ids', []);
        if ($ids === []) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.bulk.no_accessories_selected')), 422);
        }

        $accessories = Accessory::withTrashed()->whereIn('id', $ids)->get();

        $restored = 0;
        $errors = [];

        foreach ($accessories as $accessory) {
            if (! Gate::allows('delete', $accessory)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if ($accessory->deleted_at === null) {
                continue;
            }

            if ($accessory->restore()) {
                $restored++;
            }
        }

        return response()->json(Helper::formatStandardApiResponse(
            $restored > 0 ? 'success' : 'error',
            ['restored' => $restored, 'errors' => $errors],
            trans_choice('admin/accessories/message.bulk.restore.success', $restored, ['count' => $restored]),
        ), $restored > 0 ? 200 : 422);
    }

    /**
     * Save the Accessory checkout information.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @return JsonResponse
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function checkout(AccessoryCheckoutRequest $request, Accessory $accessory)
    {
        $this->authorize('checkout', $accessory);
        $target = $this->determineCheckoutTarget();

        if (! $accessory->canCheckoutTo($target)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));
        }

        $accessory->checkout_qty = $request->input('checkout_qty', 1);
        $payload = null;
        $overAllocated = false;

        // Concurrency guard. AccessoryCheckoutRequest validated
        // number_remaining_after_checkout >= 0 with an unlocked read of
        // numRemaining(), so two simultaneous checkout requests could both
        // pass validation, both attach rows, and land the register at -1.
        // Re-fetch the parent row under lockForUpdate INSIDE the
        // transaction, re-check availability against the locked snapshot,
        // and only then write. Mirrors the License checkout locking
        // pattern.
        DB::transaction(function () use ($accessory, $request, $target, &$payload, &$overAllocated): void {
            $locked = Accessory::whereKey($accessory->id)->lockForUpdate()->first();

            if (! $locked || $locked->numRemaining() < $accessory->checkout_qty) {
                $overAllocated = true;

                return;
            }

            for ($i = 0; $i < $accessory->checkout_qty; $i++) {

                $accessory_checkout = new AccessoryCheckout([
                    'accessory_id' => $accessory->id,
                    'created_at' => Carbon::now(),
                    'assigned_to' => $target->id,
                    'assigned_type' => $target::class,
                    'note' => $request->input('note'),
                ]);

                $accessory_checkout->created_by = auth()->id();
                $accessory_checkout->save();

                $payload = [
                    'accessory_id' => $accessory->id,
                    'assigned_to' => $target->id,
                    'assigned_type' => $target::class,
                    'note' => $request->input('note'),
                    'created_by' => auth()->id(),
                    'pivot' => $accessory_checkout->id,
                ];
            }

            // Set this value to be able to pass the qty through to the event.
            event(new CheckoutableCheckedOut(
                $accessory,
                $target,
                auth()->user(),
                $request->input('note'),
                [],
                $accessory->checkout_qty,
            ));
        });

        if ($overAllocated) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.checkout.unavailable')));
        }

        return response()->json(Helper::formatStandardApiResponse('success', $payload, trans('admin/accessories/message.checkout.success')));

    }

    /**
     * Check in the item so that it can be checked out again to someone else
     *
     * @param  int  $accessoryUserId
     * @return JsonResponse
     *
     * @uses Accessory::checkin_email() to determine if an email can and should be sent
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @internal param int $accessoryId
     */
    public function checkin(Request $request, $accessoryUserId = null)
    {
        if (is_null($accessory_checkout = AccessoryCheckout::find($accessoryUserId))) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.does_not_exist', ['id' => $accessoryUserId])));
        }

        $accessory = Accessory::find($accessory_checkout->accessory_id);
        $this->authorize('checkin', $accessory);

        if ($accessory_checkout->delete()) {
            event(new CheckoutableCheckedIn($accessory, $accessory_checkout->assignedTo, auth()->user(), $request->input('note')));

            $payload = [
                'accessory_id' => $accessory->id,
                'note' => $request->input('note'),
                'created_by' => auth()->id(),
                'pivot' => $accessory_checkout->id,
            ];

            return response()->json(Helper::formatStandardApiResponse('success', $payload, trans('admin/accessories/message.checkin.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.checkin.error')));

    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @see SelectlistTransformer
     */
    public function selectlist(Request $request)
    {
        $this->authorize('view.selectlists');

        $accessories = Accessory::select([
            'accessories.id',
            'accessories.name',
        ]);

        if ($request->filled('search')) {
            $accessories = $accessories->where('accessories.name', 'LIKE', '%'.$request->input('search').'%');
        }

        $accessories = $accessories->orderBy('name', 'ASC')->paginate(50);

        return (new SelectlistTransformer)->transformSelectlist($accessories);
    }

    public function history(Request $request, Accessory $accessory): JsonResponse|array
    {
        $this->authorize('history', $accessory);
        $historyQuery = $accessory->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * List accessories that are requestable AND reachable by the
     * current caller (per FMCS + location scoping). Hydrates the
     * accessories tab on /account/requestable. Deliberately
     * open to any authenticated user - the web page it feeds is open
     * too, and the Requestable() scope layered on top of
     * the CompanyableTrait global scope ensures the caller only sees
     * rows they can act on.
     */
    public function requestable(Request $request): array
    {
        $this->authorize('viewRequestable', Accessory::class);

        $query = Accessory::with('category', 'location', 'company', 'manufacturer', 'requests')
            ->withCount('checkouts as checkouts_count')
            ->Requestable();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('accessories.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('accessories.model_number', 'LIKE', '%'.$search.'%');
            });
        }

        $total = $query->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), ['name', 'created_at'], true) ? $request->input('sort') : 'name';

        $rows = $query->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        return (new AccessoriesTransformer)->transformRequestedAccessories($rows, $total);
    }
}
