<?php

namespace App\Http\Controllers\Components;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustQuantityRequest;
use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Http\Traits\HandlesAdjustQuantity;
use App\Models\Company;
use App\Models\Component;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * This class controls all actions related to Components for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ComponentsController extends Controller
{
    use HandlesAdjustQuantity;

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the components listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentsController::getDatatable() method that generates the JSON response
     * @since [v3.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('view', Component::class);

        return view('components/index');
    }

    /**
     * Returns a form to create a new component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentsController::postCreate() method that stores the data
     * @since [v3.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Component::class);

        return view('components/edit')->with('category_type', 'component')
            ->with('item', new Component)
            ->with('pendingRequestCount', 0);
    }

    /**
     * Validate and store data for new component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentsController::getCreate() method that generates the view
     * @since [v3.0]
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreComponentRequest $request)
    {
        $this->authorize('create', Component::class);
        $component = new Component;
        $component->name = $request->input('name');
        $component->category_id = $request->input('category_id');
        $component->manufacturer_id = $request->input('manufacturer_id');
        $component->model_number = $request->input('model_number');
        $component->location_id = $request->input('location_id');
        $component->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        // order_number / supplier_id / purchase_date / purchase_cost all
        // moved off the parent column to Orders / OrderItems.
        $component->min_amt = $request->input('min_amt', null);
        $component->serial = $request->input('serial', null);
        $component->qty = $request->input('qty');
        $component->created_by = auth()->id();
        $component->notes = $request->input('notes');
        // Unchecked checkboxes are omitted from the POST body; coerce
        // absence to false so the flag really flips off. The setter
        // normalizes the truthy branch.
        $component->requestable = $request->input('requestable', false);
        $component->archived = $request->input('archived', false);
        // Seed the template supplier from the initial-acquisition
        // supplier on the create form; editable afterwards.
        $component->default_supplier_id = $request->input('default_supplier_id', $request->input('supplier_id'));

        $component = $request->handleImages($component);

        if ($request->input('redirect_option') === 'back') {
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->input('redirect_option')]);
        }

        if ($component->save()) {
            $this->enrichInitialOrderFromRequest($request, $component);

            return Helper::getRedirectOption($request, $component->id, 'Components')
                ->with('success', trans('admin/components/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($component->getErrors());
    }

    /**
     * Return a view to edit a component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentsController::postEdit() method that stores the data.
     * @since [v3.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(Component $component)
    {

        $this->authorize('update', $component);
        if ($safeReferer = Helper::sameOriginUrl(url()->previous())) {
            session()->put('url.intended', $safeReferer);
        }

        return view('components/edit')
            ->with('item', $component)
            ->with('category_type', 'component')
            ->with('pendingRequestCount', $component->openRequests()->count());
    }

    /**
     * Return a view to edit a component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentsController::getEdit() method presents the form.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     *
     * @since [v3.0]
     */
    public function update(UpdateComponentRequest $request, Component $component)
    {
        $this->authorize('update', $component);

        // qty and order_number are intentionally NOT accepted on update.
        // See AccessoriesController::update for rationale. supplier_id is
        // editable again (imperfect single-value semantics accepted for
        // the info-panel display).
        $component->name = $request->input('name');
        $component->category_id = $request->input('category_id');
        $component->manufacturer_id = $request->input('manufacturer_id');
        $component->model_number = $request->input('model_number');
        $component->location_id = $request->input('location_id');
        $component->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        $component->min_amt = $request->input('min_amt');
        $component->serial = $request->input('serial');
        // supplier_id, purchase_date, purchase_cost are create-only on
        // the parent. Post-create acquisitions live as Orders +
        // OrderItems. default_supplier_id remains editable as the
        // parent's "typical supplier" template.
        $component->default_supplier_id = $request->input('default_supplier_id');
        $component->notes = $request->input('notes');
        // Unchecked checkbox is omitted from the POST body; coerce
        // absence to false so unchecking really turns the flag off.
        $component->requestable = $request->input('requestable', false);
        $component->archived = $request->input('archived', false);

        $component = $request->handleImages($component);

        session()->put(['redirect_option' => $request->input('redirect_option')]);

        if ($component->save()) {
            return Helper::getRedirectOption($request, $component->id, 'Components')
                ->with('success', trans('admin/components/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($component->getErrors());
    }

    /**
     * Delete a component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     *
     * @param  int  $componentId
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy($componentId)
    {
        if (is_null($component = Component::find($componentId))) {
            return redirect()->route('components.index')->with('error', trans('admin/components/message.does_not_exist'));
        }

        $this->authorize('delete', $component);

        // Note: the image file is deliberately preserved across this
        // soft-delete. Snipe-IT's `snipeit:purge` command permanently
        // removes it later when the row is force-deleted. Keeping the
        // file here means a restored soft-deleted row still has its
        // image.
        if ($component->numCheckedOut() > 0) {
            return redirect()->route('components.index')->with('error', trans('admin/components/message.delete.error_qty'));
        }

        $component->delete();

        return redirect()->route('components.index')->with('success', trans('admin/components/message.delete.success'));
    }

    /**
     * Restore a soft-deleted component.
     */
    public function postRestore($id): RedirectResponse
    {
        if ($component = Component::withTrashed()->find($id)) {
           
            $this->authorize('delete', $component);

            if ($component->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.component')]));
            }

            if ($component->restore()) {
                return redirect()->route('components.show', $component)
                    ->with('success', trans('admin/components/message.restore.success'));
            }

            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.component'), 'error' => $component->getErrors()->first()]));
        }

        return redirect()->route('components.index')->with('error', trans('admin/components/message.does_not_exist'));
    }

    /**
     * Return a view to display component information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentsController::getDataView() method that generates the JSON response
     * @since [v3.0]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(Component $component)
    {
        $this->authorize('view', $component);

        return view('components/view', compact('component'))->with('snipe_component', $component);
    }

    public function getClone(Component $component): View|RedirectResponse
    {
        $this->authorize('clone', $component);

        $cloned_component = clone $component;
        $cloned_component->id = null;
        $cloned_component->deleted_at = null;

        // See AccessoriesController::getClone for the rationale, including
        // the note on why these are explicit assignments not a foreach.
        $prefill = $component->lastOrderPrefill();
        $cloned_component->supplier_id = $prefill['supplier_id'];
        $cloned_component->purchase_date = $prefill['purchase_date'];
        $cloned_component->purchase_cost = $prefill['purchase_cost'];
        $cloned_component->order_number = $prefill['order_number'];

        // Show the page
        return view('components/edit')
            ->with('item', $cloned_component)
            ->with('component', $cloned_component)
            ->with('pendingRequestCount', 0);
    }

    /**
     * Apply an on-hand quantity delta (+/-) and log the change. Route
     * exists here so route-model binding resolves against Component,
     * everything else lives on HandlesAdjustQuantity.
     */
    public function adjustQuantity(AdjustQuantityRequest $request, Component $component): RedirectResponse
    {
        return $this->adjustQuantityAsRedirect($request, $component);
    }
}
