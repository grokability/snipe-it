<?php

namespace App\Http\Controllers\Accessories;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustQuantityRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Traits\HandlesAdjustQuantity;
use App\Models\Accessory;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/** This controller handles all actions related to Accessories for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class AccessoriesController extends Controller
{
    use HandlesAdjustQuantity;

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the accessories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see AccessoriesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index(): View
    {
        $this->authorize('index', Accessory::class);

        return view('accessories.index');
    }

    /**
     * Returns a view with a form to create a new Accessory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function create(): View
    {
        $this->authorize('create', Accessory::class);
        $category_type = 'accessory';

        return view('accessories/edit')->with('category_type', $category_type)
            ->with('item', new Accessory)
            ->with('pendingRequestCount', 0);
    }

    /**
     * Validate and save new Accessory from form post
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function store(ImageUploadRequest $request): RedirectResponse
    {
        $this->authorize(Accessory::class);

        // create a new model instance
        $accessory = new Accessory;

        // Update the accessory data
        $accessory->name = request('name');
        $accessory->category_id = request('category_id');
        $accessory->location_id = request('location_id');
        $accessory->min_amt = request('min_amt');
        $accessory->company_id = Company::getIdForCurrentUser(request('company_id'));
        // order_number + currency don't live on the Accessory parent
        // column any more — the HasOrders creation hook writes
        // the initial Order + OrderItem, and we enrich that Order below
        // (after save) with the form-supplied order_number / currency.
        $accessory->manufacturer_id = request('manufacturer_id');
        $accessory->model_number = request('model_number');
        $accessory->qty = request('qty');
        $accessory->created_by = auth()->id();
        $accessory->notes = request('notes');
        $accessory->requestable = request('requestable', 0);
        $accessory->archived = request('archived', 0);
        // Seed the template supplier from the initial-acquisition
        // supplier on the create form so newly-created items already
        // have a "typical" supplier pre-populated. Editable afterwards.
        $accessory->default_supplier_id = request('default_supplier_id', request('supplier_id'));

        if ($request->has('use_cloned_image')) {
            // Authorize the source first
            $cloned = Accessory::findOrFail($request->input('clone_image_from_id'));
            $this->authorize('clone', $cloned);

            $source_image = $cloned->image;
            if ($source_image) {
                $new_image_name = 'clone-' . date('U') . '-' . $source_image;
                Storage::disk('public')->copy(
                    FileStorage::Accessories->publicPath() . $source_image,
                    FileStorage::Accessories->publicPath() . $new_image_name,
                );
                $accessory->image = $new_image_name;
            }

        } else {
            $accessory = $request->handleImages($accessory);
        }

        if ($request->input('redirect_option') === 'back') {
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->input('redirect_option')]);
        }

        // Was the accessory created?
        if ($accessory->save()) {
            $this->enrichInitialOrderFromRequest($request, $accessory);

            // Redirect to the new accessory  page
            return Helper::getRedirectOption($request, $accessory->id, 'Accessories')
                ->with('success', trans('admin/accessories/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($accessory->getErrors());
    }

    /**
     * Return view for the Accessory update form, prepopulated with existing data
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function edit(Accessory $accessory): View|RedirectResponse
    {
        $this->authorize('update', $accessory);
        if ($safeReferer = Helper::sameOriginUrl(url()->previous())) {
            session()->put('url.intended', $safeReferer);
        }

        return view('accessories.edit')
            ->with('item', $accessory)
            ->with('category_type', 'accessory')
            ->with('pendingRequestCount', $accessory->openRequests()->count());
    }

    /**
     * Returns a view that presents a form to clone an accessory.
     *
     * @author [J. Vinsmoke]
     *
     * @since [v6.0]
     */
    public function getClone(Accessory $accessory): View|RedirectResponse
    {

        $this->authorize('clone', $accessory);
        $cloned = clone $accessory;
        $accessory_to_clone = $accessory;
        $cloned->id = null;
        $cloned->deleted_at = '';

        // Restore the pre-Orders clone-as-fast-entry workflow: carry
        // the source item's most recent acquisition context onto the
        // cloned entry's create form so operators cloning a stock row
        // to restock don't have to copy-paste supplier / order # /
        // date / price from another tab. Field names match the
        // create-form input names so enrichInitialOrderFromRequest
        // in store() picks them up on save and writes them onto the
        // initial Order + OrderItem created by HasOrders for the new row.
        // Explicit assignments (not a foreach) so each typed value from
        // lastOrderPrefill() lands on the matching typed model property
        // without going through a mixed intermediate that would fail
        // larastan's assign.propertyType check.
        $prefill = $accessory->lastOrderPrefill();
        $cloned->supplier_id = $prefill['supplier_id'];
        $cloned->purchase_date = $prefill['purchase_date'];
        $cloned->purchase_cost = $prefill['purchase_cost'];
        $cloned->order_number = $prefill['order_number'];

        return view('accessories/edit')
            ->with('cloned_model', $accessory_to_clone)
            ->with('item', $cloned)
            ->with('pendingRequestCount', 0);

    }

    /**
     * Save edited Accessory from form post
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function update(ImageUploadRequest $request, Accessory $accessory): RedirectResponse
    {
        $this->authorize('update', $accessory);

        if ($accessory = Accessory::find($accessory->id)) {

            // qty and order_number are intentionally NOT accepted on
            // update. On-hand qty is managed via the adjust-quantity
            // modal (see AdjustsQuantity trait); each change becomes a
            // QuantityAdjust action_log entry rather than a silent
            // overwrite. order_number is captured onto the create
            // action_log at create time and onto QuantityAdjust log
            // entries thereafter — a single value on multi-batch
            // inventory is misleading, so the model accessor hides it.
            // supplier_id remains editable (imperfect single-value
            // semantics accepted for the info-panel display).
            $accessory->name = request('name');
            $accessory->location_id = request('location_id');
            $accessory->min_amt = request('min_amt');
            $accessory->category_id = request('category_id');
            $accessory->company_id = Company::getIdForCurrentUser(request('company_id'));
            $accessory->manufacturer_id = request('manufacturer_id');
            $accessory->model_number = request('model_number');
            // supplier_id, purchase_date, purchase_cost are create-only
            // on the parent. Post-create acquisitions live as Orders +
            // OrderItems (each with its own supplier / date / price).
            // default_supplier_id remains editable — it's the parent's
            // "typical supplier" template that seeds new Orders when
            // the item has no order history yet.
            $accessory->default_supplier_id = request('default_supplier_id');
            $accessory->notes = request('notes');
            $accessory->requestable = request('requestable', 0);
            $accessory->archived = request('archived', 0);

            $accessory = $request->handleImages($accessory);

            if ($request->input('redirect_option') === 'back') {
                session()->put(['redirect_option' => 'index']);
            } else {
                session()->put(['redirect_option' => $request->input('redirect_option')]);
            }

            if ($accessory->save()) {
                return Helper::getRedirectOption($request, $accessory->id, 'Accessories')
                    ->with('success', trans('admin/accessories/message.update.success'));
            }
        } else {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        return redirect()->back()->withInput()->withErrors($accessory->getErrors());
    }

    /**
     * Delete the given accessory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function destroy(Accessory $accessory): RedirectResponse
    {
        $this->authorize('delete', $accessory);
        $accessory->loadCount('checkouts as checkouts_count');

        if ($accessory->isDeletable()) {
            // Note: the image file is deliberately preserved across this
            // soft-delete. Snipe-IT's `snipeit:purge` command permanently
            // removes it later when the row is force-deleted. Keeping
            // the file here means a restored soft-deleted row still has
            // its image.
            $accessory->delete();

            return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.delete.success'));
        }

        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/general.delete_disabled'));
    }

    /**
     * Restore a soft-deleted accessory.
     */
    public function postRestore($id): RedirectResponse
    {
        if ($accessory = Accessory::withTrashed()->find($id)) {
            // Per-instance authorize so the policy's before() hook runs
            // its FMCS isCurrentUserHasAccess check against the actual
            // row, not just the class.
            $this->authorize('delete', $accessory);

            if ($accessory->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.accessory')]));
            }

            if ($accessory->restore()) {
                return redirect()->route('accessories.show', $accessory)
                    ->with('success', trans('admin/accessories/message.restore.success'));
            }

            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.accessory'), 'error' => $accessory->getErrors()->first()]));
        }

        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
    }

    /**
     * Flip an accessory back to live inventory. Mirrors postRestore's
     * auth shape so unarchiving goes through the same per-instance
     * authorize path as restore from soft-delete.
     */
    public function postUnarchive(Accessory $accessory): RedirectResponse
    {
        $this->authorize('update', $accessory);

        if (! $accessory->archived) {
            return redirect()->back()->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory->archived = false;

        if ($accessory->save()) {
            return redirect()->back()->with('success', trans('general.unarchive_success', ['item_type' => trans('general.accessory')]));
        }

        return redirect()->back()->withErrors($accessory->getErrors());
    }

    /**
     * Returns a view that invokes the ajax table which  contains
     * the content for the accessory detail view, which is generated in getDataView.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see AccessoriesController::getDataView() method that generates the JSON response
     * @since [v1.0]
     */
    public function show(Accessory $accessory): View|RedirectResponse
    {
        $this->authorize('view', $accessory);
        $accessory->loadCount('checkouts as checkouts_count');
        $accessory->load(['adminuser' => fn ($query) => $query->withTrashed()]);

        return view('accessories.view', compact('accessory'));
    }

    /**
     * Apply an on-hand quantity delta (+/-) and log the change. Route
     * exists here so route-model binding resolves against Accessory,
     * everything else lives on HandlesAdjustQuantity.
     */
    public function adjustQuantity(AdjustQuantityRequest $request, Accessory $accessory): RedirectResponse
    {
        return $this->adjustQuantityAsRedirect($request, $accessory);
    }
}
