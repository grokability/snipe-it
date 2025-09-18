<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
/**
 * This controller handles all actions related to Locations for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class LocationsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index() : View
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('locations/index');
    }

    /**
     * Returns a form view used to create a new location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::postCreate() method that validates and stores the data
     * @since [v1.0]
     */
    public function create() : View
    {
        $this->authorize('create', Location::class);

        return view('locations/edit')
            ->with('item', new Location);
    }

    /**
     * Validates and stores a new location.
     *
     * @todo Check if a Form Request would work better here.
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getCreate() method that makes the form
     * @since [v1.0]
     * @param ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('create', Location::class);
        
        $location = new Location();
        $location->name = $request->input('name');
        $location->parent_id = $request->input('parent_id', null);
        $location->currency = $request->input('currency', '$');
        $location->address = $request->input('address');
        $location->address2 = $request->input('address2');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->country = $request->input('country');
        $location->zip = $request->input('zip');
        $location->ldap_ou = $request->input('ldap_ou');
        $location->manager_id = $request->input('manager_id');
        $location->created_by = auth()->id();
        $location->phone = request('phone');
        $location->fax = request('fax');
        $location->notes = $request->input('notes');
        $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        // Only scope the location if the setting is enabled
        if (Setting::getSettings()->scope_locations_fmcs) {
            $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));
            // check if parent is set and has a different company
            if ($location->parent_id && Location::find($location->parent_id)->company_id != $location->company_id) {
                return redirect()->back()->withInput()->withInput()->with('error', 'different company than parent');
            }                
        } else {
            $location->company_id = $request->input('company_id');
        }

        if ($request->has('use_cloned_image')) {
            $cloned_model_img = Location::select('image')->find($request->input('clone_image_from_id'));
            if ($cloned_model_img) {
                $new_image_name = 'clone-'.date('U').'-'.$cloned_model_img->image;
                $new_image = 'locations/'.$new_image_name;
                Storage::disk('public')->copy('locations/'.$cloned_model_img->image, $new_image);
                $location->image = $new_image_name;
            }

        } else {
            $location = $request->handleImages($location);
        }

        if ($location->save()) {
            return redirect()->route('locations.index')->with('success', trans('admin/locations/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($location->getErrors());
    }

    /**
     * Makes a form view to edit location information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::postCreate() method that validates and stores
     * @param int $locationId
     * @since [v1.0]
     */
    public function edit(Location $location) : View | RedirectResponse
    {
        $this->authorize('update', Location::class);
        return view('locations/edit')->with('item', $location);
    }

    /**
     * Validates and stores updated location data from edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getEdit() method that makes the form view
     * @param ImageUploadRequest $request
     * @param int $locationId
     * @since [v1.0]
     */
    public function update(ImageUploadRequest $request, Location $location) : RedirectResponse
    {
        $this->authorize('update', Location::class);

        $location->name = $request->input('name');
        $location->parent_id = $request->input('parent_id', null);
        $location->currency = $request->input('currency', '$');
        $location->address = $request->input('address');
        $location->address2 = $request->input('address2');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->country = $request->input('country');
        $location->zip = $request->input('zip');
        $location->phone = request('phone');
        $location->fax = request('fax');
        $location->ldap_ou = $request->input('ldap_ou');
        $location->manager_id = $request->input('manager_id');
        $location->notes = $request->input('notes');

        // Only scope the location if the setting is enabled
        if (Setting::getSettings()->scope_locations_fmcs) {
            $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));
            // check if there are related objects with different company
            if (Helper::test_locations_fmcs(false, $location->id, $location->company_id)) {
                return redirect()->back()->withInput()->withInput()->with('error', 'error scoped locations');
            }            
        } else {
            $location->company_id = $request->input('company_id');
        }

        $location = $request->handleImages($location);

        if ($location->save()) {
            return redirect()->route('locations.index')->with('success', trans('admin/locations/message.update.success'));
        }

        return redirect()->back()->withInput()->withInput()->withErrors($location->getErrors());
    }

    /**
     * Validates and deletes selected location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $locationId
     * @since [v1.0]
     */
    public function destroy($locationId) : RedirectResponse
    {
        $this->authorize('delete', Location::class);

        $location = Location::withCount('assignedAssets as assigned_assets_count')
        ->withCount('assets as assets_count')
        ->withCount('assignedAccessories as assigned_accessories_count')
        ->withCount('accessories as accessories_count')
        ->withCount('rtd_assets as rtd_assets_count')
        ->withCount('children as children_count')
        ->withCount('users as users_count')
        ->withCount('consumables as consumables_count')
        ->withCount('components as components_count')
        ->find($locationId);

        if (!$location) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.does_not_exist'));
        }

        if ($location->isDeletable()) {

            if ($location->image) {
                try {
                    Storage::disk('public')->delete('locations/'.$location->image);
                } catch (\Exception $e) {
                    Log::error($e);
                }
            }
            $location->delete();
            return redirect()->to(route('locations.index'))->with('success', trans('admin/locations/message.delete.success'));
        } else {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.assoc_users'));
        }

    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $id
     * @since [v1.0]
     */
    public function show(Location $location) : View | RedirectResponse
    {
        $this->authorize('view', Location::class);

        $location = Location::withCount('assignedAssets as assigned_assets_count')
            ->withCount('assets as assets_count')
            ->withCount('rtd_assets as rtd_assets_count')
            ->withCount('children as children_count')
            ->withCount('users as users_count')
            ->withTrashed()
            ->find($location->id);

        if (isset($location->id)) {
            return view('locations/view', compact('location'));
        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

    public function print_assigned($id) : View | RedirectResponse
    {
        $this->authorize('view', Location::class);

        if ($location = Location::where('id', $id)->first()) {
            return view('locations/print')
                ->with('assigned', false)
                ->with('assets', $location->assets)
                ->with('assignedAssets', $location->assignedAssets)
                ->with('accessories', $location->accessories)
                ->with('assignedAccessories', $location->assignedAccessories)
                ->with('users',$location->users)
                ->with('location', $location)
                ->with('consumables', $location->consumables)
                ->with('components', $location->components)
                ->with('children', $location->children);
        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

    public function print_all_assigned($id) : View | RedirectResponse
    {
        $this->authorize('view', Location::class);
        if ($location = Location::where('id', $id)->first()) {
            return view('locations/print')
                ->with('assigned', true)
                ->with('assets', $location->assets)
                ->with('assignedAssets', $location->assignedAssets)
                ->with('accessories', $location->accessories)
                ->with('assignedAccessories', $location->assignedAccessories)
                ->with('users',$location->users)
                ->with('location', $location)
                ->with('consumables', $location->consumables)
                ->with('components', $location->components)
                ->with('children', $location->children);
        }
        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


    /**
     * Returns a view that presents a form to clone a location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $locationId
     * @since [v6.0.14]
     */
    public function getClone($locationId = null) : View | RedirectResponse
    {
        $this->authorize('create', Location::class);

        // Check if the asset exists
        if (is_null($location_to_clone = Location::find($locationId))) {
            // Redirect to the asset management page
            return redirect()->route('licenses.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }

        $location = clone $location_to_clone;

        // unset these values
        $location->id = null;

        return view('locations/edit')
            ->with('cloned_model', $location_to_clone)
            ->with('item', $location);
    }


    /**
     * Restore a given Asset Model (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $id
     */
    public function postRestore($id) : RedirectResponse
    {
        $this->authorize('create', Location::class);

        if ($location = Location::withTrashed()->find($id)) {

            if ($location->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.location')]));
            }

            if ($location->restore()) {
                $logaction = new Actionlog();
                $logaction->item_type = Location::class;
                $logaction->item_id = $location->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');

                return redirect()->route('locations.index')->with('success', trans('admin/locations/message.restore.success'));
            }

            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.location'), 'error' => $location->getErrors()->first()]));
        }

        return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));

    }

    /**
     * Returns a view that allows the user to bulk delete locations
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.3.1]
     */
    public function postBulkDelete(Request $request) : View | RedirectResponse
    {
        $this->authorize('update', Location::class);

        $locations_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($locations_raw_array)) && (count($locations_raw_array) > 0)) {
            $locations = Location::whereIn('id', $locations_raw_array)
                ->withCount('assignedAssets as assigned_assets_count')
                ->withCount('assets as assets_count')
                ->withCount('assignedAccessories as assigned_accessories_count')
                ->withCount('accessories as accessories_count')
                ->withCount('rtd_assets as rtd_assets_count')
                ->withCount('children as children_count')
                ->withCount('consumables as consumables_count')
                ->withCount('components as components_count')
                ->withCount('users as users_count')->get();

                $valid_count = 0;
                foreach ($locations as $location) {
                    if ($location->isDeletable()) {
                        $valid_count++;
                    }
                }
                return view('locations/bulk-delete', compact('locations'))->with('valid_count', $valid_count);
        }

        return redirect()->route('models.index')
            ->with('error', 'You must select at least one model to edit.');
    }

    /**
     * Checks that locations can be deleted and deletes them if they can
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.3.1]

     */
    public function postBulkDeleteStore(Request $request) : RedirectResponse
    {
        $this->authorize('delete', Location::class);

        $locations_raw_array = $request->input('ids');

        if ((is_array($locations_raw_array)) && (count($locations_raw_array) > 0)) {
            $locations = Location::whereIn('id', $locations_raw_array)
                ->withCount('assignedAssets as assigned_assets_count')
                ->withCount('assets as assets_count')
                ->withCount('assignedAccessories as assigned_accessories_count')
                ->withCount('accessories as accessories_count')
                ->withCount('rtd_assets as rtd_assets_count')
                ->withCount('children as children_count')
                ->withCount('users as users_count')
                ->withCount('consumables as consumables_count')
                ->withCount('components as components_count')->get();

            $success_count = 0;
            $error_count = 0;

            foreach ($locations as $location) {

                // Can we delete this location?
                if ($location->isDeletable()) {
                    $location->delete();
                    $success_count++;
                } else {
                    $error_count++;
                }
            }

            Log::debug('Success count: '.$success_count);
            Log::debug('Error count: '.$error_count);
            // Complete success
            if ($success_count == count($locations_raw_array)) {
                return redirect()
                    ->route('locations.index')
                    ->with('success', trans_choice('general.bulk.delete.success', $success_count,
                        ['object_type' => trans_choice('general.location_plural', $success_count), 'count' => $success_count]
                    ));
            }

            // Partial success
            if ($error_count > 0) {
                return redirect()
                    ->route('locations.index')
                    ->with('warning', trans('general.bulk.delete.partial',
                        ['success' => $success_count, 'error' => $error_count, 'object_type' => trans('general.locations')]
                    ));
                }
            }


        // Nothing was selected - return to the index
        return redirect()
            ->route('locations.index')
            ->with('error', trans('general.bulk.nothing_selected',
                ['object_type' => trans('general.locations')]
            ));

    }
}
