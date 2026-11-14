<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\ActionlogsTransformer;
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
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class LocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @return Response
     */
    public function index(FilterRequest $request): JsonResponse|array
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
            'fax',
            'name',
            'phone',
            'rtd_assets_count',
            'state',
            'updated_at',
            'zip',
            'tag_color',
            'notes',
        ];

        $locations = Location::with([
            'parent',
            'children',
            'manager' => fn ($q) => $q->withCount([
                'assets as assets_count',
                'accessories as accessories_count',
                'licenses as licenses_count',
                'consumables as consumables_count',
                'managesUsers as manages_users_count',
                'managedLocations as manages_locations_count',
            ]),
        ])->select([
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
            'locations.tag_color',
            'locations.tag_color',
            'locations.notes',
            'locations.created_by',
            'locations.deleted_at',
        ])
            // Asset counts thread through AssetsForShow so the API totals
            // agree with the tab counts in resources/views/locations/view.blade.php,
            // which use ->assets()->AssetsForShow() etc. Without the scope,
            // rtd_assets_count in particular ignores the show_archived_in_list
            // setting because the rtd_assets() relation has no built-in status
            // filter. See #17565.
            ->withCount(['assignedAssets as assigned_assets_count' => fn ($q) => $q->AssetsForShow()])
            ->withCount(['assets as assets_count' => fn ($q) => $q->AssetsForShow()])
            ->withCount('assignedAccessories as assigned_accessories_count')
            ->withCount('accessories as accessories_count')
            ->withCount(['rtd_assets as rtd_assets_count' => fn ($q) => $q->AssetsForShow()])
            ->withCount('children as children_count')
            ->withCount('users as users_count')
            ->withCount('consumables as consumables_count')
            ->withCount('components as components_count')
            ->with('adminuser');

        // scope_locations_fmcs is required for location-level company scoping (locations may not
        // have company_id assigned unless the compatibility check has been completed in Settings).
        // Without it, locations are visible to all authenticated users regardless of FMCS state.
        if (Setting::getSettings()->scope_locations_fmcs) {
            $locations = Company::scopeCompanyables($locations);
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $locations->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
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

        if ($request->filled('tag_color')) {
            $locations->where('tag_color', '=', $request->input('locations.tag_color'));
        }

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
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $locations = $locations->skip($offset)->take($limit)->get();

        return (new LocationsTransformer)->transformLocations($locations, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function store(ImageUploadRequest $request): JsonResponse
    {
        $this->authorize('create', Location::class);
        $location = new Location;
        $location->fill($request->all());
        $location = $request->handleImages($location);

        if (Setting::getSettings()->scope_locations_fmcs) {
            $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));
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
                return response()->json(Helper::formatStandardApiResponse('error', null, Location::parentCompanyMismatchMessage($parent, $location->company?->name)));
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
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse|array
    {
        $this->authorize('view', Location::class);
        $location = Location::with([
            'parent',
            'children',
            'company',
            'manager' => fn ($q) => $q->withCount([
                'assets as assets_count',
                'accessories as accessories_count',
                'licenses as licenses_count',
                'consumables as consumables_count',
                'managesUsers as manages_users_count',
                'managedLocations as manages_locations_count',
            ]),
        ])
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
                'locations.tag_color',
            ])
            // See index() for why the asset counts thread AssetsForShow. #17565.
            ->withCount(['assignedAssets as assigned_assets_count' => fn ($q) => $q->AssetsForShow()])
            ->withCount(['assets as assets_count' => fn ($q) => $q->AssetsForShow()])
            ->withCount('assignedAccessories as assigned_accessories_count')
            ->withCount('accessories as accessories_count')
            ->withCount(['rtd_assets as rtd_assets_count' => fn ($q) => $q->AssetsForShow()])
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
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function update(ImageUploadRequest $request, $id): JsonResponse
    {
        $this->authorize('update', Location::class);
        $location = Location::findOrFail($id);

        $location->fill($request->all());
        $location = $request->handleImages($location);

        if ($request->filled('company_id')) {
            if (Setting::getSettings()->scope_locations_fmcs) {
                $location->company_id = Company::getIdForCurrentUser($request->input('company_id'));
                // check if there are related objects with different company
                if ($mismatched = Helper::test_locations_fmcs(false, $id, $location->company_id)) {
                    $first = $mismatched[0];

                    return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_location_scoped_items', [
                        'item_type' => trans('general.'.strtolower($first[0])),
                        'item_name' => $first[2],
                        'item_company' => $first[5] ?? trans('general.unassigned'),
                    ])));
                }
            } else {
                $location->company_id = $request->input('company_id');
            }
        }

        // Parent company check applies whenever FMCS is on, independent of scope_locations_fmcs.
        // Runs outside the company_id gate so a parent_id-only update is also validated.
        if (Setting::getSettings()->full_multiple_companies_support) {
            // withoutGlobalScopes: Location::CompanyableScope would filter a
            // cross-tenant parent out for a scoped non-superuser, so find()
            // would return null and the null check below would short-circuit
            // the reject branch, letting the parent_id save through. See
            // ValidationServiceProvider::fmcs_location for the sibling fix.
            $parent = $location->parent_id ? Location::withoutGlobalScopes()->find($location->parent_id) : null;
            if ($parent && $parent->company_id != $location->company_id) {
                return response()->json(Helper::formatStandardApiResponse('error', null, Location::parentCompanyMismatchMessage($parent, $location->company?->name)));
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

    public function assets(Request $request, Location $location): JsonResponse|array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $location);
        $assets = Asset::where('location_id', '=', $location->id)->with('model', 'model.category', 'status', 'location', 'company', 'defaultLoc');
        $assets = $assets->get();

        return (new AssetsTransformer)->transformAssets($assets, $assets->count(), $request);
    }

    public function assignedAssets(Request $request, Location $location): JsonResponse|array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $location);
        $assets = Asset::where('assigned_to', '=', $location->id)->where('assigned_type', '=', Location::class)->with('model', 'model.category', 'status', 'location', 'company', 'defaultLoc');
        $assets = $assets->get();

        return (new AssetsTransformer)->transformAssets($assets, $assets->count(), $request);
    }

    public function assignedAccessories(Request $request, Location $location): JsonResponse|array
    {
        $this->authorize('view', Accessory::class);
        $this->authorize('view', $location);
        $accessory_checkouts = AccessoryCheckout::LocationAssigned()->where('assigned_to', $location->id)->with('adminuser')->with('accessories');

        $total = $accessory_checkouts->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $accessory_checkouts = $accessory_checkouts->skip($offset)->take($limit)->get();

        return (new LocationsTransformer)->transformCheckedoutAccessories($accessory_checkouts, $total);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
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
     *
     * @since [v4.0.16]
     * @see SelectlistTransformer
     */
    public function selectlist(Request $request): array
    {

        abort_unless(
            auth()->user()?->canAny(['view.selectlists', 'self.edit_location']),
            403
        );

        $locations = Location::select([
            'locations.id',
            'locations.name',
            'locations.parent_id',
            'locations.image',
            'locations.tag_color',
        ]);

        if ($request->filled('search')) {
            $locations = $locations->where('locations.name', 'LIKE', '%'.$request->input('search').'%');
        }

        if ($request->filled('excludeId')) {
            $locations->where('locations.id', '!=', (int) $request->input('excludeId'));
        }

        // excludeIds is the batch variant used by the bulk-edit form's
        // parent picker so a location can't be picked as its own new
        // parent (or the parent of one of its siblings in the same
        // batch, which would collapse the hierarchy in confusing ways).
        // Accepts a comma-separated list of ints because js-data-ajax
        // forwards data-exclude-ids as-is.
        if ($request->filled('excludeIds')) {
            $rawExclude = $request->input('excludeIds');
            $excludeIds = array_filter(array_map('intval', is_array($rawExclude) ? $rawExclude : explode(',', (string) $rawExclude)));
            if ($excludeIds !== []) {
                $locations->whereNotIn('locations.id', $excludeIds);
            }
        }

        if ((Setting::getSettings()->full_multiple_companies_support == '1') && $request->filled('companyId')) {
            $companyId = (int) $request->input('companyId');

            if (Setting::getSettings()->null_company_is_floater) {
                // Floater mode: include null-company (floater) locations too,
                // matching the "items from any company can be checked out
                // to targets with no company assignment" policy. Without
                // this the strict equality below hid all floaters from the
                // checkout dropdown while the server-side canCheckoutTo
                // still permitted the checkout (#19394).
                $locations->where(function ($q) use ($companyId) {
                    $q->where('locations.company_id', '=', $companyId)
                        ->orWhereNull('locations.company_id');
                });
            } else {
                $locations->where('locations.company_id', '=', $companyId);
            }
        }

        $locations = $locations->orderBy('name', 'ASC')->get();

        $locations_with_children = [];

        // Use 0 (not null) for the top-level bucket — null array offsets are
        // deprecated in PHP 8.4 and Location::indenter expects an int key.
        foreach ($locations as $location) {
            $parentKey = (int) $location->parent_id;
            if (! array_key_exists($parentKey, $locations_with_children)) {
                $locations_with_children[$parentKey] = [];
            }
            $locations_with_children[$parentKey][] = $location;
        }

        if ($request->filled('search')) {
            // Search results are cherry-picked out of the tree — no useful
            // indent depth to apply — so just use the plain name. The user
            // is filtering by typed text so context comes from the search
            // term rather than dropdown position (see #19398 for why we
            // stopped inlining the parent chain here too).
            foreach ($locations as $location) {
                $location->use_text = $location->name;
            }
            $locations_formatted = $locations;
        } else {
            $location_options = Location::indenter($locations_with_children);
            $locations_formatted = new Collection($location_options);
        }

        return (new SelectlistTransformer)->transformSelectlist(Helper::paginateCollection($locations_formatted));
    }

    public function history(Request $request, Location $location): JsonResponse|array
    {
        $this->authorize('history', $location);
        $historyQuery = $location->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }
}
