<?php

namespace App\Http\Controllers;

use App\Actions\Manufacturers\DeleteManufacturerAction;
use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasChildren;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This controller handles all actions related to Manufacturers for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ManufacturersController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the manufacturers listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see Api\ManufacturersController::index() method that generates the JSON response
     * @since [v1.0]
     */
    public function index() : View
    {
        $this->authorize('index', Manufacturer::class);
        $manufacturer_count = Manufacturer::withTrashed()->count();
        return view('manufacturers/index')->with('manufacturer_count', $manufacturer_count);
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the manufacturers listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see Api\ManufacturersController::index() method that generates the JSON response
     * @since [v1.0]
     */
    public function seed() : RedirectResponse
    {
        $this->authorize('index', Manufacturer::class);

        $manufacturers_count = Manufacturer::withTrashed()->count();

        if ($manufacturers_count == 0) {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ManufacturerSeeder', '--force' => true]);
            return redirect()->route('manufacturers.index')->with('success', trans('general.seeding.manufacturers.success'));
        }

        return redirect()->route('manufacturers.index')->with('error', trans_choice('general.seeding.manufacturers.error', ['count' => $manufacturers_count]));
    }

    /**
     * Returns a view that displays a form to create a new manufacturer.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ManufacturersController::store()
     * @since [v1.0]
     */
    public function create() : View
    {
        $this->authorize('create', Manufacturer::class);

        return view('manufacturers/edit')->with('item', new Manufacturer);
    }

    /**
     * Validates and stores the data for a new manufacturer.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ManufacturersController::create()
     * @since [v1.0]
     * @param ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('create', Manufacturer::class);
        $manufacturer = new Manufacturer;
        $manufacturer->name = $request->input('name');
        $manufacturer->created_by = auth()->id();
        $manufacturer->url = $request->input('url');
        $manufacturer->support_url = $request->input('support_url');
        $manufacturer->warranty_lookup_url = $request->input('warranty_lookup_url');
        $manufacturer->support_phone = $request->input('support_phone');
        $manufacturer->support_email = $request->input('support_email');
        $manufacturer->notes = $request->input('notes');
        $manufacturer = $request->handleImages($manufacturer);

        if ($manufacturer->save()) {
            return redirect()->route('manufacturers.index')->with('success', trans('admin/manufacturers/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($manufacturer->getErrors());
    }

    /**
     * Returns a view that displays a form to edit a manufacturer.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ManufacturersController::update()
     * @param int $manufacturerId
     * @since [v1.0]
     */
    public function edit(Manufacturer $manufacturer) : View | RedirectResponse
    {
        $this->authorize('update', Manufacturer::class);
        return view('manufacturers/edit')->with('item', $manufacturer);
    }

    /**
     * Validates and stores the updated manufacturer data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ManufacturersController::getEdit()
     * @param Request $request
     * @param int $manufacturerId
     * @since [v1.0]
     */
    public function update(ImageUploadRequest $request, Manufacturer $manufacturer) : RedirectResponse
    {
        $this->authorize('update', Manufacturer::class);

        $manufacturer->name = $request->input('name');
        $manufacturer->url = $request->input('url');
        $manufacturer->support_url = $request->input('support_url');
        $manufacturer->warranty_lookup_url = $request->input('warranty_lookup_url');
        $manufacturer->support_phone = $request->input('support_phone');
        $manufacturer->support_email = $request->input('support_email');
        $manufacturer->notes = $request->input('notes');

        // Set the model's image property to null if the image is being deleted
        if ($request->input('image_delete') == 1) {
            $manufacturer->image = null;
        }

        $manufacturer = $request->handleImages($manufacturer);

        if ($manufacturer->save()) {
            return redirect()->route('manufacturers.index')->with('success', trans('admin/manufacturers/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($manufacturer->getErrors());
    }

    /**
     * Deletes a manufacturer.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $manufacturerId
     * @since [v1.0]
     */
    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        $this->authorize('delete', $manufacturer);
        try {
            DeleteManufacturerAction::run($manufacturer);
        } catch (ItemStillHasChildren $e) {
            return redirect()->route('manufacturers.index')->with('error', trans('general.bulk_delete_associations.general_assoc_warning', ['item' => trans('general.manufacturer')]));
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('manufacturers.index')->with('error', trans('general.something_went_wrong'));
        }

        return redirect()->route('manufacturers.index')->with('success', trans('admin/manufacturers/message.delete.success'));
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the manufacturers detail listing, which is generated via API.
     * This data contains a listing of all assets that belong to that manufacturer.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $manufacturerId
     * @since [v1.0]
     */
    public function show(Manufacturer $manufacturer) : View | RedirectResponse
    {
        $this->authorize('view', Manufacturer::class);
        return view('manufacturers/view', compact('manufacturer'));
    }

    /**
     * Restore a given Manufacturer (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.1.15]
     * @param int $manufacturers_id
     */
    public function restore($id) : RedirectResponse
    {
        $this->authorize('delete', Manufacturer::class);

        if ($manufacturer = Manufacturer::withTrashed()->find($id)) {

            if ($manufacturer->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.manufacturer')]));
            }

            if ($manufacturer->restore()) {
                $logaction = new Actionlog();
                $logaction->item_type = Manufacturer::class;
                $logaction->item_id = $manufacturer->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');

                // Redirect them to the deleted page if there are more, otherwise the section index
                $deleted_manufacturers = Manufacturer::onlyTrashed()->count();
                if ($deleted_manufacturers > 0) {
                    return redirect()->back()->with('success', trans('admin/manufacturers/message.success.restored'));
                }
                return redirect()->route('manufacturers.index')->with('success', trans('admin/manufacturers/message.restore.success'));
            }

            // Check validation to make sure we're not restoring an asset with the same asset tag (or unique attribute) as an existing asset
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.manufacturer'), 'error' => $manufacturer->getErrors()->first()]));
        }

        return redirect()->route('manufacturers.index')->with('error', trans('admin/manufacturers/message.does_not_exist'));

    }
}
