<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedIn;
use App\Exceptions\MissingLogTarget;
use App\Helpers\Helper;
use App\Http\Controllers\Components\BulkComponentsController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustQuantityRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Traits\HandlesAdjustQuantity;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\ComponentsTransformer;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Setting;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComponentsController extends Controller
{
    use HandlesAdjustQuantity;

    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function index(Request $request): JsonResponse|array
    {
        $this->authorize('view', Component::class);

        // This array is what determines which fields should be allowed to be sorted on ON the table itself, no relations
        // Relations will be handled in query scopes a little further down.
        $allowed_columns =
            [
                'id',
                'name',
                'min_amt',
                'order_number',
                'model_number',
                'serial',
                'purchase_date',
                'purchase_cost',
                'qty',
                'image',
                'notes',
                // These are *relationships* so we wouldn't normally include them in this array,
                // since they would normally create a `column not found` error,
                // BUT we account for them in the ordering switch down at the end of this method
                // DO NOT ADD ANYTHING TO THIS LIST WITHOUT CHECKING THE ORDERING SWITCH BELOW!
                'company',
                'location',
                'category',
                'manufacturer',
                'supplier',

            ];

        // Eager-load orderItems.order.supplier so lastAcquisitionSupplier()
        // walks the relation cache in the transformer instead of firing a
        // Supplier::find() per row.
        $components = Component::select('components.*')
            ->with('company', 'location', 'category', 'defaultSupplier', 'adminuser', 'manufacturer', 'orderItems.order.supplier')
            ->withSum('unconstrainedAssets as sum_unconstrained_assets', 'components_assets.assigned_qty');

        // Archived / soft-deleted scope.
        switch ($request->input('status')) {
            case 'Archived':
                $components->archived();
                break;
            case 'Deleted':
                $components->onlyTrashed();
                break;
            default:
                $components->componentsForShow();
                break;
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $components->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('name')) {
            $components->where('components.name', '=', $request->input('name'));
        }

        if ($request->filled('company_id')) {
            // expand_company_hierarchy=1 opts the company show-page tabs into the
            // parent/child rollup so a child shows items inherited from its parent.
            if ($request->boolean('expand_company_hierarchy')) {
                $components->whereIn('components.company_id', Company::reachableCompanyIds($request->input('company_id')));
            } else {
                $components->where('components.company_id', '=', $request->input('company_id'));
            }
        }

        if ($request->filled('order_number')) {
            // Reroute through the HasOrders orders() HasManyThrough since
            // the parent components.order_number column no longer exists.
            $orderNumber = $request->input('order_number');
            $components->whereHas('orders', function ($query) use ($orderNumber) {
                $query->where('orders.order_number', '=', $orderNumber);
            });
        }

        if ($request->filled('category_id')) {
            $components->where('components.category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('supplier_id')) {
            $components->where('components.default_supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $components->where('components.manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('model_number')) {
            $components->where('components.model_number', '=', $request->input('model_number'));
        }

        if ($request->filled('location_id')) {
            $components->where('components.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $components->where('components.notes', '=', $request->input('notes'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $components_count = $components->count();
        $offset = ($request->input('offset') > $components_count) ? $components_count : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override = $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'category':
                $components = $components->OrderCategory($order);
                break;
            case 'location':
                $components = $components->OrderLocation($order);
                break;
            case 'company':
                $components = $components->OrderCompany($order);
                break;
            case 'supplier':
                $components = $components->OrderSupplier($order);
                break;
            case 'manufacturer':
                $components = $components->OrderManufacturer($order);
                break;
            case 'created_by':
                $components = $components->OrderByCreatedBy($order);
                break;
            case 'percent_remaining':
                $components = $components->OrderPercentRemaining($order);
                break;
            case 'remaining':
                $components = $components->OrderRemaining($order);
                break;
            case 'order_number':
                // components.order_number is `legacy_order_number` now
                // so a raw orderBy on it is a SQL error.
                $components = $components->OrderByOrderNumber($order);
                break;
            case 'purchase_cost':
                // See AccessoriesController for the rationale — these
                // three sorts walk order_items rather than removed
                // parent columns.
                $components = $components->OrderByLastPurchaseCost($order);
                break;
            case 'purchase_date':
                $components = $components->OrderByLastPurchaseDate($order);
                break;
            case 'total_cost':
                $components = $components->OrderByTotalOrderCost($order);
                break;
            default:
                $components = $components->orderBy($column_sort, $order);
                break;
        }

        $total = $components_count;
        $components = $components->skip($offset)->take($limit)->get();

        return (new ComponentsTransformer)->transformComponents($components, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function store(ImageUploadRequest $request): JsonResponse
    {
        $this->authorize('create', Component::class);
        $component = new Component;
        $component->fill($request->all());
        $component->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        // See AccessoriesController::store for the default-supplier seeding rationale.
        if (! $request->filled('default_supplier_id') && $request->filled('supplier_id')) {
            $component->default_supplier_id = $request->input('supplier_id');
        }
        $component = $request->handleImages($component);

        if ($component->save()) {
            $this->enrichInitialOrderFromRequest($request, $component);

            return response()->json(Helper::formatStandardApiResponse('success', $component, trans('admin/components/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $component->getErrors()));
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
        $this->authorize('view', Component::class);
        $component = Component::findOrFail($id);

        if ($component) {
            return (new ComponentsTransformer)->transformComponent($component);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function update(ImageUploadRequest $request, $id): JsonResponse
    {
        $this->authorize('update', Component::class);
        $component = Component::findOrFail($id);

        // See Api\AccessoriesController::update for the qty / order_number
        // / supplier_id contract. Same logic mirrored here.
        $qtyBefore = (int) $component->qty;
        $qtyRequested = $request->has('qty') ? (int) $request->input('qty') : $qtyBefore;
        $qtyDelta = $qtyRequested - $qtyBefore;

        // supplier_id, purchase_date, purchase_cost, and order_number
        // are create-only on the parent. Post-create acquisitions live
        // as Orders + OrderItems, so update-mode drops all four.
        $component->fill($request->except([
            'qty',
            'order_number',
            'purchase_cost',
            'purchase_date',
            'supplier_id',
        ]));
        $component->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $component = $request->handleImages($component);

        if (! $component->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $component->getErrors()));
        }

        if ($qtyDelta !== 0) {
            $orderId = $this->resolveOrderForAdjustment($request, $component, $qtyDelta);
            try {
                $component->adjustQuantity(
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

        return response()->json(Helper::formatStandardApiResponse('success', $component, trans('admin/components/message.update.success')));
    }

    /**
     * See Api\AccessoriesController::adjustQuantity for the shape/contract.
     */
    public function adjustQuantity(AdjustQuantityRequest $request, Component $component): JsonResponse
    {
        return $this->adjustQuantityAsJson($request, $component);
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
        $this->authorize('delete', Component::class);
        $component = Component::findOrFail($id);
        $this->authorize('delete', $component);

        // Mirror the web + bulk paths. Active checkouts block destroy
        // the same way they block archive. numCheckedOut() reads
        // sum_unconstrained_assets. Load it first to avoid a per-row query.
        $component->loadSum('unconstrainedAssets as sum_unconstrained_assets', 'components_assets.assigned_qty');
        if (! $component->isDeletable()) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $component->name,
                    'item' => trans('general.component'),
                ]),
            ), 422);
        }

        $component->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/components/message.delete.success')));
    }

    /**
     * Restore a soft-deleted component. API parity for the web
     * ComponentsController::postRestore endpoint.
     */
    public function restore($id): JsonResponse
    {
        $this->authorize('delete', Component::class);
        $component = Component::withTrashed()->findOrFail($id);
        $this->authorize('delete', $component);

        if ($component->deleted_at === null) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.not_deleted', ['item_type' => trans('general.component')]),
            ), 422);
        }

        if (! $component->restore()) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                null,
                trans('general.could_not_restore', ['item_type' => trans('general.component'), 'error' => $component->getErrors()->first()]),
            ), 422);
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            null,
            trans('admin/components/message.restore.success'),
        ));
    }

    /**
     * Flip archived off. API parity for the web
     * ComponentsController::postUnarchive endpoint.
     */
    public function unarchive(Component $component): JsonResponse
    {
        $this->authorize('update', $component);

        if (! $component->archived) {
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/components/message.update.success')));
        }

        $component->archived = false;
        if (! $component->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $component->getErrors()), 422);
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/components/message.update.success')));
    }

    /**
     * Bulk edit. Mirror of BulkComponentsController::update.
     * Returns JSON instead of a flash redirect, otherwise identical
     * semantics: writes only non-blank fields, routes company_id
     * through FMCS, supports null_{field}=1 opt-in null-outs, per-row
     * Gate check, surfaces partial-success detail in the payload.
     */
    public function bulkEdit(Request $request): JsonResponse
    {
        $this->authorize('update', Component::class);

        if (! $request->filled('ids')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.bulk.no_components_selected')), 422);
        }

        $components = Component::whereIn('id', $request->input('ids'))->get();
        if ($components->isEmpty()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.bulk.no_components_selected')), 422);
        }

        $updateData = [];
        foreach (BulkComponentsController::EDITABLE_FIELDS as $field) {
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
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.bulk.no_fields_updated')), 422);
        }

        $authorized = $components->filter(fn (Component $c) => Gate::allows('update', $c));
        $skipped = $components->count() - $authorized->count();

        $updated = 0;
        $failed_rows = [];
        foreach ($authorized as $component) {
            $component->fill($updateData);
            if ($component->save()) {
                $updated++;

                continue;
            }
            $failed_rows[] = $component->name.': '.$component->getErrors()->first();
        }

        $failed = count($failed_rows);
        $payload = ['updated' => $updated, 'skipped_unauthorized' => $skipped, 'failed_rows' => $failed_rows];

        if ($updated === 0 && $failed > 0) {
            return response()->json(Helper::formatStandardApiResponse(
                'error',
                $payload,
                trans_choice('admin/components/message.bulk.update.none_updated', $failed, ['count' => $failed]),
            ), 422);
        }

        return response()->json(Helper::formatStandardApiResponse(
            'success',
            $payload,
            trans_choice('admin/components/message.bulk.update.success', $updated, ['count' => $updated]),
        ));
    }

    /**
     * Bulk soft-delete. Per-row isDeletable + Gate check mirror the
     * web controller.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $this->authorize('delete', Component::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            $component = Component::find($id);
            if (is_null($component)) {
                $errors[] = trans('admin/components/message.does_not_exist', ['id' => $id]);

                continue;
            }

            if (! Gate::allows('delete', $component)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            // numCheckedOut() reads sum_unconstrained_assets. Load it
            // first so isDeletable() does not fire a per-row sub-query.
            $component->loadSum('unconstrainedAssets as sum_unconstrained_assets', 'components_assets.assigned_qty');
            if (! $component->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $component->name,
                    'item' => trans('general.component'),
                ]);

                continue;
            }

            $component->delete();
            $success_count++;
        }

        $payload = ['deleted' => $success_count, 'errors' => $errors];
        $message = $success_count > 0
            ? trans_choice('admin/components/message.bulk.delete.success', $success_count, ['count' => $success_count])
            : trans('admin/components/message.bulk.no_components_selected');

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
        $this->authorize('delete', Component::class);

        $ids = (array) $request->input('ids', []);
        if ($ids === []) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.bulk.no_components_selected')), 422);
        }

        $components = Component::withTrashed()->whereIn('id', $ids)->get();

        $restored = 0;
        $errors = [];

        foreach ($components as $component) {
            if (! Gate::allows('delete', $component)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if ($component->deleted_at === null) {
                continue;
            }

            if ($component->restore()) {
                $restored++;
            }
        }

        return response()->json(Helper::formatStandardApiResponse(
            $restored > 0 ? 'success' : 'error',
            ['restored' => $restored, 'errors' => $errors],
            trans_choice('admin/components/message.bulk.restore.success', $restored, ['count' => $restored]),
        ), $restored > 0 ? 200 : 422);
    }

    /**
     * Display all assets attached to a component
     *
     * @author [A. Bergamasco] [@vjandrea]
     *
     * @since [v4.0]
     */
    public function getAssets(Component $component, Request $request): array
    {
        // Backs the default "Assigned" tab on the component show
        // page. Anyone who can view this specific component reaches
        // that page, so gating on view Asset alone (which
        // components-only viewers don't have) 403s the tab that
        // opens by default and breaks the show page for scoped
        // viewers. Accept either view on this component OR view on
        // Asset. Asset viewers who hit this endpoint directly still
        // work as they did before this widen. The BelongsToMany
        // below still applies Asset's CompanyableScope so
        // FMCS-scoped viewers see only assets in their own company.
        abort_unless(Gate::allows('view', $component) || Gate::allows('view', Asset::class), 403);

        $offset = request('offset', 0);
        $limit = $request->input('limit', 50);

        if ($request->filled('search')) {
            $assets = $component->assets()
                ->where(function ($query) use ($request) {
                    $search_str = '%'.$request->input('search').'%';
                    $query->where('name', 'like', $search_str)
                        ->orWhereIn('model_id', function (Builder $query) use ($request) {
                            $search_str = '%'.$request->input('search').'%';
                            $query->selectRaw('id')->from('models')->where('name', 'like', $search_str);
                        })
                        ->orWhere('asset_tag', 'like', $search_str);
                })
                ->get();
            $total = $assets->count();
        } else {
            $assets = $component->assets();
            $total = $assets->count();
            $assets = $assets->skip($offset)->take($limit)->get();
        }

        return (new ComponentsTransformer)->transformCheckedoutComponents($assets, $total);
    }

    /**
     * Validate and checkout the component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * t
     *
     * @since [v5.1.8]
     *
     * @param  int  $componentId
     */
    public function checkout(Request $request, $componentId): JsonResponse
    {
        // Check if the component exists
        if (! $component = Component::find($componentId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.does_not_exist')));
        }

        $this->authorize('checkout', $component);

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:assets,id',
            'assigned_qty' => 'required|numeric|min:1|digits_between:1,'.$component->numRemaining(),
        ]);

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', $validator->errors()));

        }

        // Make sure there is at least one available to checkout
        if ($component->numRemaining() < $request->input('assigned_qty')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.checkout.unavailable', ['remaining' => $component->numRemaining(), 'requested' => $request->input('assigned_qty')])));
        }

        if ($component->numRemaining() >= $request->input('assigned_qty')) {
            // Resolve the raw target first, then enforce FMCS explicitly.
            // Scoped lookup can hide cross-company records and lead to partial writes.
            $asset = Asset::withoutGlobalScopes()->find($request->input('assigned_to'));

            // withoutGlobalScopes bypasses SoftDeletes so we can distinguish
            // "no such asset" from "in another company" for FMCS messaging.
            // Trashed assets must not be treated as valid checkout targets.
            if ($asset && ! empty($asset->deleted_at)) {
                $asset = null;
            }

            if (! $asset) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')));
            }

            if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($component->company_id !== $asset->company_id)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));
            }

            // Concurrency guard. The numRemaining() checks above are
            // unlocked reads, so two simultaneous checkout requests could
            // both pass, both attach a pivot row, and land the register at
            // -1. Re-fetch the parent under lockForUpdate INSIDE the
            // transaction and re-check against the locked snapshot before
            // writing. Mirrors the License checkout locking pattern.
            $overAllocated = false;

            try {
                DB::transaction(function () use ($component, $request, $asset, &$overAllocated): void {
                    $locked = Component::whereKey($component->id)->lockForUpdate()->first();

                    if (! $locked || $locked->numRemaining() < $request->input('assigned_qty')) {
                        $overAllocated = true;

                        return;
                    }

                    $component->assigned_to = $request->input('assigned_to');

                    $component->assets()->attach($component->id, [
                        'component_id' => $component->id,
                        'created_at' => Carbon::now(),
                        'assigned_qty' => $request->input('assigned_qty', 1),
                        'created_by' => auth()->id(),
                        'asset_id' => $request->input('assigned_to'),
                        'note' => $request->input('note'),
                    ]);

                    $component->logCheckout($request->input('note'), $asset, null, [], $request->get('assigned_qty', 1));
                });
            } catch (MissingLogTarget $e) {
                // Loggable trait fell through its target check inside the
                // transaction. DB::transaction rethrew on exception, so the
                // pivot attach was rolled back and no checkout persisted.
                // Downgrade what would otherwise surface as an unhandled 500
                // to a 4xx the client can act on, and warning-log for
                // triage (see the same pattern in LicenseSeatsController).
                Log::warning('logCheckout target validation failed during component checkout.', [
                    'component_id' => $component->id,
                    'asset_id' => $request->input('assigned_to'),
                    'error' => $e->getMessage(),
                ]);

                return response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'), 422);
            }

            if ($overAllocated) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.checkout.unavailable', [
                    'remaining' => $component->fresh()->numRemaining(),
                    'requested' => $request->input('assigned_qty'),
                ])));
            }

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/components/message.checkout.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.checkout.unavailable', ['remaining' => $component->numRemaining(), 'requested' => $request->input('assigned_qty')])));
    }

    /**
     * Validate and store checkin data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v5.1.8]
     */
    public function checkin(Request $request, $component_asset_id): JsonResponse
    {
        if ($component_assets = DB::table('components_assets')->find($component_asset_id)) {
            if (is_null($component = Component::find($component_assets->component_id))) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.not_found')));
            }

            $this->authorize('checkin', $component);

            $max_to_checkin = $component_assets->assigned_qty;

            $validator = Validator::make($request->all(), [
                'checkin_qty' => "required|numeric|between:1,$max_to_checkin",
            ]);

            if ($validator->fails()) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Checkin quantity must be between 1 and '.$max_to_checkin));
            }

            // Validation passed, so let's figure out what we have to do here.
            $qty_remaining_in_checkout = ($component_assets->assigned_qty - (int) $request->input('checkin_qty', 1));

            // We have to modify the record to reflect the new qty that's
            // actually checked out.
            $component_assets->assigned_qty = $qty_remaining_in_checkout;

            Log::debug($component_asset_id.' - '.$qty_remaining_in_checkout.' remaining in record '.$component_assets->id);

            DB::table('components_assets')->where('id', $component_asset_id)->update(['assigned_qty' => $qty_remaining_in_checkout]);

            // If the checked-in qty is exactly the same as the assigned_qty,
            // we can simply delete the associated components_assets record
            if ($qty_remaining_in_checkout === 0) {
                DB::table('components_assets')->where('id', '=', $component_asset_id)->delete();
            }

            $asset = Asset::find($component_assets->asset_id);

            event(new CheckoutableCheckedIn($component, $asset, auth()->user(), $request->input('note'), Carbon::now()));

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/components/message.checkin.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'No matching checkouts for that component join record'));
    }

    public function history(Request $request, Component $component): JsonResponse|array
    {
        $this->authorize('history', $component);
        $historyQuery = $component->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * List components that are requestable AND reachable by the
     * current caller (per FMCS + location scoping). Hydrates the
     * components tab on /account/requestable. See the sibling
     * AccessoriesController::requestable for design rationale.
     */
    public function requestable(Request $request): array
    {
        $this->authorize('viewRequestable', Component::class);

        $query = Component::with('category', 'location', 'company', 'manufacturer', 'requests')
            ->withCount('assets as components_assets_count')
            ->Requestable();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('components.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('components.model_number', 'LIKE', '%'.$search.'%');
            });
        }

        $total = $query->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), ['name', 'created_at'], true) ? $request->input('sort') : 'name';

        $rows = $query->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        return (new ComponentsTransformer)->transformRequestedComponents($rows, $total);
    }
}
