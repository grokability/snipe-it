<?php

namespace App\Http\Controllers;

use App\Actions\CheckoutRequests\FulfillCheckoutRequestAction;
use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\StoreAssetModelRequest;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\CustomField;
use App\Models\SnipeModel;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * This class controls all actions related to asset models for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 *
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetModelsController extends Controller
{
    protected MessageBag $validatorErrors;

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the accessories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function index(): View
    {
        $this->authorize('index', AssetModel::class);

        return view('models/index');
    }

    /**
     * Returns a view containing the asset model creation form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function create(): View
    {
        $this->authorize('create', AssetModel::class);

        return view('models/edit')->with('category_type', 'asset')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('item', new AssetModel);
    }

    /**
     * Validate and process the new Asset Model data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     *
     * @param  ImageUploadRequest  $request
     */
    public function store(StoreAssetModelRequest $request): RedirectResponse
    {
        $this->authorize('create', AssetModel::class);
        $model = new AssetModel;

        $model->eol = $request->input('eol');
        $model->depreciation_id = $request->input('depreciation_id');
        $model->name = $request->input('name');
        $model->model_number = $request->input('model_number');
        $model->min_amt = $request->input('min_amt');
        $model->default_purchase_cost = $request->input('default_purchase_cost');
        $model->default_currency = $request->input('default_currency');
        $model->manufacturer_id = $request->input('manufacturer_id');
        $model->category_id = $request->input('category_id');
        $model->notes = $request->input('notes');
        $model->created_by = auth()->id();
        $model->requestable = $request->has('requestable');
        $model->require_serial = $request->input('require_serial', 0);

        if ($request->input('fieldset_id') != '') {
            $model->fieldset_id = $request->input('fieldset_id');
        }

        if ($request->has('use_cloned_image')) {
            $cloned_model_img = AssetModel::select('image')->find($request->input('clone_image_from_id'));
            if ($cloned_model_img) {
                $new_image_name = 'clone-'.date('U').'-'.$cloned_model_img->image;
                $new_image = 'models/'.$new_image_name;
                Storage::disk('public')->copy('models/'.$cloned_model_img->image, $new_image);
                $model->image = $new_image_name;
            }

        } else {
            $model = $request->handleImages($model);
        }

        if ($model->save()) {
            if ($this->shouldAddDefaultValues($request->input())) {
                if (! $this->assignCustomFieldsDefaultValues($model, $request->input('default_values'))) {
                    return redirect()->back()->withInput()->with('error', trans('admin/custom_fields/message.fieldset_default_value.error'));
                }
            }

            return redirect()->route('models.index')->with('success', trans('admin/models/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($model->getErrors());
    }

    /**
     * Returns a view containing the asset model edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function edit(AssetModel $model): View|RedirectResponse
    {
        $this->authorize('update', AssetModel::class);
        $category_type = 'asset';

        return view('models/edit', compact('category_type'))->with('item', $model)->with('depreciation_list', Helper::depreciationList());
    }

    /**
     * Validates and processes form data from the edit
     * Asset Model form based on the model ID passed.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     *
     * @param  ImageUploadRequest  $request
     *
     * @throws AuthorizationException
     */
    public function update(StoreAssetModelRequest $request, AssetModel $model): RedirectResponse
    {
        $this->authorize('update', AssetModel::class);

        $model = $request->handleImages($model);

        /** @var AssetModel $model */
        $model->depreciation_id = $request->input('depreciation_id');
        $model->eol = $request->input('eol');
        $model->name = $request->input('name');
        $model->model_number = $request->input('model_number');
        $model->min_amt = $request->input('min_amt');
        $model->default_purchase_cost = $request->input('default_purchase_cost');
        $model->default_currency = $request->input('default_currency');
        $model->manufacturer_id = $request->input('manufacturer_id');
        $model->category_id = $request->input('category_id');
        $model->notes = $request->input('notes');
        $model->requestable = $request->input('requestable', '0');
        $model->require_serial = $request->input('require_serial', 0);
        $model->fieldset_id = $request->input('fieldset_id');

        if ($model->save()) {
            $this->removeCustomFieldsDefaultValues($model);

            if ($this->shouldAddDefaultValues($request->input())) {
                if (! $this->assignCustomFieldsDefaultValues($model, $request->input('default_values'))) {
                    return redirect()->back()->withInput()->withErrors($this->validatorErrors);
                }
            }

            if ($model->wasChanged('eol')) {
                if ($model->eol > 0) {
                    $newEol = $model->eol;
                    $model->assets()->whereNotNull('purchase_date')->where('eol_explicit', false)
                        ->update(['asset_eol_date' => DB::raw('DATE_ADD(purchase_date, INTERVAL '.$newEol.' MONTH)')]);
                } elseif ($model->eol == 0) {
                    $model->assets()->whereNotNull('purchase_date')->where('eol_explicit', false)
                        ->update(['asset_eol_date' => DB::raw('null')]);
                }
            }

            return redirect()->route('models.index')->with('success', trans('admin/models/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($model->getErrors());
    }

    /**
     * Validate and delete the given Asset Model. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function destroy(AssetModel $model): RedirectResponse
    {
        $this->authorize('delete', AssetModel::class);

        if ($model->assets()->count() > 0) {
            // Throw an error that this model is associated with assets
            return redirect()->route('models.index')->with('error', trans('admin/models/message.assoc_users'));
        }

        // Delete the model
        $model->delete();

        // Redirect to the models management page
        return redirect()->route('models.index')->with('success', trans('admin/models/message.delete.success'));
    }

    /**
     * Restore a given Asset Model (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     *
     * @param  int  $id
     */
    public function getRestore($id): RedirectResponse
    {
        // Restoring a soft-deleted record is a delete-tier action across the
        // project (users, locations, manufacturers, assets, and this same
        // model's API sibling Api\AssetModelsController::restore all
        // authorize `delete`). The `create` gate was inconsistent and let
        // an account holding models.create but not models.delete reverse
        // an admin's soft-delete.
        $this->authorize('delete', AssetModel::class);

        if ($model = AssetModel::withTrashed()->find($id)) {

            if ($model->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.asset_model')]));
            }

            if ($model->restore()) {
                // The `restore` action_log entry is written by
                // AssetModelObserver::restoring, no manual write here.

                // Redirect them to the deleted page if there are more, otherwise the section index
                $deleted_models = AssetModel::onlyTrashed()->count();
                if ($deleted_models > 0) {
                    return redirect()->back()->with('success', trans('admin/models/message.restore.success'));
                }

                return redirect()->route('models.index')->with('success', trans('admin/models/message.restore.success'));
            }

            // Check validation
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.asset_model'), 'error' => $model->getErrors()->first()]));
        }

        return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));

    }

    /**
     * Get the model information to present to the model view page
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function show(AssetModel $model): View|RedirectResponse
    {
        $this->authorize('view', AssetModel::class);

        return view('models/view', compact('model'));
    }

    /**
     * Get the clone page to clone a model
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function getClone(AssetModel $model): View|RedirectResponse
    {
        $this->authorize('clone', $model);

        $cloned_model = clone $model;
        // Preserve the source model's id BEFORE we blank the working copy — the
        // fieldset picker Livewire component uses model_id to look up the source
        // model's fieldset and default values, so the clone form arrives with
        // the same fieldset preselected. Regression: #19286.
        $source_model_id = $model->id;
        $model->id = null;
        $model->deleted_at = null;

        // Show the page
        return view('models/edit')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('item', $model)
            ->with('model_id', $source_model_id)
            ->with('cloned_model', $cloned_model);
    }

    /**
     * Get the custom fields form
     *
     * @author [B. Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v2.0]
     *
     * @param  int  $modelId
     */
    public function getCustomFields($modelId): View
    {
        // Endpoint fires from the asset create/edit form's model-select
        // change handler (hardware/edit.blade.php's fetchCustomFields
        // XHR) to swap in the picked model's custom-field form partial.
        // Zero-permission accounts must be blocked. This used to have
        // no guard at all, so anyone authenticated could enumerate every
        // model's schema by iterating IDs. The guard has to accept
        // the three legit caller shapes: asset creators (from /hardware/
        // create), asset editors (from /hardware/{id}/edit), and model
        // viewers (from /models/{id}/edit's custom-field preview). Any-of
        // is cheaper than falling back on Gate::authorize's single-perm
        // check because none of those three alone covers every legit
        // caller.
        $user = auth()->user();
        if (! $user?->can('view', AssetModel::class)
            && ! $user?->can('create', Asset::class)
            && ! $user?->can('update', Asset::class)) {
            abort(403);
        }

        return view('models.custom_fields_form')->with('model', AssetModel::find($modelId));
    }

    /**
     * Returns a view that allows the user to bulk edit model attributes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.7]
     */
    public function postBulkEdit(Request $request): View|RedirectResponse
    {
        $models_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->orderBy('assets_count', 'ASC')->get();

            // If deleting....
            if ($request->input('bulk_actions') == 'delete') {
                $valid_count = 0;
                foreach ($models as $model) {
                    if ($model->assets_count == 0) {
                        $valid_count++;
                    }
                }

                if ($valid_count === 0) {
                    return redirect()->route('models.index')
                        ->with('error', trans('admin/models/message.bulkdelete.nothing_deletable'));
                }

                return view('models/bulk-delete', compact('models'))->with('valid_count', $valid_count);

                // Otherwise display the bulk edit screen
            } else {
                $nochange = ['NC' => 'No Change'];
                $fieldset_list = $nochange + Helper::customFieldsetList();
                $depreciation_list = $nochange + Helper::depreciationList();

                return view('models/bulk-edit', compact('models'))
                    ->with('fieldset_list', $fieldset_list)
                    ->with('depreciation_list', $depreciation_list);
            }
        }

        return redirect()->route('models.index')
            ->with('error', 'You must select at least one model to edit.');
    }

    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.7]
     */
    public function postBulkEditSave(Request $request): RedirectResponse
    {
        $models_raw_array = $request->input('ids');
        $update_array = [];

        if (($request->filled('manufacturer_id') && ($request->input('manufacturer_id') != 'NC'))) {
            $update_array['manufacturer_id'] = $request->input('manufacturer_id');
        }
        if (($request->filled('category_id') && ($request->input('category_id') != 'NC'))) {
            $update_array['category_id'] = $request->input('category_id');
        }
        if ($request->input('fieldset_id') != 'NC') {
            $update_array['fieldset_id'] = $request->input('fieldset_id');
        }
        if ($request->input('depreciation_id') != 'NC') {
            $update_array['depreciation_id'] = $request->input('depreciation_id');
        }

        if (count($update_array) > 0) {
            AssetModel::whereIn('id', $models_raw_array)->update($update_array);

            return redirect()->route('models.index')
                ->with('success', trans('admin/models/message.bulkedit.success'));
        }

        return redirect()->route('models.index')
            ->with('warning', trans('admin/models/message.bulkedit.error'));
    }

    /**
     * Validate and delete the given Asset Models. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function postBulkDelete(Request $request): RedirectResponse
    {
        $models_raw_array = $request->input('ids');

        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->get();

            $del_error_count = 0;
            $del_count = 0;

            foreach ($models as $model) {

                if ($model->assets_count > 0) {
                    $del_error_count++;
                } else {
                    $model->delete();
                    $del_count++;
                }
            }

            if ($del_error_count == 0) {
                return redirect()->route('models.index')
                    ->with('success', trans('admin/models/message.bulkdelete.success', ['success_count' => $del_count]));
            }

            return redirect()->route('models.index')
                ->with('warning', trans('admin/models/message.bulkdelete.success_partial', ['fail_count' => $del_error_count, 'success_count' => $del_count]));
        }

        return redirect()->route('models.index')
            ->with('error', trans('admin/models/message.bulkdelete.error'));
    }

    /**
     * Returns true if a fieldset is set, 'add default values' is ticked and if
     * any default values were entered into the form.
     */
    private function shouldAddDefaultValues(array $input): bool
    {
        return ! empty($input['add_default_values'])
            && ! empty($input['default_values'])
            && ! empty($input['fieldset_id']);
    }

    /**
     * Adds default values to a model (as long as they are truthy)
     *
     * @param  AssetModel  $model
     */
    private function assignCustomFieldsDefaultValues(AssetModel|SnipeModel $model, array $defaultValues): bool
    {
        $data = [];
        foreach ($defaultValues as $customFieldId => $defaultValue) {
            $customField = CustomField::find($customFieldId);

            $data[$customField->db_column] = $defaultValue;
        }

        $allRules = $model->fieldset->validation_rules();
        $rules = [];

        foreach ($allRules as $field => $validation) {
            // If the field is marked as required, eliminate the rule so it doesn't interfere with the default values
            // (we are at model level, the rule still applies when creating a new asset using this model)
            $index = array_search('required', $validation);
            if ($index !== false) {
                $validation[$index] = 'nullable';
            }
            $rules[$field] = $validation;
        }

        $attributes = [];
        foreach ($model->fieldset->fields as $field) {
            $attributes[$field->db_column] = trim(preg_replace('/_+|snipeit|\d+/', ' ', $field->db_column));
        }

        $validator = Validator::make($data, $rules)->setAttributeNames($attributes);

        if ($validator->fails()) {
            $this->validatorErrors = $validator->errors();

            return false;
        }

        foreach ($defaultValues as $customFieldId => $defaultValue) {
            if (is_array($defaultValue)) {
                $model->defaultValues()->attach($customFieldId, ['default_value' => implode(', ', $defaultValue)]);
            } elseif ($defaultValue) {
                $model->defaultValues()->attach($customFieldId, ['default_value' => $defaultValue]);
            }
        }

        return true;
    }

    /**
     * Removes all default values
     */
    private function removeCustomFieldsDefaultValues(AssetModel|SnipeModel $model): void
    {
        $model->defaultValues()->detach();
    }

    /**
     * Bulk-fulfill screen for a model's pending-request queue. An
     * AssetModel request ("I want a Macbook Pro 14") fulfills by
     * checking out a specific available Asset OF that model to the
     * requester - so each row here picks an asset from the model's
     * currently-available pool. Small pool = plain <select> picker
     * with the first available option pre-selected as an "auto-
     * pick" default (admins who don't care which specific unit can
     * just tick + submit, and admins who do care flip the select).
     *
     * Rows compete for the same limited pool, so the picker options
     * per row all draw from the SAME available-asset list up-front.
     * The store method serializes assignments and validates the
     * chosen asset is still free at commit time (rollback the row
     * if someone else grabbed it in between page-load and submit).
     */
    public function bulkFulfillRequests(AssetModel $model)
    {
        $this->authorize('checkout', Asset::class);

        $pendingRequests = CheckoutRequest::pending()
            ->where('requestable_type', AssetModel::class)
            ->where('requestable_id', $model->id)
            ->with('user')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (CheckoutRequest $r) => $r->user !== null)
            ->values();

        if ($pendingRequests->isEmpty()) {
            return redirect()->route('models.show', $model)
                ->with('info', trans('admin/hardware/message.requests.no_active'));
        }

        // Available = deployable-status assets of this model with
        // no current assignee. Same pool feeds every row's picker;
        // admins can hand out the same pre-selected first option
        // to N requesters by editing each row, or use the auto-
        // pick default and only fulfill up to available_count rows
        // at a time.
        $availableAssets = Asset::where('model_id', $model->id)
            ->whereNull('assigned_to')
            ->whereHas('status', fn ($q) => $q->where('deployable', 1))
            ->with('model')
            ->orderBy('asset_tag')
            ->get();

        // Auto-pick: pre-select a DIFFERENT asset per row so a
        // straight submit without edits doesn't collide (every
        // row defaulting to the first available asset would
        // conflict at commit-time with only the first row
        // winning). Nth row gets the Nth available asset in the
        // pool. If there are more rows than available assets,
        // the trailing rows pre-select the pool's last option
        // (admin has to manually resolve the overflow, or wait
        // for stock). The full pool still populates each select
        // so admins can override the auto-pick.
        $rowContext = [];
        $poolIndex = 0;
        foreach ($pendingRequests as $request) {
            $default = null;
            if ($availableAssets->isNotEmpty()) {
                $default = ($availableAssets[$poolIndex] ?? $availableAssets->last())->id;
                $poolIndex++;
            }
            $rowContext[$request->id] = [
                'availableAssets' => $availableAssets,
                'defaultAssetId' => $default,
                'emptyMessage' => $availableAssets->isEmpty()
                    ? trans('admin/hardware/message.requests.no_available_units')
                    : null,
            ];
        }

        return view('checkouts/fulfill-multiple-to-asset', [
            'item' => $model,
            'pendingRequests' => $pendingRequests,
            'rowContext' => $rowContext,
            'formRoute' => route('models.fulfill-requests.store', $model),
            'remaining' => $availableAssets->count(),
            // AssetModel is one asset per request (each fulfillment
            // consumes one unit from the pool); no per-row qty
            // input needed.
            'hideQty' => true,
        ]);
    }

    /**
     * Iterate + fulfill. Each ticked row assigns the admin-picked
     * asset to the requester in its own transaction (per-row
     * partial-success). Uses lockForUpdate on the target asset to
     * catch the race where two admins load the same page and both
     * try to hand out the same unit - the second submit sees
     * assigned_to already set and skips the row with an error.
     *
     * Since the checkout target is a User (asset -> user), the
     * state-machine event listener catches the fulfillment and
     * closes the matching request automatically. No explicit
     * FulfillCheckoutRequestAction call needed here - but we call
     * it anyway as belt-and-suspenders for the case where the
     * listener would otherwise silently skip (e.g. LicenseSeat -
     * unrelated but same defensive pattern).
     */
    public function bulkFulfillStoreRequests(Request $request, AssetModel $model): RedirectResponse
    {
        $this->authorize('checkout', Asset::class);

        // Checkboxes post as enabled_requests[<request_id>]="1",
        // keyed by request id (unchecked boxes don't post at all).
        $enabledIds = collect(array_keys((array) $request->input('enabled_requests', [])))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($enabledIds->isEmpty()) {
            return redirect()->route('models.fulfill-requests.create', $model)
                ->with('error', trans('admin/hardware/message.requests.no_selection'));
        }

        $assetInputs = (array) $request->input('asset_id', []);
        $noteInputs = (array) $request->input('notes', []);

        $requests = CheckoutRequest::pending()
            ->where('requestable_type', AssetModel::class)
            ->where('requestable_id', $model->id)
            ->whereIn('id', $enabledIds)
            ->with('user')
            ->get()
            ->keyBy('id');

        $adminUser = auth()->user();
        $fulfilled = 0;
        $errors = [];

        foreach ($enabledIds as $requestId) {
            /** @var CheckoutRequest|null $checkoutRequest */
            $checkoutRequest = $requests->get($requestId);
            if (! $checkoutRequest) {
                $errors[] = trans('admin/hardware/message.requests.row_stale', ['id' => $requestId]);

                continue;
            }

            $targetAssetId = (int) ($assetInputs[$requestId] ?? 0);
            $note = $noteInputs[$requestId] ?? $checkoutRequest->notes;

            $requester = $checkoutRequest->user;
            if (! $requester) {
                $errors[] = trans('admin/hardware/message.requests.row_user_missing', ['id' => $requestId]);

                continue;
            }

            $checkedOut = false;

            DB::transaction(function () use ($model, $targetAssetId, $requester, $adminUser, $note, $requestId, &$errors, &$checkedOut): void {
                $locked = Asset::whereKey($targetAssetId)->lockForUpdate()->first();

                // Model + availability guard. The picker only offers
                // available assets of this model, but a hand-crafted
                // POST or a stale page load could point at an off-
                // model or already-assigned asset. Re-check both
                // under lock so the pool contract holds.
                if (! $locked || $locked->model_id !== $model->id) {
                    $errors[] = trans('admin/hardware/message.requests.row_asset_not_available', ['id' => $requestId]);

                    return;
                }
                if (! empty($locked->assigned_to)) {
                    $errors[] = trans('admin/hardware/message.requests.row_asset_taken', ['id' => $requestId]);

                    return;
                }
                if (! $locked->availableForCheckout()) {
                    $errors[] = trans('admin/hardware/message.requests.row_asset_not_available', ['id' => $requestId]);

                    return;
                }

                $checkedOut = (bool) $locked->checkOut(
                    $requester,
                    $adminUser,
                    date('Y-m-d H:i:s'),
                    null,
                    $note,
                );
            });

            if (! $checkedOut) {
                continue;
            }

            // The state-machine listener only matches requestable_type=Asset;
            // AssetModel-typed request rows would slip past it. Call
            // the action explicitly, passing qty=1 - each row hands
            // out ONE asset, so a request for "3 laptops of this
            // model" needs three row-submits (or three model
            // requests) to fully close. Partial-fulfillment
            // tracking keeps the row on the queue between passes.
            try {
                FulfillCheckoutRequestAction::run($checkoutRequest, null, 1);
            } catch (\Throwable $e) {
                Log::warning('Bulk-fulfill state-machine close failed for request '.$requestId.': '.$e->getMessage());
            }

            $fulfilled++;
        }

        $summary = trans('admin/hardware/message.requests.bulk_summary', [
            'fulfilled' => $fulfilled,
            'total' => $enabledIds->count(),
        ]);

        return redirect()->route('requests.index')
            ->with($fulfilled > 0 ? 'success' : 'warning', $summary)
            ->with('multi_error_messages', $errors);
    }
}
