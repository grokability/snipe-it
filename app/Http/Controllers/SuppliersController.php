<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This controller handles all actions related to Suppliers for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class SuppliersController extends Controller
{
    /**
     * Show a list of all suppliers
     *
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index() : View
    {
        $this->authorize('view', Supplier::class);
        return view('suppliers/index');
    }

    /**
     * Supplier create.
     *
     */
    public function create() : View
    {
        $this->authorize('create', Supplier::class);
        return view('suppliers/edit')->with('item', new Supplier);
    }

    /**
     * Supplier create form processing.
     *
     * @param ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('create', Supplier::class);
        // Create a new supplier
        $supplier = new Supplier;
        // Save the location data
        $supplier->name = request('name');
        $supplier->address = request('address');
        $supplier->address2 = request('address2');
        $supplier->city = request('city');
        $supplier->state = request('state');
        $supplier->country = request('country');
        $supplier->zip = request('zip');
        $supplier->contact = request('contact');
        $supplier->phone = request('phone');
        $supplier->fax = request('fax');
        $supplier->email = request('email');
        $supplier->notes = request('notes');
        $supplier->url = $supplier->addhttp(request('url'));
        $supplier->created_by = auth()->id();
        $supplier = $request->handleImages($supplier);

        if ($supplier->save()) {
            return redirect()->route('suppliers.index')->with('success', trans('admin/suppliers/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($supplier->getErrors());
    }

    /**
     * Supplier update.
     *
     * @param  int $supplierId
     */
    public function edit(Supplier $supplier) : View | RedirectResponse
    {
        $this->authorize('update', Supplier::class);
        return view('suppliers/edit')->with('item',  $supplier);
    }

    /**
     * Supplier update form processing page.
     *
     * @param  int $supplierId
     */
    public function update(ImageUploadRequest $request, Supplier $supplier) : RedirectResponse
    {
        $this->authorize('update', Supplier::class);
        // Save the  data
        $supplier->name = request('name');
        $supplier->address = request('address');
        $supplier->address2 = request('address2');
        $supplier->city = request('city');
        $supplier->state = request('state');
        $supplier->country = request('country');
        $supplier->zip = request('zip');
        $supplier->contact = request('contact');
        $supplier->phone = request('phone');
        $supplier->fax = request('fax');
        $supplier->email = request('email');
        $supplier->url = $supplier->addhttp(request('url'));
        $supplier->notes = request('notes');
        $supplier = $request->handleImages($supplier);

        if ($supplier->save()) {
            return redirect()->route('suppliers.index')->with('success', trans('admin/suppliers/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($supplier->getErrors());
    }

    /**
     * Delete the given supplier.
     *
     * @param  int $supplierId
     */
    public function destroy($supplierId) : RedirectResponse
    {
        $this->authorize('delete', Supplier::class);
        if (is_null($supplier = Supplier::with('maintenances', 'assets', 'licenses')->withCount('maintenances as maintenances_count', 'assets as assets_count', 'licenses as licenses_count')->find($supplierId))) {
            return redirect()->route('suppliers.index')->with('error', trans('admin/suppliers/message.not_found'));
        }

        if ($supplier->assets_count > 0) {
            return redirect()->route('suppliers.index')->with('error', trans('admin/suppliers/message.delete.assoc_assets', ['asset_count' => (int) $supplier->assets_count]));
        }

        if ($supplier->maintenances_count > 0) {
            return redirect()->route('suppliers.index')->with('error', trans('admin/suppliers/message.delete.assoc_maintenances', ['maintenances_count' => $supplier->maintenances_count]));
        }

        if ($supplier->licenses_count > 0) {
            return redirect()->route('suppliers.index')->with('error', trans('admin/suppliers/message.delete.assoc_licenses', ['licenses_count' => (int) $supplier->licenses_count]));
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success',
            trans('admin/suppliers/message.delete.success')
        );
    }

    /**
     *  Get the asset information to present to the supplier view page
     *
     * @param null $supplierId
     * @internal param int $assetId
     */
    public function show(Supplier $supplier) : View | RedirectResponse
    {
        $this->authorize('view', Supplier::class);
        return view('suppliers/view', compact('supplier'));

    }
}
