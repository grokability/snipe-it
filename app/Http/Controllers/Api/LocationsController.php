<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\AccessoriesTransformer;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\LocationsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', Location::class);
        $allowed_columns = [
            'accessories_count',
            'address',
            'address2',
            'assets_count',
            'assigned_assets_count',
            'rtd_assets_count',
            'accessories_count',
            'assigned_accessories_count',
            'components_count',
            'consumables_count',
            'users_count',
            'children_count',
            'city',
            'country',
            'created_at',
            'currency',
            'id',
            'image',
            'ldap_ou',
            'company_id',
            'manager_id',
            'name',
            'rtd_assets_count',
            'state',
            'updated_at',
            'zip',
            'notes',
            ];

        $locations = Location::with('parent', 'manager', 'children')->select([
            'locations.id',
            'locations.name',
            'locations.address',
            'locations.address2',
            'locations.city',
            'locations.state',
            'locations.zip',
            'locations.phone',
            'locations.fax',
            'locations.country',
            'locations.parent_id',
            'locations.manager_id',
            'locations.created_at',
            'locations.updated_at',
            'locations.image',
            'locations.ldap_ou',
            'locations.currency',
            'locations.company_id',
            'locations.notes',
            'locations.created_by',
            'locations.deleted_at',
        ])
            ->withCount('assignedAssets as assigned_assets_count')
            ->withCount('assets as assets_count')
            ->withCount('assignedAccessories as assigned_accessories_count')
            ->withCount('accessories as accessories_count')
            ->withCount('rtd_assets as rtd_assets_count')
            ->withCount('children as children_count')
            ->withCount('users as users_count')
            ->withCount('consumables as consumables_count')
            ->withCount('components as components_count')
            ->with('adminuser');

        // Only scope locations if the setting is enabled
        if (Setting::getSettings()->scope_locations_fmcs) {
            $locations = Company::scopeCompanyables($locations);
        }

        if ($request->filled('search')) {
            $locations = $locations->TextSearch($request->input('search'));
        }

        if ($request->filled('name')) {
            $locations->where('locations.name', '=', $request->input('name'));
        }

        if ($request->filled('address')) {
            $locations->where('locations.address', '=', $request->input('address'));
        }

        if ($request->filled('address2')) {
            $locations->where('locations.address2', '=', $request->input('address2'));
        }

        if ($request->filled('city')) {
            $locations->where('locations.city', '=', $request->input('city'));
        }

        if ($request->filled('zip')) {
            $locations->where('locations.zip', '=', $request->input('zip'));
        }

        if ($request->filled('country')) {
            $locations->where('locations.country', '=', $request->input('country'));
        }

        if ($request->filled('manager_id')) {
            $locations->where('locations.manager_id', '=', $request->input('manager_id'));
        }

        if ($request->filled('company_id')) {
            $locations->where('locations.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('parent_id')) {
            $locations->where('locations.parent_id', '=', $request->input('parent_id'));
        }

        if ($request->input('status') == 'deleted') {
            $locations->onlyTrashed();
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $locations->count()) ? $locations->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';



        switch ($request->input('sort')) {
            case 'parent':
                $locations->OrderParent($order);
                break;
            case 'manager':
                $locations->OrderManager($order);
                break;
            case 'company':
                $locations->OrderCompany($order);
                break;
            default:
                $locations->orderBy($sort, $order);
                break;
        }


        $total = $locations->count();
        $locations = $locations->skip($offset)->take($limit)->get();

        return (new LocationsTransformer)->transformLocations($locations, $total);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     */
    public function store(ImageUploadRequest $request) : JsonResponse
    {
        $this->authorize('create', Location::class);
        $location = new Location;
        $location->fill($request->all());
        $location = $request->handleImages($location);

        // Only scope location if the setting is enabled
        if (Setting::getSettings()->scope_locations_fmcs) {
            $location->company_id = Company::getIdForCurrentUser($request->get('company_id'));
            // check if parent is set and has a different company
            if ($location->parent_id && Location::find($location->parent_id)->company_id != $location->company_id) {
                response()->json(Helper::formatStandardApiResponse('error', null, 'different company than parent'));
            }    
        }

        if ($location->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new LocationsTransformer)->transformLocation($location), trans('admin/locations/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $location->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function show($id) : JsonResponse | array
    {
        $this->authorize('view', Location::class);
        $location = Location::with('parent', 'manager', 'children', 'company')
            ->select([
                'locations.id',
                'locations.name',
                'locations.address',
                'locations.address2',
                'locations.city',
                'locations.state',
                'locations.zip',
                'locations.country',
                'locations.parent_id',
                'locations.manager_id',
                'locations.created_at',
                'locations.updated_at',
                'locations.image',
                'locations.currency',
                'locations.company_id',
                'locations.notes',
            ])
            ->withCount('assignedAssets as assigned_assets_count')
            ->withCount('assets as assets_count')
            ->withCount('assignedAccessories as assigned_accessories_count')
            ->withCount('accessories as accessories_count')
            ->withCount('rtd_assets as rtd_assets_count')
            ->withCount('children as children_count')
            ->withCount('users as users_count')
            ->withCount('consumables as consumables_count')
            ->withCount('components as components_count')
            ->findOrFail($id);

        return (new LocationsTransformer)->transformLocation($location);
    }


    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     * @param  int  $id
     */
    public function update(ImageUploadRequest $request, $id) : JsonResponse
    {
        $this->authorize('update', Location::class);
        $location = Location::findOrFail($id);

        $location->fill($request->all());
        $location = $request->handleImages($location);

        if ($request->filled('company_id')) {
            // Only scope location if the setting is enabled
            if (Setting::getSettings()->scope_locations_fmcs) {
                $location->company_id = Company::getIdForCurrentUser($request->get('company_id'));
                // check if there are related objects with different company
                if (Helper::test_locations_fmcs(false, $id, $location->company_id)) {
                    return response()->json(Helper::formatStandardApiResponse('error', null, 'error scoped locations'));
                }                
            } else {
                $location->company_id = $request->get('company_id');
            }
        }

        if ($location->isValid()) {

            $location->save();
            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new LocationsTransformer)->transformLocation($location),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $location->getErrors()));
    }


    public function assets(Request $request, Location $location) : JsonResponse | array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $location);
        $assets = Asset::where('location_id', '=', $location->id)->with('model', 'model.category', 'assetstatus', 'location', 'company', 'defaultLoc');
        $assets = $assets->get();
        return (new AssetsTransformer)->transformAssets($assets, $assets->count(), $request);
    }

    public function assignedAssets(Request $request, Location $location) : JsonResponse | array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $location);
        $assets = Asset::where('assigned_to', '=', $location->id)->where('assigned_type', '=', Location::class)->with('model', 'model.category', 'assetstatus', 'location', 'company', 'defaultLoc');
        $assets = $assets->get();
        return (new AssetsTransformer)->transformAssets($assets, $assets->count(), $request);
    }

    public function assignedAccessories(Request $request, Location $location) : JsonResponse | array
    {
        $this->authorize('view', Accessory::class);
        $this->authorize('view', $location);
        $accessory_checkouts = AccessoryCheckout::LocationAssigned()->where('assigned_to', $location->id)->with('adminuser')->with('accessories');

        $offset = ($request->input('offset') > $accessory_checkouts->count()) ? $accessory_checkouts->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $total = $accessory_checkouts->count();
        $accessory_checkouts = $accessory_checkouts->skip($offset)->take($limit)->get();
        return (new LocationsTransformer)->transformCheckedoutAccessories($accessory_checkouts, $total);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function destroy($id) : JsonResponse
    {
        $this->authorize('delete', Location::class);
        $location = Location::withCount('assignedAssets as assigned_assets_count')
            ->withCount('assignedAssets as assigned_assets_count')
            ->withCount('assets as assets_count')
            ->withCount('assignedAccessories as assigned_accessories_count')
            ->withCount('accessories as accessories_count')
            ->withCount('rtd_assets as rtd_assets_count')
            ->withCount('children as children_count')
            ->withCount('users as users_count')
            ->withCount('consumables as consumables_count')
            ->withCount('components as components_count')
            ->findOrFail($id);

        if (! $location->isDeletable()) {
            return response()
                    ->json(Helper::formatStandardApiResponse('error', null, trans('admin/locations/message.assoc_users')));
        }
        $this->authorize('delete', $location);
        $location->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/locations/message.delete.success')));
    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * This is handled slightly differently as of ~4.7.8-pre, as
     * we have to do some recursive magic to get the hierarchy to display
     * properly when looking at the parent/child relationship in the
     * rich menus.
     *
     * This means we can't use the normal pagination that we use elsewhere
     * in our selectlists, since we have to get the full set before we can
     * determine which location is parent/child/grandchild, etc.
     *
     * This also means that hierarchy display gets a little funky when people
     * use the Select2 search functionality, but there's not much we can do about
     * that right now.
     *
     * As a result, instead of paginating as part of the query, we have to grab
     * the entire data set, and then invoke a paginator manually and pass that
     * through to the SelectListTransformer.
     *
     * Many thanks to @uberbrady for the help getting this working better.
     * Recursion still sucks, but I guess he doesn't have to get in the
     * sea... this time.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     */
    public function selectlist(Request $request) : array
    {
        // If a user is in the process of editing their profile, as determined by the referrer,
        // then we check that they have permission to edit their own location.
        // Otherwise, we do our normal check that they can view select lists.
        $request->headers->get('referer') === route('profile')
            ? $this->authorize('self.edit_location')
            : $this->authorize('view.selectlists');

        $locations = Location::select([
            'locations.id',
            'locations.name',
            'locations.parent_id',
            'locations.image',
        ]);

        // Only scope locations if the setting is enabled
        if (Setting::getSettings()->scope_locations_fmcs) {
            $locations = Company::scopeCompanyables($locations);
        }

        $page = 1;
        if ($request->filled('page')) {
            $page = $request->input('page');
        }

        if ($request->filled('search')) {
            $locations = $locations->where('locations.name', 'LIKE', '%'.$request->input('search').'%');
        }

        $locations = $locations->orderBy('name', 'ASC')->get();

        $locations_with_children = [];

        foreach ($locations as $location) {
            if (! array_key_exists($location->parent_id, $locations_with_children)) {
                $locations_with_children[$location->parent_id] = [];
            }
            $locations_with_children[$location->parent_id][] = $location;
        }

        if ($request->filled('search')) {
            $locations_formatted = $locations;
        } else {
            $location_options = Location::indenter($locations_with_children);
            $locations_formatted = new Collection($location_options);
        }

        $paginated_results = new LengthAwarePaginator($locations_formatted->forPage($page, 500), $locations_formatted->count(), 500, $page, []);

        return (new SelectlistTransformer)->transformSelectlist($paginated_results);
    }
}
