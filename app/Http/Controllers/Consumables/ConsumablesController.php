<?php

namespace App\Http\Controllers\Consumables;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustQuantityRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\StoreConsumableRequest;
use App\Http\Traits\HandlesAdjustQuantity;
use App\Models\Company;
use App\Models\Consumable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * This controller handles all actions related to Consumables for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ConsumablesController extends Controller
{
    use HandlesAdjustQuantity;

    /**
     * Return a view to display component information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumablesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Consumable::class);

        return view('consumables/index');
    }

    /**
     * Return a view to display the form view to create a new consumable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumablesController::postCreate() method that stores the form data
     * @since [v1.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Consumable::class);

        return view('consumables.edit')->with('category_type', 'consumable')
            ->with('item', new Consumable)
            ->with('pendingRequestCount', 0);
    }

    /**
     * Validate and store new consumable data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumablesController::getCreate() method that returns the form view
     * @since [v1.0]
     *
     * @param  ImageUploadRequest  $request
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreConsumableRequest $request)
    {
        $this->authorize('create', Consumable::class);
        $consumable = new Consumable;
        $consumable->name = $request->input('name');
        $consumable->category_id = $request->input('category_id');
        $consumable->location_id = $request->input('location_id');
        $consumable->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        // order_number / supplier_id / purchase_date / purchase_cost all
        // moved off the parent column to Orders / OrderItems.
        $consumable->min_amt = $request->input('min_amt');
        $consumable->manufacturer_id = $request->input('manufacturer_id');
        $consumable->model_number = $request->input('model_number');
        $consumable->item_no = $request->input('item_no');
        $consumable->qty = $request->input('qty');
        $consumable->created_by = auth()->id();
        $consumable->notes = $request->input('notes');
        // Unchecked checkboxes are omitted from the POST body; coerce
        // absence to false so the flag really flips off. The setter
        // normalizes the truthy branch.
        $consumable->requestable = $request->input('requestable', false);
        $consumable->archived = $request->input('archived', 0);
        // Seed the template supplier from the initial-acquisition
        // supplier on the create form, editable afterwards.
        $consumable->default_supplier_id = $request->input('default_supplier_id', $request->input('supplier_id'));

        if ($request->has('use_cloned_image')) {
            // Authorize the source first
            $cloned = Consumable::findOrFail($request->input('clone_image_from_id'));
            $this->authorize('clone', $cloned);

            $source_image = $cloned->image;
            if ($source_image) {
                $new_image_name = 'clone-' . date('U') . '-' . $source_image;
                Storage::disk('public')->copy(
                    FileStorage::Consumables->publicPath() . $source_image,
                    FileStorage::Consumables->publicPath() . $new_image_name,
                );
                $consumable->image = $new_image_name;
            }

        } else {
            $consumable = $request->handleImages($consumable);
        }

        if ($request->input('redirect_option') === 'back') {
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->input('redirect_option')]);
        }

        if ($consumable->save()) {
            $this->enrichInitialOrderFromRequest($request, $consumable);

            return Helper::getRedirectOption($request, $consumable->id, 'Consumables')
                ->with('success', trans('admin/consumables/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($consumable->getErrors());
    }

    /**
     * Returns a form view to edit a consumable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumablesController::postEdit() method that stores the form data.
     * @since [v1.0]
     */
    public function edit(Consumable $consumable): View|RedirectResponse
    {
        $this->authorize($consumable);
        if ($safeReferer = Helper::sameOriginUrl(url()->previous())) {
            session()->put('url.intended', $safeReferer);
        }

        return view('consumables/edit')
            ->with('item', $consumable)
            ->with('category_type', 'consumable')
            ->with('pendingRequestCount', $consumable->openRequests()->count());

    }

    /**
     * Returns a form view to edit a consumable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  ImageUploadRequest  $request
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     *
     * @see ConsumablesController::getEdit() method that stores the form data.
     * @since [v1.0]
     */
    public function update(StoreConsumableRequest $request, Consumable $consumable)
    {
        $this->authorize($consumable);

        // qty and order_number are intentionally NOT accepted on update.
        // See AccessoriesController::update for rationale. supplier_id is
        // editable again (imperfect single-value semantics accepted for
        // the info-panel display).
        $consumable->name = $request->input('name');
        $consumable->category_id = $request->input('category_id');
        $consumable->location_id = $request->input('location_id');
        $consumable->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $consumable->min_amt = $request->input('min_amt');
        $consumable->manufacturer_id = $request->input('manufacturer_id');
        $consumable->model_number = $request->input('model_number');
        $consumable->item_no = $request->input('item_no');
        // supplier_id, purchase_date, purchase_cost are create-only on
        // the parent. Post-create acquisitions live as Orders +
        // OrderItems (each with its own supplier / date / price).
        // default_supplier_id remains editable — see accessory update
        // controller for the parent-as-template rationale.
        $consumable->default_supplier_id = $request->input('default_supplier_id');
        $consumable->notes = $request->input('notes');
        // Unchecked checkbox is omitted from the POST body. Coerce
        // absence to false so unchecking really turns the flag off.
        $consumable->requestable = $request->input('requestable', false);
        $consumable->archived = $request->input('archived', 0);

        $consumable = $request->handleImages($consumable);

        session()->put(['redirect_option' => $request->input('redirect_option')]);

        if ($consumable->save()) {
            return Helper::getRedirectOption($request, $consumable->id, 'Consumables')
                ->with('success', trans('admin/consumables/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($consumable->getErrors());
    }

    /**
     * Delete a consumable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $consumableId
     *
     * @since [v1.0]
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy($consumableId)
    {
        if (is_null($consumable = Consumable::find($consumableId))) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.not_found'));
        }
        $this->authorize($consumable);

        $consumable->delete();

        // Redirect to the locations management page
        return redirect()->route('consumables.index')->with('success', trans('admin/consumables/message.delete.success'));
    }

    /**
     * Restore a soft-deleted consumable. Mirrors AccessoriesController::postRestore
     * (restoring is a delete-tier action across the project).
     */
    public function postRestore($id): RedirectResponse
    {
        if ($consumable = Consumable::withTrashed()->find($id)) {
            // Per-instance authorize so the policy's before() hook runs
            // its FMCS isCurrentUserHasAccess check against the actual
            // row, not just the class.
            $this->authorize('delete', $consumable);

            if ($consumable->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.consumable')]));
            }

            if ($consumable->restore()) {
                return redirect()->route('consumables.show', $consumable)
                    ->with('success', trans('admin/consumables/message.restore.success'));
            }

            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.consumable'), 'error' => $consumable->getErrors()->first()]));
        }

        return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.does_not_exist'));
    }

    /**
     * Flip a consumable back to live inventory. Mirrors postRestore's
     * auth shape so unarchiving goes through the same per-instance
     * authorize path as restore from soft-delete.
     */
    public function postUnarchive(Consumable $consumable): RedirectResponse
    {
        $this->authorize('update', $consumable);

        if (! $consumable->archived) {
            return redirect()->back()->with('error', trans('admin/consumables/message.does_not_exist'));
        }

        $consumable->archived = false;

        if ($consumable->save()) {
            return redirect()->back()->with('success', trans('general.unarchive_success', ['item_type' => trans('general.consumable')]));
        }

        return redirect()->back()->withErrors($consumable->getErrors());
    }

    /**
     * Return a view to display component information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumablesController::getDataView() method that generates the JSON response
     * @since [v1.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(Consumable $consumable)
    {
        // Route-model binding resolves soft-deleted rows via
        // withTrashed(['show']) on the resource route, so re-fetch
        // with withTrashed() as well. Without it the show page 404s
        // for admins following a link from the Deleted index view.
        $consumable = Consumable::withTrashed()
            ->withCount('users as users_consumables')
            ->find($consumable->id);
        $this->authorize($consumable);

        return view('consumables/view', compact('consumable'));
    }

    public function clone(Consumable $consumable): View
    {
        $this->authorize('clone', $consumable);
        $consumable_to_close = $consumable;
        $consumable = clone $consumable_to_close;
        $consumable->id = null;
        $consumable->created_by = null;

        // See AccessoriesController::getClone for the rationale, including
        // the note on why these are explicit assignments not a foreach.
        $prefill = $consumable_to_close->lastOrderPrefill();
        $consumable->supplier_id = $prefill['supplier_id'];
        $consumable->purchase_date = $prefill['purchase_date'];
        $consumable->purchase_cost = $prefill['purchase_cost'];
        $consumable->order_number = $prefill['order_number'];

        return view('consumables/edit')
            ->with('cloned_model', $consumable_to_close)
            ->with('item', $consumable)
            ->with('pendingRequestCount', 0);
    }

    /**
     * Apply an on-hand quantity delta (+/-) and log the change. Route
     * exists here so route-model binding resolves against Consumable,
     * everything else lives on HandlesAdjustQuantity.
     */
    public function adjustQuantity(AdjustQuantityRequest $request, Consumable $consumable): RedirectResponse
    {
        return $this->adjustQuantityAsRedirect($request, $consumable);
    }
}
