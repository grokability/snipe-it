<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedIn;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Http\Traits\MigratesLegacyAssetLocations;
use App\Http\Transformers\ComponentsTransformer;
use App\Models\AccessoryCheckout;
use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\LicensesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\License;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\View\Label;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


/**
 * This class controls all actions related to assets for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetsController extends Controller
{
    use MigratesLegacyAssetLocations;

    /**
     * Returns JSON listing of all assets
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v4.0]
     */
    public function index(Request $request, $action = null, $upcoming_status = null) : JsonResponse | array
    {


        // This handles the legacy audit endpoints :(
        if ($action == 'audit') {
            $action = 'audits';
        }
        $filter_non_deprecable_assets = false;

        /**
         * This looks MAD janky (and it is), but the AssetsController@index does a LOT of heavy lifting throughout the 
         * app. This bit here just makes sure that someone without permission to view assets doesn't 
         * end up with priv escalations because they asked for a different endpoint. 
         * 
         * Since we never gave the specification for which transformer to use before, it should default 
         * gracefully to just use the AssetTransformer by default, which shouldn't break anything. 
         * 
         * It was either this mess, or repeating ALL of the searching and sorting and filtering code, 
         * which would have been far worse of a mess. *sad face*  - snipe (Sept 1, 2021)
         */
        if (Route::currentRouteName()=='api.depreciation-report.index') {
            $filter_non_deprecable_assets = true;
            $transformer = 'App\Http\Transformers\DepreciationReportTransformer';
            $this->authorize('reports.view');
        } else {
            $transformer = 'App\Http\Transformers\AssetsTransformer';
            $this->authorize('index', Asset::class);
        }


        $settings = Setting::getSettings();

        $allowed_columns = [
            'id',
            'name',
            'asset_tag',
            'serial',
            'model_number',
            'last_checkout',
            'last_checkin',
            'notes',
            'expected_checkin',
            'order_number',
            'image',
            'assigned_to',
            'created_at',
            'updated_at',
            'purchase_date',
            'purchase_cost',
            'last_audit_date',
            'next_audit_date',
            'warranty_months',
            'checkout_counter',
            'checkin_counter',
            'requests_counter',
            'byod',
            'asset_eol_date',
            'requestable',
            'jobtitle',
            // These are *relationships* so we wouldn't normally include them in this array,
            // since they would normally create a `column not found` error,
            // BUT we account for them in the ordering switch down at the end of this method
            // DO NOT ADD ANYTHING TO THIS LIST WITHOUT CHECKING THE ORDERING SWITCH BELOW!
            'company',
            'model',
            'location',
            'rtd_location',
            'category',
            'status_label',
            'manufacturer',
            'supplier',
            'jobtitle',
            'assigned_to',
            'created_by',

        ];

        $all_custom_fields = CustomField::all(); //used as a 'cache' of custom fields throughout this page load

        foreach ($all_custom_fields as $field) {
            $allowed_columns[] = $field->db_column_name();
        }

        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);

            $filter = array_filter($filter, function ($key) use ($allowed_columns) {
                return in_array($key, $allowed_columns);
            }, ARRAY_FILTER_USE_KEY);

        }

        $assets = Asset::select('assets.*')
            ->with(
                'model',
                'location',
                'assetstatus',
                'company',
                'defaultLoc',
                'assignedTo',
                'adminuser',
                'model.depreciation',
                'model.category',
                'model.manufacturer',
                'model.fieldset',
                'model.depreciation',
                'supplier'
            ); // it might be tempting to add 'assetlog' here, but don't. It blows up update-heavy users.


        if ($filter_non_deprecable_assets) {
            $non_deprecable_models = AssetModel::select('id')->whereNotNull('depreciation_id')->get();
            $assets->InModelList($non_deprecable_models->toArray());
        }



        // These are used by the API to query against specific ID numbers.
        // They are also used by the individual searches on detail pages like
        // locations, etc.

        // Search custom fields by column name
        foreach ($all_custom_fields as $field) {
            if ($request->filled($field->db_column_name()) && $field->db_column_name()) {
                $assets->where($field->db_column_name(), '=', $request->input($field->db_column_name()));
            }
        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $assets->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $assets->TextSearch($request->input('search'));
        }


        /**
         * Handle due and overdue audits and checkin dates
         */
        switch ($action) {
                // Audit (singular) is left over from earlier legacy APIs
            case 'audits':
                switch ($upcoming_status) {
                    case 'due':
                        $assets->DueForAudit($settings);
                        break;
                    case 'overdue':
                        $assets->OverdueForAudit();
                        break;
                    case 'due-or-overdue':
                        $assets->DueOrOverdueForAudit($settings);
                        break;
                }
                break;

            case 'checkins':
                switch ($upcoming_status) {
                    case 'due':
                        $assets->DueForCheckin($settings);
                        break;
                    case 'overdue':
                        $assets->OverdueForCheckin();
                        break;
                    case 'due-or-overdue':
                        $assets->DueOrOverdueForCheckin($settings);
                        break;
                }
                break;
        }

        /**
         * End handling due and overdue audits and checkin dates
         */


        // This is used by the sidenav, mostly

        // We switched from using query scopes here because of a Laravel bug
        // related to fulltext searches on complex queries.
        // I am sad. :(
        switch ($request->input('status')) {
            case 'Deleted':
                $assets->onlyTrashed();
                break;
            case 'Pending':
                $assets->join('status_labels AS status_alias', function ($join) {
                    $join->on('status_alias.id', '=', 'assets.status_id')
                        ->where('status_alias.deployable', '=', 0)
                        ->where('status_alias.pending', '=', 1)
                        ->where('status_alias.archived', '=', 0);
                });
                break;
            case 'RTD':
                $assets->whereNull('assets.assigned_to')
                    ->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'assets.status_id')
                            ->where('status_alias.deployable', '=', 1)
                            ->where('status_alias.pending', '=', 0)
                            ->where('status_alias.archived', '=', 0);
                    });
                break;
            case 'Undeployable':
                $assets->Undeployable();
                break;
            case 'Archived':
                $assets->join('status_labels AS status_alias', function ($join) {
                    $join->on('status_alias.id', '=', 'assets.status_id')
                        ->where('status_alias.deployable', '=', 0)
                        ->where('status_alias.pending', '=', 0)
                        ->where('status_alias.archived', '=', 1);
                });
                break;
            case 'Requestable':
                $assets->where('assets.requestable', '=', 1)
                    ->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'assets.status_id')
                            ->where('status_alias.deployable', '=', 1)
                            ->where('status_alias.pending', '=', 0)
                            ->where('status_alias.archived', '=', 0);
                    });

                break;
            case 'Deployed':
                // more sad, horrible workarounds for laravel bugs when doing full text searches
                $assets->whereNotNull('assets.assigned_to');
                break;
            case 'byod':
                // This is kind of redundant, since we already check for byod=1 above, but this keeps the
                // sidebar nav links a little less chaotic
                $assets->where('assets.byod', '=', '1');
                break;
            default:

                if ((! $request->filled('status_id')) && ($settings->show_archived_in_list != '1')) {
                    // terrible workaround for complex-query Laravel bug in fulltext
                    $assets->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'assets.status_id')
                            ->where('status_alias.archived', '=', 0);
                    });

                    // If there is a status ID, don't take show_archived_in_list into consideration
                } else {
                    $assets->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'assets.status_id');
                    });
                }
        }


        // Leave these under the TextSearch scope, else the fuzziness will override the specific ID (status ID, etc) requested
        if ($request->filled('status_id')) {
            $assets->where('assets.status_id', '=', $request->input('status_id'));
        }

        if ($request->filled('asset_tag')) {
            $assets->where('assets.asset_tag', '=', $request->input('asset_tag'));
        }

        if ($request->filled('serial')) {
            $assets->where('assets.serial', '=', $request->input('serial'));
        }

        if ($request->input('requestable') == 'true') {
            $assets->where('assets.requestable', '=', '1');
        }
        
        if ($request->filled('model_id')) {
            // If model_id is already an array, just use it as-is
            if (is_array($request->input('model_id'))) {
                $assets->InModelList($request->input('model_id'));
            } else {
                // Otherwise, turn it into an array
                $assets->InModelList([$request->input('model_id')]);
            }
        }

        if ($request->filled('category_id')) {
            $assets->InCategory($request->input('category_id'));
        }

        if ($request->filled('location_id')) {
            $assets->where('assets.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('rtd_location_id')) {
            $assets->where('assets.rtd_location_id', '=', $request->input('rtd_location_id'));
        }

        if ($request->filled('supplier_id')) {
            $assets->where('assets.supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('asset_eol_date')) {
            $assets->where('assets.asset_eol_date', '=', $request->input('asset_eol_date'));
        }

        if (($request->filled('assigned_to')) && ($request->filled('assigned_type'))) {
            $assets->where('assets.assigned_to', '=', $request->input('assigned_to'))
                ->where('assets.assigned_type', '=', $request->input('assigned_type'));
        }

        if ($request->filled('company_id')) {
            $assets->where('assets.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $assets->ByManufacturer($request->input('manufacturer_id'));
        }

        if ($request->filled('depreciation_id')) {
            $assets->ByDepreciationId($request->input('depreciation_id'));
        }

        if ($request->filled('byod')) {
            $assets->where('assets.byod', '=', $request->input('byod'));
        }

        if ($request->filled('order_number')) {
            $assets->where('assets.order_number', '=', strval($request->get('order_number')));
        }

        // This is kinda gross, but we need to do this because the Bootstrap Tables
        // API passes custom field ordering as custom_fields.fieldname, and we have to strip
        // that out to let the default sorter below order them correctly on the assets table.
        $sort_override = str_replace('custom_fields.', '', $request->input('sort'));

        // This handles all of the pivot sorting (versus the assets.* fields
        // in the allowed_columns array)
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'assets.created_at';

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        switch ($sort_override) {
            case 'model':
                $assets->OrderModels($order);
                break;
            case 'model_number':
                $assets->OrderModelNumber($order);
                break;
            case 'category':
                $assets->OrderCategory($order);
                break;
            case 'manufacturer':
                $assets->OrderManufacturer($order);
                break;
            case 'company':
                $assets->OrderCompany($order);
                break;
            case 'location':
                $assets->OrderLocation($order);
            case 'rtd_location':
                $assets->OrderRtdLocation($order);
                break;
            case 'status_label':
                $assets->OrderStatus($order);
                break;
            case 'supplier':
                $assets->OrderSupplier($order);
                break;
            case 'assigned_to':
                $assets->OrderAssigned($order);
                break;
            case 'jobtitle':
                $assets->OrderByJobTitle($order);
                break;
            case 'created_by':
                $assets->OrderByCreatedByName($order);
                break;
            default:
                $numeric_sort = false;

                // Search through the custom fields array to see if we're sorting on a custom field
                if (array_search($column_sort, $all_custom_fields->pluck('db_column')->toArray()) !== false) {

                    // Check to see if this is a numeric field type
                    foreach ($all_custom_fields as $field) {
                        if (($field->db_column == $sort_override) && ($field->format == 'NUMERIC')) {
                            $numeric_sort = true;
                            break;
                        }
                    }

                    // This may not work for all databases, but it works for MySQL
                    if ($numeric_sort) {
                        $assets->orderByRaw(DB::getTablePrefix() . 'assets.' . $sort_override . ' * 1 ' . $order);
                    } else {
                        $assets->orderBy($sort_override, $order);
                    }
                } else {
                    $assets->orderBy($column_sort, $order);
                }
                break;
        }


        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $assets->count()) ? $assets->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $total = $assets->count();
        $assets = $assets->skip($offset)->take($limit)->get();


        /**
         * Include additional associated relationships
         */
        if ($request->input('components')) {
            $assets->loadMissing(['components' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);
        }

        return (new $transformer)->transformAssets($assets, $total, $request);
    }


    /**
     * Returns JSON with information about an asset (by tag) for detail view.
     *
     * @param string $tag
     * @since [v4.2.1]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function showByTag(Request $request, $tag): JsonResponse | array
    {
        $this->authorize('index', Asset::class);
        $assets = Asset::where('asset_tag', $tag)->with('assetstatus')->with('assignedTo');

        // Check if they've passed ?deleted=true
        if ($request->input('deleted', 'false') == 'true') {
            $assets = $assets->withTrashed();
        }

        if (($assets = $assets->get()) && ($assets->count()) > 0) {

            // If there is exactly one result and the deleted parameter is not passed, we should pull the first (and only)
            // asset from the returned collection, since transformAsset() expects an Asset object, NOT a collection
            if (($assets->count() == 1) && ($request->input('deleted') != 'true')) {
                return (new AssetsTransformer)->transformAsset($assets->first());

                // If there is more than one result OR if the endpoint is requesting deleted items (even if there is only one
                // match, return the normal collection transformed.
            } else {
                return (new AssetsTransformer)->transformAssets($assets, $assets->count());
            }
        }

        // If there are 0 results, return the "no such asset" response
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')), 200);
    }

    /**
     * Returns JSON with information about an asset (by serial) for detail view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param string $serial
     * @since [v4.2.1]
     * @return \Illuminate\Http\JsonResponse
     */
    public function showBySerial(Request $request, $serial): JsonResponse | array
    {
        $this->authorize('index', Asset::class);
        $assets = Asset::where('serial', $serial)->with([
            'assetstatus',
            'assignedTo',
            'company',
            'defaultLoc',
            'location',
            'model.category',
            'model.depreciation',
            'model.fieldset',
            'model.manufacturer',
            'supplier',
        ]);

        // Check if they've passed ?deleted=true
        if ($request->input('deleted', 'false') == 'true') {
            $assets = $assets->withTrashed();
        }

        $offset = ($request->input('offset') > $assets->count()) ? $assets->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $total = $assets->count();
        $assets = $assets->skip($offset)->take($limit)->get();

        if (($assets) && ($assets->count()) > 0) {
            return (new AssetsTransformer)->transformAssets($assets, $total);
        }

        // If there are 0 results, return the "no such asset" response
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')), 200);
    }

    /**
     * Returns JSON with information about an asset for detail view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v4.0]
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id): JsonResponse | array
    {
        if ($asset = Asset::with('assetstatus')
            ->with('assignedTo')->withTrashed()
            ->withCount('checkins as checkins_count', 'checkouts as checkouts_count', 'userRequests as user_requests_count')->find($id)
        ) {
            $this->authorize('view', $asset);

            return (new AssetsTransformer)->transformAsset($asset, $request->input('components'));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')), 200);
    }

    public function licenses(Request $request, $id): array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', License::class);
        $asset = Asset::where('id', $id)->withTrashed()->firstorfail();
        $licenses = $asset->licenses()->get();

        return (new LicensesTransformer())->transformLicenses($licenses, $licenses->count());
    }


    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     */
    public function selectlist(Request $request): array
    {

        $assets = Asset::select([
            'assets.id',
            'assets.name',
            'assets.asset_tag',
            'assets.model_id',
            'assets.assigned_to',
            'assets.assigned_type',
            'assets.status_id',
        ])->with('model', 'assetstatus', 'assignedTo')
            ->NotArchived();

        if ((Setting::getSettings()->full_multiple_companies_support=='1') &&  ($request->filled('companyId'))) {
            $assets->where('assets.company_id', $request->input('companyId'));
        }

        if ($request->filled('assetStatusType') && $request->input('assetStatusType') === 'RTD') {
            $assets = $assets->RTD();
        }

        if ($request->filled('search')) {
            $assets = $assets->AssignedSearch($request->input('search'));
        }

        $assets = $assets->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($assets as $asset) {


            $asset->use_text = $asset->present()->fullName;

            if (($asset->checkedOutToUser()) && ($asset->assigned)) {
                $asset->use_text .= ' → ' . $asset->assigned->display_name;
            }


            if ($asset->assetstatus->getStatuslabelType() == 'pending') {
                $asset->use_text .= '(' . $asset->assetstatus->getStatuslabelType() . ')';
            }

            $asset->use_image = ($asset->getImageUrl()) ? $asset->getImageUrl() : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($assets);
    }


    /**
     * Accepts a POST request to create a new asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param \App\Http\Requests\ImageUploadRequest $request
     * @since [v4.0]
     */
    public function store(StoreAssetRequest $request): JsonResponse
    {
        $asset = new Asset();
        $asset->model()->associate(AssetModel::find((int) $request->get('model_id')));

        $asset->fill($request->validated());
        $asset->created_by    = auth()->id();

        /**
         * this is here just legacy reasons. Api\AssetController
         * used image_source  once to allow encoded image uploads.
         */
        if ($request->has('image_source')) {
            $request->offsetSet('image', $request->offsetGet('image_source'));
        }

        $asset = $request->handleImages($asset);

        // Update custom fields in the database.
        $model = AssetModel::find($request->input('model_id'));

        // Check that it's an object and not a collection
        // (Sometimes people send arrays here and they shouldn't
        if (($model) && ($model instanceof AssetModel) && ($model->fieldset)) {
            foreach ($model->fieldset->fields as $field) {

                // Set the field value based on what was sent in the request
                $field_val = $request->input($field->db_column, null);

                // If input value is null, use custom field's default value
                if ($field_val == null) {
                    Log::debug('Field value for ' . $field->db_column . ' is null');
                    $field_val = $field->defaultValue($request->get('model_id'));
                    Log::debug('Use the default fieldset value of ' . $field->defaultValue($request->get('model_id')));
                }

                // if the field is set to encrypted, make sure we encrypt the value
                if ($field->field_encrypted == '1') {
                    Log::debug('This model field is encrypted in this fieldset.');

                    if (Gate::allows('assets.view.encrypted_custom_fields')) {

                        // If input value is null, use custom field's default value
                        if (($field_val == null) && ($request->has('model_id') != '')) {
                            $field_val = Crypt::encrypt($field->defaultValue($request->get('model_id')));
                        } else {
                            $field_val = Crypt::encrypt($request->input($field->db_column));
                        }
                    }
                }
                if ($field->element == 'checkbox') {
                    if (is_array($field_val)) {
                        $field_val = implode(',', $field_val);
                    }
                }


                $asset->{$field->db_column} = $field_val;
            }
        }

        if ($asset->save()) {
            if ($request->get('assigned_user')) {
                $target = User::find(request('assigned_user'));
            } elseif ($request->get('assigned_asset')) {
                $target = Asset::find(request('assigned_asset'));
            } elseif ($request->get('assigned_location')) {
                $target = Location::find(request('assigned_location'));
            }
            if (isset($target)) {
                $asset->checkOut($target, auth()->user(), date('Y-m-d H:i:s'), '', 'Checked out on asset creation', e($request->get('name')));
            }

            if ($asset->image) {
                $asset->image = $asset->getImageUrl();
            }

            return response()->json(Helper::formatStandardApiResponse('success', $asset, trans('admin/hardware/message.create.success')));

            // below is what we want the _eventual_ return to look like - in a more standardized format.
            // return response()->json(Helper::formatStandardApiResponse('success', (new AssetsTransformer)->transformAsset($asset), trans('admin/hardware/message.create.success')));

        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $asset->getErrors()), 200);
    }


    /**
     * Accepts a POST request to update an asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $asset->fill($request->validated());

        if ($request->has('model_id')) {
            $asset->model()->associate(AssetModel::find($request->validated()['model_id']));
        }
        if ($request->has('company_id')) {
            $asset->company_id = Company::getIdForCurrentUser($request->validated()['company_id']);
        }
        if ($request->has('rtd_location_id') && !$request->has('location_id')) {
            $asset->location_id = $request->validated()['rtd_location_id'];
        }
        if ($request->input('last_audit_date')) {
            $asset->last_audit_date = Carbon::parse($request->input('last_audit_date'))->startOfDay()->format('Y-m-d H:i:s');
        }

        /**
         * this is here just legacy reasons. Api\AssetController
         * used image_source  once to allow encoded image uploads.
         */
        if ($request->has('image_source')) {
            $request->offsetSet('image', $request->offsetGet('image_source'));
        }

        $asset = $request->handleImages($asset);
        $model = $asset->model;

        // Update custom fields
        $problems_updating_encrypted_custom_fields = false;
        if (($model) && (isset($model->fieldset))) {
            foreach ($model->fieldset->fields as $field) {
                $field_val = $request->input($field->db_column, null);

                if ($request->has($field->db_column)) {
                    if ($field->element == 'checkbox') {
                        if (is_array($field_val)) {
                            $field_val = implode(',', $field_val);
                        }
                    }
                    if ($field->field_encrypted == '1') {
                        if (Gate::allows('assets.view.encrypted_custom_fields')) {
                            $field_val = Crypt::encrypt($field_val);
                        } else {
                            $problems_updating_encrypted_custom_fields = true;
                            continue;
                        }
                    }
                    $asset->{$field->db_column} = $field_val;
                }
            }
        }
        if ($asset->save()) {
            if (($request->filled('assigned_user')) && ($target = User::find($request->get('assigned_user')))) {
                $location = $target->location_id;
            } elseif (($request->filled('assigned_asset')) && ($target = Asset::find($request->get('assigned_asset')))) {
                $location = $target->location_id;

                Asset::where('assigned_type', \App\Models\Asset::class)->where('assigned_to', $asset->id)
                    ->update(['location_id' => $target->location_id]);
            } elseif (($request->filled('assigned_location')) && ($target = Location::find($request->get('assigned_location')))) {
                $location = $target->id;
            }

            if (isset($target)) {
                $asset->checkOut($target, auth()->user(), date('Y-m-d H:i:s'), '', 'Checked out on asset update', e($request->get('name')), $location);
            }

            if ($asset->image) {
                $asset->image = $asset->getImageUrl();
            }

            if ($problems_updating_encrypted_custom_fields) {
                return response()->json(Helper::formatStandardApiResponse('success', $asset, trans('admin/hardware/message.update.encrypted_warning')));
                // Below is the *correct* return since it uses the transformer, but we have to use the old, flat return for now until we can update Jamf2Snipe and Kanji2Snipe
                // return response()->json(Helper::formatStandardApiResponse('success', (new AssetsTransformer)->transformAsset($asset), trans('admin/hardware/message.update.encrypted_warning')));
            } else {
                return response()->json(Helper::formatStandardApiResponse('success', $asset, trans('admin/hardware/message.update.success')));
                // Below is the *correct* return since it uses the transformer, but we have to use the old, flat return for now until we can update Jamf2Snipe and Kanji2Snipe
                /// return response()->json(Helper::formatStandardApiResponse('success', (new AssetsTransformer)->transformAsset($asset), trans('admin/hardware/message.update.success')));
            }
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $asset->getErrors()), 200);
    }


    /**
     * Delete a given asset (mark as deleted).
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v4.0]
     */
    public function destroy($id): JsonResponse
    {
        $this->authorize('delete', Asset::class);

        if ($asset = Asset::find($id)) {
            $this->authorize('delete', $asset);

            if ($asset->assignedTo) {

                $target = $asset->assignedTo;
                $checkin_at = date('Y-m-d H:i:s');
                $originalValues = $asset->getRawOriginal();
                event(new CheckoutableCheckedIn($asset, $target, auth()->user(), 'Checkin on delete', $checkin_at, $originalValues));
                DB::table('assets')
                    ->where('id', $asset->id)
                    ->update(['assigned_to' => null]);
            }

            $asset->delete();

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.delete.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')), 200);
    }



    /**
     * Restore a soft-deleted asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v5.1.18]
     */
    public function restore(Request $request, $assetId = null): JsonResponse
    {

        if ($asset = Asset::withTrashed()->find($assetId)) {
            $this->authorize('delete', $asset);

            if ($asset->deleted_at == '') {
                return response()->json(Helper::formatStandardApiResponse('error', trans('general.not_deleted', ['item_type' => trans('general.asset')])), 200);
            }

            if ($asset->restore()) {
                return response()->json(Helper::formatStandardApiResponse('success', trans('admin/hardware/message.restore.success')), 200);
            }

            // Check validation to make sure we're not restoring an asset with the same asset tag (or unique attribute) as an existing asset
            return response()->json(Helper::formatStandardApiResponse('error', trans('general.could_not_restore', ['item_type' => trans('general.asset'), 'error' => $asset->getErrors()->first()])), 200);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.does_not_exist')), 200);
    }

    /**
     * Checkout an asset by its tag.
     *
     * @author [N. Butler]
     * @param string $tag
     * @since [v6.0.5]
     */
    public function checkoutByTag(AssetCheckoutRequest $request, $tag): JsonResponse
    {
        if ($asset = Asset::where('asset_tag', $tag)->first()) {
            return $this->checkout($request, $asset->id);
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, 'Asset not found'), 200);
    }

    /**
     * Checkout an asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v4.0]
     */
    public function checkout(AssetCheckoutRequest $request, $asset_id): JsonResponse
    {
        $this->authorize('checkout', Asset::class);
        $asset = Asset::findOrFail($asset_id);

        if (! $asset->availableForCheckout()) {
            return response()->json(Helper::formatStandardApiResponse('error', ['asset' => e($asset->asset_tag)], trans('admin/hardware/message.checkout.not_available')));
        }

        $this->authorize('checkout', $asset);

        $error_payload = [];
        $error_payload['asset'] = [
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
        ];

        // This item is checked out to a location
        if (request('checkout_to_type') == 'location') {
            $target = Location::find(request('assigned_location'));
            $asset->location_id = ($target) ? $target->id : '';
            $error_payload['target_id'] = $request->input('assigned_location');
            $error_payload['target_type'] = 'location';
        } elseif (request('checkout_to_type') == 'asset') {
            $target = Asset::where('id', '!=', $asset_id)->find(request('assigned_asset'));
            // Override with the asset's location_id if it has one
            $asset->location_id = (($target) && (isset($target->location_id))) ? $target->location_id : '';
            $error_payload['target_id'] = $request->input('assigned_asset');
            $error_payload['target_type'] = 'asset';
        } elseif (request('checkout_to_type') == 'user') {
            // Fetch the target and set the asset's new location_id
            $target = User::find(request('assigned_user'));
            $asset->location_id = (($target) && (isset($target->location_id))) ? $target->location_id : '';
            $error_payload['target_id'] = $request->input('assigned_user');
            $error_payload['target_type'] = 'user';
        }

        if ($request->filled('status_id')) {
            $asset->status_id = $request->get('status_id');
        }

        if (! isset($target)) {
            return response()->json(Helper::formatStandardApiResponse('error', $error_payload, 'Checkout target for asset ' . e($asset->asset_tag) . ' is invalid - ' . $error_payload['target_type'] . ' does not exist.'));
        }

        $checkout_at = request('checkout_at', date('Y-m-d H:i:s'));
        $expected_checkin = request('expected_checkin', null);
        $note = request('note', null);
        // Using `->has` preserves the asset name if the name parameter was not included in request.
        $asset_name = request()->has('name') ? request('name') : $asset->name;

        // Set the location ID to the RTD location id if there is one
        // Wait, why are we doing this? This overrides the stuff we set further up, which makes no sense.
        // TODO: Follow up here. WTF. Commented out for now. 


        //        if ((isset($target->rtd_location_id)) && ($asset->rtd_location_id!='')) {
        //            $asset->location_id = $target->rtd_location_id;
        //        }

        if ($asset->checkOut($target, auth()->user(), $checkout_at, $expected_checkin, $note, $asset_name, $asset->location_id)) {
            return response()->json(Helper::formatStandardApiResponse('success', ['asset' => e($asset->asset_tag)], trans('admin/hardware/message.checkout.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['asset' => e($asset->asset_tag)], trans('admin/hardware/message.checkout.error')));
    }


    /**
     * Checkin an asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v4.0]
     */
    public function checkin(Request $request, $asset_id): JsonResponse
    {
        $asset = Asset::with('model')->findOrFail($asset_id);
        $this->authorize('checkin', $asset);

        $target = $asset->assignedTo;
        if (is_null($target)) {
            return response()->json(Helper::formatStandardApiResponse('error', [
                'asset_tag' => e($asset->asset_tag),
                'model' => e($asset->model->name),
                'model_number' => e($asset->model->model_number)
            ], trans('admin/hardware/message.checkin.already_checked_in')));
        }

        $asset->expected_checkin = null;
        //$asset->last_checkout = null;
        $asset->last_checkin = now();
        $asset->assignedTo()->disassociate($asset);
        $asset->accepted = null;

        if ($request->has('name')) {
            $asset->name = $request->input('name');
        }

        $this->migrateLegacyLocations($asset);

        $asset->location_id = $asset->rtd_location_id;

        if ($request->filled('location_id')) {
            $asset->location_id = $request->input('location_id');

            if ($request->input('update_default_location')) {
                $asset->rtd_location_id = $request->input('location_id');
            }
        }

        if ($request->filled('status_id')) {
            $asset->status_id = $request->input('status_id');
        }

        $checkin_at = $request->filled('checkin_at') ? $request->input('checkin_at') . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $originalValues = $asset->getRawOriginal();

        if (($request->filled('checkin_at')) && ($request->get('checkin_at') != date('Y-m-d'))) {
            $originalValues['action_date'] = $checkin_at;
        }

        $asset->licenseseats->each(function (LicenseSeat $seat) {
            $seat->update(['assigned_to' => null]);
        });

        // Get all pending Acceptances for this asset and delete them
        CheckoutAcceptance::pending()
            ->whereHasMorph(
                'checkoutable',
                [Asset::class],
                function (Builder $query) use ($asset) {
                    $query->where('id', $asset->id);
                }
            )
            ->get()
            ->map(function ($acceptance) {
                $acceptance->delete();
            });

        if ($asset->save()) {
            event(new CheckoutableCheckedIn($asset, $target, auth()->user(), $request->input('note'), $checkin_at, $originalValues));

            return response()->json(Helper::formatStandardApiResponse('success', [
                'asset_tag' => e($asset->asset_tag),
                'model' => e($asset->model->name),
                'model_number' => e($asset->model->model_number)
            ], trans('admin/hardware/message.checkin.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['asset' => e($asset->asset_tag)], trans('admin/hardware/message.checkin.error')));
    }

    /**
     * Checkin an asset by asset tag
     *
     * @author [A. Janes] [<ajanes@adagiohealth.org>]
     * @since [v6.0]
     */
    public function checkinByTag(Request $request, $tag = null): JsonResponse
    {
        $this->authorize('checkin', Asset::class);
        if (null == $tag && null !== ($request->input('asset_tag'))) {
            $tag = $request->input('asset_tag');
        }
        $asset = Asset::where('asset_tag', $tag)->first();

        if ($asset) {
            return $this->checkin($request, $asset->id);
        }

        return response()->json(Helper::formatStandardApiResponse('error', [
            'asset' => e($tag)
        ], 'Asset with tag ' . e($tag) . ' not found'));
    }


    /**
     * Mark an asset as audited
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $id
     * @since [v4.0]
     */
    public function audit(Request $request, Asset $asset): JsonResponse

    {
        $this->authorize('audit', Asset::class);

        $settings = Setting::getSettings();
        $dt = Carbon::now()->addMonths($settings->audit_interval)->toDateString();

        // Allow the asset tag to be passed in the payload (legacy method)
        if ($request->filled('asset_tag')) {
            $asset = Asset::where('asset_tag', '=', $request->input('asset_tag'))->first();
        }

        if ($asset) {

            $originalValues = $asset->getRawOriginal();

            $asset->next_audit_date = $dt;

            if ($request->filled('next_audit_date')) {
                $asset->next_audit_date = $request->input('next_audit_date');
            }

            // Check to see if they checked the box to update the physical location,
            // not just note it in the audit notes
            if ($request->input('update_location') == '1') {
                $asset->location_id = $request->input('location_id');
            }

            $asset->last_audit_date = date('Y-m-d H:i:s');

            // Set up the payload for re-display in the API response
            $payload = [
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
                'note' => $request->input('note'),
                'next_audit_date' => Helper::getFormattedDateObject($asset->next_audit_date),
            ];


            /**
             * Update custom fields in the database.
             * Validation for these fields is handled through the AssetRequest form request
             * $model = AssetModel::find($request->get('model_id'));
            */
            if (($asset->model) && ($asset->model->fieldset)) {
                $payload['custom_fields'] = [];
                foreach ($asset->model->fieldset->fields as $field) {
                    if (($field->display_audit=='1') && ($request->has($field->db_column))) {
                        if ($field->field_encrypted == '1') {
                            if (Gate::allows('assets.view.encrypted_custom_fields')) {
                                if (is_array($request->input($field->db_column))) {
                                    $asset->{$field->db_column} = Crypt::encrypt(implode(', ', $request->input($field->db_column)));
                                } else {
                                    $asset->{$field->db_column} = Crypt::encrypt($request->input($field->db_column));
                                }
                            }
                        } else {
                            if (is_array($request->input($field->db_column))) {
                                $asset->{$field->db_column} = implode(', ', $request->input($field->db_column));
                            } else {
                                $asset->{$field->db_column} = $request->input($field->db_column);
                            }
                        }
                        $payload['custom_fields'][$field->db_column] =  $request->input($field->db_column);
                    }

                }
            }

            // Invoke the validation to see if the audit will complete successfully
            $asset->setRules($asset->getRules() + $asset->customFieldValidationRules());

            // Validate the rest of the data before we turn off the event dispatcher
            if ($asset->isInvalid()) {
                return response()->json(Helper::formatStandardApiResponse('error', ['asset_tag' => $asset->asset_tag],  $asset->getErrors()));
            }


            /**
             * Even though we do a save() further down, we don't want to log this as a "normal" asset update,
             * which would trigger the Asset Observer and would log an asset *update* log entry (because the
             * de-normed fields like next_audit_date on the asset itself will change on save()) *in addition* to
             * the audit log entry we're creating through this controller.
             *
             * To prevent this double-logging (one for update and one for audit), we skip the observer and bypass
             * that de-normed update log entry by using unsetEventDispatcher(), BUT invoking unsetEventDispatcher()
             * will bypass normal model-level validation that's usually handled at the observer)
             *
             * We handle validation on the save() by checking if the asset is valid via the ->isValid() method,
             * which manually invokes Watson Validating to make sure the asset's model is valid.
             *
             * @see \App\Observers\AssetObserver::updating()
             * @see \App\Models\Asset::save()
             */

             $asset->unsetEventDispatcher();


            /**
             * Invoke Watson Validating to check the asset itself and check to make sure it saved correctly.
             * We have to invoke this manually because of the unsetEventDispatcher() above.)
             */
            if ($asset->isValid() && $asset->save()) {
                $asset->logAudit(request('note'), request('location_id'), null, $originalValues);
                return response()->json(Helper::formatStandardApiResponse('success', $payload, trans('admin/hardware/message.audit.success')));
            }

        }


        // No matching asset for the asset tag that was passed.
        return response()->json(Helper::formatStandardApiResponse('error', null,  trans('admin/hardware/message.does_not_exist')), 200);

    }



    /**
     * Returns JSON listing of all requestable assets
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function requestable(Request $request): JsonResponse | array
    {
        $this->authorize('viewRequestable', Asset::class);

        $allowed_columns = [
            'name',
            'asset_tag',
            'serial',
            'model_number',
            'image',
            'purchase_cost',
            'expected_checkin',
        ];

        $all_custom_fields = CustomField::all(); //used as a 'cache' of custom fields throughout this page load

        foreach ($all_custom_fields as $field) {
            $allowed_columns[] = $field->db_column_name();
        }

        $assets = Asset::select('assets.*')
            ->with(
                'location',
                'assetstatus',
                'assetlog',
                'company',
                'assignedTo',
                'model.category',
                'model.manufacturer',
                'model.fieldset',
                'supplier',
                'requests'
            );




        if ($request->filled('search')) {
            $assets->TextSearch($request->input('search'));
        }

        // Search custom fields by column name
        foreach ($all_custom_fields as $field) {
            if ($request->filled($field->db_column_name())) {
                $assets->where($field->db_column_name(), '=', $request->input($field->db_column_name()));
            }
        }

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override = str_replace('custom_fields.', '', $request->input('sort'));

        // This handles all the pivot sorting (versus the assets.* fields
        // in the allowed_columns array)
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'assets.created_at';

        switch ($request->input('sort')) {
            case 'model':
                $assets->OrderModels($order);
                break;
            case 'model_number':
                $assets->OrderModelNumber($order);
                break;
            case 'location':
                $assets->OrderLocation($order);
                break;
            default:
                $assets->orderBy($column_sort, $order);
                break;
        }

        $assets->requestableAssets();

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $assets->count()) ? $assets->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $total = $assets->count();
        $assets = $assets->skip($offset)->take($limit)->get();

        return (new AssetsTransformer)->transformRequestedAssets($assets, $total);
    }


    public function assignedAssets(Request $request, Asset $asset) : JsonResponse | array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $asset);

        $query = Asset::where([
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);

        $total = $query->count();

        $assets = $query->applyOffsetAndLimit($total)->get();

        return (new AssetsTransformer)->transformAssets($assets, $total);
    }

    public function assignedAccessories(Request $request, Asset $asset) : JsonResponse | array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $asset);
        $accessory_checkouts = AccessoryCheckout::AssetsAssigned()
            ->where('assigned_to', $asset->id)
            ->with('adminuser')
            ->with('accessories');

        $offset = ($request->input('offset') > $accessory_checkouts->count()) ? $accessory_checkouts->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $total = $accessory_checkouts->count();
        $accessory_checkouts = $accessory_checkouts->skip($offset)->take($limit)->get();
        return (new AssetsTransformer)->transformCheckedoutAccessories($accessory_checkouts, $total);
    }

    public function assignedComponents(Request $request, Asset $asset): JsonResponse|array
    {
        $this->authorize('view', Asset::class);
        $this->authorize('view', $asset);

        $asset->loadCount('components');
        $total = $asset->components_count;

        $components = $asset->load(['components' => fn($query) => $query->applyOffsetAndLimit($total)])->components;

        return (new ComponentsTransformer)->transformComponents($components, $total);
    }

    /**
     * Generate asset labels by tag
     * 
     * @author [Nebelkreis] [https://github.com/NebelKreis]
     * 
     * @param Request $request Contains asset_tags array of asset tags to generate labels for
     * @return JsonResponse Returns base64 encoded PDF on success, error message on failure
     */
    public function getLabels(Request $request): JsonResponse
    {
        try {
            $this->authorize('view', Asset::class);

             // Validate that asset tags were provided in the request
            if (!$request->filled('asset_tags')) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 
                    trans('admin/hardware/message.no_assets_selected')), 400);
            }

             // Convert asset tags from request into collection and fetch matching assets
            $asset_tags = collect($request->input('asset_tags'));
            $assets = Asset::whereIn('asset_tag', $asset_tags)->get();

             // Return error if no assets were found for the provided tags
            if ($assets->isEmpty()) {
                return response()->json(Helper::formatStandardApiResponse('error', null,
                    trans('admin/hardware/message.does_not_exist')), 404);
            }

            try {
                $settings = Setting::getSettings();

                // Check if logo file exists in storage and disable logo if not found
                // This prevents errors when trying to include a non-existent logo in the PDF
                $settings->label_logo = ($original_logo = $settings->label_logo) && !Storage::disk('public')->exists('/' . $original_logo) ? null : $settings->label_logo;


                $label = new Label();
                
                if (!$label) {
                    throw new \Exception('Label object could not be created');
                }

                // Configure label with assets and settings
                // bulkedit=false and count=0 are default values for label generation
                $label = $label->with('assets', $assets)
                              ->with('settings', $settings)
                              ->with('bulkedit', false)
                              ->with('count', 0);

                // Generate PDF using callback function
                // The callback captures the PDF content in $pdf_content variable
                $pdf_content = '';
                $label->render(function($pdf) use (&$pdf_content) {
                    $pdf_content = $pdf->Output('', 'S');
                    return $pdf;
                });

                // Verify PDF was generated successfully
                if (empty($pdf_content)) {
                    throw new \Exception('PDF content is empty');
                }

                $encoded_content = base64_encode($pdf_content);

                return response()->json(Helper::formatStandardApiResponse('success', [
                    'pdf' => $encoded_content
                ], trans('admin/hardware/message.labels_generated')));

            } catch (\Exception $e) {
                return response()->json(Helper::formatStandardApiResponse('error', [
                    'error_message' => $e->getMessage(),
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ], trans('admin/hardware/message.error_generating_labels')), 500);
            }
        } catch (\Exception $e) {
            return response()->json(Helper::formatStandardApiResponse('error', [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile()
            ], $e->getMessage()), 500);
        }
    }
}
