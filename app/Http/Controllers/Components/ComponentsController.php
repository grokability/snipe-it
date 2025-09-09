<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Company;
use App\Models\Component;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * This class controls all actions related to Components for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ComponentsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the components listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentsController::getDatatable() method that generates the JSON response
     * @since [v3.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @see ComponentsController::postCreate() method that stores the data
     * @since [v3.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Component::class);

        return view('components/edit')->with('category_type', 'component')
            ->with('item', new Component);
    }

    /**
     * Validate and store data for new component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentsController::getCreate() method that generates the view
     * @since [v3.0]
     * @param ImageUploadRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize('create', Component::class);
        $component = new Component();
        $component->name                   = $request->input('name');
        $component->category_id            = $request->input('category_id');
        $component->supplier_id            = $request->input('supplier_id');
        $component->manufacturer_id        = $request->input('manufacturer_id');
        $component->model_number           = $request->input('model_number');
        $component->location_id            = $request->input('location_id');
        $component->company_id             = Company::getIdForCurrentUser($request->input('company_id'));
        $component->order_number           = $request->input('order_number', null);
        $component->min_amt                = $request->input('min_amt', null);
        $component->serial                 = $request->input('serial', null);
        $component->purchase_date          = $request->input('purchase_date', null);
        $component->purchase_cost          = $request->input('purchase_cost', null);
        $component->qty                    = $request->input('qty');
        $component->created_by                = auth()->id();
        $component->notes                  = $request->input('notes');

        $component = $request->handleImages($component);

        if($request->get('redirect_option') === 'back'){
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->get('redirect_option')]);
        }


        if ($component->save()) {
            return Helper::getRedirectOption($request, $component->id, 'Components')
                ->with('success', trans('admin/components/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($component->getErrors());
    }

    /**
     * Return a view to edit a component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentsController::postEdit() method that stores the data.
     * @since [v3.0]
     * @param int $componentId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Component $component)
    {

            $this->authorize('update', $component);
            session()->put('back_url', url()->previous());
            return view('components/edit')
                ->with('item', $component)
                ->with('category_type', 'component');
    }


    /**
     * Return a view to edit a component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentsController::getEdit() method presents the form.
     * @param ImageUploadRequest $request
     * @param int $componentId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v3.0]
     */
    public function update(ImageUploadRequest $request, Component $component)
    {
        $min = $component->numCheckedOut();
        $validator = Validator::make($request->all(), [
            'qty' => "required|numeric|min:$min",
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->authorize('update', $component);

        // Update the component data
        $component->name                   = $request->input('name');
        $component->category_id            = $request->input('category_id');
        $component->supplier_id            = $request->input('supplier_id');
        $component->manufacturer_id        = $request->input('manufacturer_id');
        $component->model_number           = $request->input('model_number');
        $component->location_id            = $request->input('location_id');
        $component->company_id             = Company::getIdForCurrentUser($request->input('company_id'));
        $component->order_number           = $request->input('order_number');
        $component->min_amt                = $request->input('min_amt');
        $component->serial                 = $request->input('serial');
        $component->purchase_date          = $request->input('purchase_date');
        $component->purchase_cost          = request('purchase_cost');
        $component->qty                    = $request->input('qty');
        $component->notes                  = $request->input('notes');

        $component = $request->handleImages($component);

        session()->put(['redirect_option' => $request->get('redirect_option')]);

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
     * @since [v3.0]
     * @param int $componentId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($componentId)
    {
        if (is_null($component = Component::find($componentId))) {
            return redirect()->route('components.index')->with('error', trans('admin/components/message.does_not_exist'));
        }

        $this->authorize('delete', $component);

        // Remove the image if one exists
        if ($component->image && Storage::disk('public')->exists('components/' . $component->image)) {
            try {
                Storage::disk('public')->delete('components/'.$component->image);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        if ($component->numCheckedOut() > 0) {
            return redirect()->route('components.index')->with('error', trans('admin/components/message.delete.error_qty'));
        }

        $component->delete();

        return redirect()->route('components.index')->with('success', trans('admin/components/message.delete.success'));
    }

    /**
     * Return a view to display component information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ComponentsController::getDataView() method that generates the JSON response
     * @since [v3.0]
     * @param int $componentId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Component $component)
    {
            $this->authorize('view', $component);
            return view('components/view', compact('component'));
    }
}
