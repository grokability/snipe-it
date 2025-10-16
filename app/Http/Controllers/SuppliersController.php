<?php

namespace App\Http\Controllers;

use App\Actions\Suppliers\DestroySupplierAction;
use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasMaintenances;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasLicenses;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\MessageBag;

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
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', Supplier::class);
        try {
            DestroySupplierAction::run(supplier: $supplier);
        } catch (ItemStillHasAssets $e) {
            return redirect()->route('suppliers.index')->with('error', trans('general.bulk_delete_associations.assoc_assets', [
                'asset_count' => (int) $supplier->assets_count, 'item' => trans('general.supplier')
            ]));
        } catch (ItemStillHasMaintenances $e) {
            return redirect()->route('suppliers.index')->with('error', trans('general.bulk_delete_associations.assoc_maintenances', [
                'asset_maintenances_count' => $supplier->asset_maintenances_count, 'item' => trans('general.supplier')
            ]));
        } catch (ItemStillHasLicenses $e) {
            return redirect()->route('suppliers.index')->with('error', trans('general.bulk_delete_associations.assoc_licenses', [
                'licenses_count' => (int) $supplier->licenses_count, 'item' => trans('general.supplier')
            ]));
        } catch (ItemStillHasAccessories $e) {
            return redirect()->route('suppliers.index')->with('error', trans('general.bulk_delete_associations.assoc_accessories', [
                'accessories_count' => (int) $supplier->accessories_count, 'item' => trans('general.supplier')
            ]));
        } catch (ItemStillHasConsumables $e) {
            return redirect()->route('suppliers.index')->with('error', trans('general.bulk_delete_associations.assoc_consumables', [
                'consumables_count' => (int) $supplier->consumables_count, 'item' => trans('general.supplier')
            ]));
        } catch (ItemStillHasComponents $e) {
            return redirect()->route('suppliers.index')->with('error', trans('general.bulk_delete_associations.assoc_components', [
                'components_count' => (int) $supplier->components_count, 'item' => trans('general.supplier')
            ]));
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('suppliers.index')->with('error', trans('admin/suppliers/message.delete.error'));
        }

        return redirect()->route('suppliers.index')->with('success', trans('admin/suppliers/message.delete.success'));
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
