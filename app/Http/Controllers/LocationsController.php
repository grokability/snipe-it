<?php

namespace App\Http\Controllers;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
     *
     * @see LocationsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index(): View
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
     *
     * @see LocationsController::postCreate() method that validates and stores the data
     * @since [v1.0]
     */
    public function create(): View
    {
        $this->authorize('create', Location::class);

        return view('locations/edit')
            ->with('item', new Location);
    }

    /**
     * Validates and stores a new location.
     *
     * @todo Check if a Form Request would work better here.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see LocationsController::getCreate() method that makes the form
     * @since [v1.0]
     */
    public function store(ImageUploadRequest $request): RedirectResponse
    {
        $this->authorize('create', Location::class);

        $location = new Location;
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
        $location->tag_color = $request->input('tag_color');
        $location->notes = $request->input('notes');
        if (Setting::getSettings()->scope_locations_fmcs) {
            $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));
        } else {
            $location->company_id = $request->input('company_id');
        }

        // Parent company check applies whenever FMCS is on, independent of scope_locations_fmcs.
        if (Setting::getSettings()->full_multiple_companies_support) {
            // withoutGlobalScopes: Location::CompanyableScope would filter a
            // cross-tenant parent out for a scoped non-superuser, so find()
            // would return null and the null check below would short-circuit
            // the reject branch, letting the parent_id save through. See
            // ValidationServiceProvider::fmcs_location for the sibling fix.
            $parent = $location->parent_id ? Location::withoutGlobalScopes()->find($location->parent_id) : null;
            if ($parent && $parent->company_id != $location->company_id) {
                return redirect()->back()->withInput()->with('error', Location::parentCompanyMismatchMessage($parent, $location->company?->name));
            }
        }

        if ($request->has('use_cloned_image')) {
            // Authorize the source first
            $cloned = Location::findOrFail($request->input('clone_image_from_id'));
            $this->authorize('clone', $cloned);

            $source_image = $cloned->image;
            if ($source_image) {
                $new_image_name = 'clone-'.date('U').'-'.$source_image;
                Storage::disk('public')->copy(
                    FileStorage::Locations->publicPath().$source_image,
                    FileStorage::Locations->publicPath().$new_image_name,
                );
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
     *
     * @see LocationsController::postCreate() method that validates and stores
     * @since [v1.0]
     */
    public function edit(Location $location): View|RedirectResponse
    {
        $this->authorize('update', Location::class);

        return view('locations/edit')->with('item', $location);
    }

    /**
     * Validates and stores updated location data from edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see LocationsController::getEdit() method that makes the form view
     * @since [v1.0]
     */
    public function update(ImageUploadRequest $request, Location $location): RedirectResponse
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
        $location->tag_color = $request->input('tag_color');
        $location->notes = $request->input('notes');

        if (Setting::getSettings()->scope_locations_fmcs) {
            $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));
            // check if there are related objects with different company
            if ($mismatched = Helper::test_locations_fmcs(false, $location->id, $location->company_id)) {
                $first = $mismatched[0];

                return redirect()->back()->withInput()->with('error', trans('general.error_location_scoped_items', [
                    'item_type' => trans('general.'.strtolower($first[0])),
                    'item_name' => $first[2],
                    'item_company' => $first[5] ?? trans('general.unassigned'),
                ]));
            }
        } else {
            $location->company_id = $request->input('company_id');
        }

        // Parent company check applies whenever FMCS is on, independent of scope_locations_fmcs.
        if (Setting::getSettings()->full_multiple_companies_support) {
            // withoutGlobalScopes: Location::CompanyableScope would filter a
            // cross-tenant parent out for a scoped non-superuser, so find()
            // would return null and the null check below would short-circuit
            // the reject branch, letting the parent_id save through. See
            // ValidationServiceProvider::fmcs_location for the sibling fix.
            $parent = $location->parent_id ? Location::withoutGlobalScopes()->find($location->parent_id) : null;
            if ($parent && $parent->company_id != $location->company_id) {
                return redirect()->back()->withInput()->with('error', Location::parentCompanyMismatchMessage($parent, $location->company?->name));
            }
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
     *
     * @param  int  $locationId
     *
     * @since [v1.0]
     */
    public function destroy($locationId): RedirectResponse
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

        if (! $location) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.does_not_exist'));
        }

        if ($location->isDeletable()) {

            // Note: the image file is deliberately preserved across this
            // soft-delete. Snipe-IT's `snipeit:purge` command permanently
            // removes it later when the row is force-deleted. Keeping
            // the file here means a restored soft-deleted row still has
            // its image.
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
     *
     * @since [v1.0]
     */
    public function show(Location $location): View|RedirectResponse
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

    public function print_assigned($id): View|RedirectResponse
    {
        $this->authorize('view', Location::class);

        if ($location = Location::where('id', $id)->first()) {
            return view('locations/print', $this->printPayload($location, assigned: false));
        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

    public function print_all_assigned($id): View|RedirectResponse
    {
        $this->authorize('view', Location::class);

        if ($location = Location::where('id', $id)->first()) {
            return view('locations/print', $this->printPayload($location, assigned: true));
        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

    /**
     * Build the per-model related collections the print sheet renders.
     * The route only gates on `view` for Location, but the view rendered
     * the assigned users / assets / accessories / consumables /
     * components inline without matching per-model permission checks -
     * so a caller with locations.view but not users.view could read
     * assigned users' identity through the printassigned URL, even
     * though /users/{id} would 403 for them.
     *
     * Substitute an empty Collection for each relation the caller can't
     * view. The existing `@if ($users->count() > 0)` guards in
     * locations/print.blade.php then naturally skip the entire block,
     * matching the per-model @can guards used by the standard
     * locations/view.blade.php page. Instance-context gate checks pass
     * the model class so FMCS scoping still applies.
     */
    private function printPayload(Location $location, bool $assigned): array
    {
        $empty = new Collection;

        return [
            'assigned' => $assigned,
            'location' => $location,
            'assets' => Gate::allows('view', Asset::class) ? $location->assets : $empty,
            'assignedAssets' => Gate::allows('view', Asset::class) ? $location->assignedAssets : $empty,
            'accessories' => Gate::allows('view', Accessory::class) ? $location->accessories : $empty,
            'assignedAccessories' => Gate::allows('view', Accessory::class) ? $location->assignedAccessories : $empty,
            'users' => Gate::allows('view', User::class) ? $location->users()->with('companies')->get() : $empty,
            'consumables' => Gate::allows('view', Consumable::class) ? $location->consumables : $empty,
            'components' => Gate::allows('view', Component::class) ? $location->components : $empty,
            // Child locations key off the same locations.view permission the
            // outer authorize() already required, so no further gate here.
            'children' => $location->children,
        ];
    }

    /**
     * Returns a view that presents a form to clone a location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $locationId
     *
     * @since [v6.0.14]
     */
    public function getClone($locationId = null): View|RedirectResponse
    {
        // Check if the asset exists
        if (is_null($location_to_clone = Location::find($locationId))) {
            // Redirect to the asset management page
            return redirect()->route('licenses.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }

        $this->authorize('clone', $location_to_clone);

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
     *
     * @since [v1.0]
     */
    public function postRestore(Location $location): RedirectResponse
    {
        $this->authorize('delete', $location);

        if ($location->deleted_at == '') {
            return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.location')]));
        }

        if ($location->restore()) {
            $logaction = new Actionlog;
            $logaction->item_type = Location::class;
            $logaction->item_id = $location->id;
            $logaction->created_at = date('Y-m-d H:i:s');
            $logaction->created_by = auth()->id();
            $logaction->logaction('restore');

            return redirect()->route('locations.index')->with('success', trans('admin/locations/message.restore.success'));
        }

        return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.location'), 'error' => $location->getErrors()->first()]));

    }
}
