<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ComponentsTransformer;
use App\Models\Component;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComponentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     *
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', Component::class);

        // This array is what determines which fields should be allowed to be sorted on ON the table itself, no relations
        // Relations will be handled in query scopes a little further down.
        $allowed_columns = 
            [
                'id',
                'name',
                'min_amt',
                'order_number',
                'model_number',
                'serial',
                'purchase_date',
                'purchase_cost',
                'qty',
                'image',
                'notes',
                // These are *relationships* so we wouldn't normally include them in this array,
                // since they would normally create a `column not found` error,
                // BUT we account for them in the ordering switch down at the end of this method
                // DO NOT ADD ANYTHING TO THIS LIST WITHOUT CHECKING THE ORDERING SWITCH BELOW!
                'company',
                'location',
                'category',
                'manufacturer',
                'supplier',

            ];

        $components = Component::select('components.*')
            ->with('company', 'location', 'category', 'assets', 'supplier', 'adminuser', 'manufacturer', 'uncontrainedAssets')
            ->withSum('uncontrainedAssets', 'components_assets.assigned_qty');

        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);

            $filter = array_filter($filter, function ($key) use ($allowed_columns) {
                return in_array($key, $allowed_columns);
            }, ARRAY_FILTER_USE_KEY);

        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $components->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $components->TextSearch($request->input('search'));
        }


        if ($request->filled('name')) {
            $components->where('name', '=', $request->input('name'));
        }

        if ($request->filled('company_id')) {
            $components->where('components.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('category_id')) {
            $components->where('category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('supplier_id')) {
            $components->where('supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $components->where('manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('model_number')) {
            $components->where('model_number', '=', $request->input('model_number'));
        }

        if ($request->filled('location_id')) {
            $components->where('location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $components->where('notes','=',$request->input('notes'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $components->count()) ? $components->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'category':
                $components = $components->OrderCategory($order);
                break;
            case 'location':
                $components = $components->OrderLocation($order);
                break;
            case 'company':
                $components = $components->OrderCompany($order);
                break;
            case 'supplier':
                $components = $components->OrderSupplier($order);
                break;
            case 'manufacturer':
                $components = $components->OrderManufacturer($order);
                break;
            case 'created_by':
                $components = $components->OrderByCreatedBy($order);
                break;
            default:
                $components = $components->orderBy($column_sort, $order);
                break;
        }

        $total = $components->count();
        $components = $components->skip($offset)->take($limit)->get();

        return (new ComponentsTransformer)->transformComponents($components, $total);
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
        $this->authorize('create', Component::class);
        $component = new Component;
        $component->fill($request->all());
        $component = $request->handleImages($component);

        if ($component->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $component, trans('admin/components/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $component->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int  $id
     */
    public function show($id) : array
    {
        $this->authorize('view', Component::class);
        $component = Component::findOrFail($id);

        if ($component) {
            return (new ComponentsTransformer)->transformComponent($component);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param   \App\Http\Requests\ImageUploadRequest  $request
     * @param  int  $id
     */
    public function update(ImageUploadRequest $request, $id) : JsonResponse
    {
        $this->authorize('update', Component::class);
        $component = Component::findOrFail($id);
        $component->fill($request->all());
        $component = $request->handleImages($component);
        

        if ($component->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $component, trans('admin/components/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $component->getErrors()));
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
        $this->authorize('delete', Component::class);
        $component = Component::findOrFail($id);
        $this->authorize('delete', $component);

        if ($component->numCheckedOut() > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null,  trans('admin/components/message.delete.error_qty')));
        }

        $component->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/components/message.delete.success')));
    }

    /**
     * Display all assets attached to a component
     *
     * @author [A. Bergamasco] [@vjandrea]
     * @since [v4.0]
     * @param Request $request
     * @param int $id
    */
    public function getAssets(Request $request, $id) : array
    {
        $this->authorize('view', \App\Models\Asset::class);
        
        $component = Component::findOrFail($id);
        
        $offset = request('offset', 0);
        $limit = $request->input('limit', 50);
        
        if ($request->filled('search')) {
            $assets = $component->assets()
                                ->where(function ($query) use ($request) {
                                    $search_str = '%' . $request->input('search') . '%';
                                    $query->where('name', 'like', $search_str)
                                            ->orWhereIn('model_id', function (Builder $query) use ($request) {
                                                $search_str = '%' . $request->input('search') . '%';
                                                $query->selectRaw('id')->from('models')->where('name', 'like', $search_str);
                                            })
                                            ->orWhere('asset_tag', 'like', $search_str);
                                         })
                                         ->get();
            $total = $assets->count();
        } else {
            $assets = $component->assets();
            
            $total = $assets->count();
            $assets = $assets->skip($offset)->take($limit)->get();
        }

        return (new ComponentsTransformer)->transformCheckedoutComponents($assets, $total);
    }


    /**
     * Validate and checkout the component.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * t
     * @since [v5.1.8]
     * @param Request $request
     * @param int $componentId
     */
    public function checkout(Request $request, $componentId) : JsonResponse
    {
        // Check if the component exists
        if (!$component = Component::find($componentId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.does_not_exist')));
        }

        $this->authorize('checkout', $component);

        $validator = Validator::make($request->all(), [
            'assigned_to'          => 'required|exists:assets,id',
            'assigned_qty'      => "required|numeric|min:1|digits_between:1,".$component->numRemaining(),
        ]);

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', $validator->errors()));

        }

        // Make sure there is at least one available to checkout
        if ($component->numRemaining() < $request->get('assigned_qty')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.checkout.unavailable', ['remaining' => $component->numRemaining(), 'requested' => $request->get('assigned_qty')])));
        }

        if ($component->numRemaining() >= $request->get('assigned_qty')) {

            $asset = Asset::find($request->input('assigned_to'));
            $component->assigned_to = $request->input('assigned_to');

            $component->assets()->attach($component->id, [
                'component_id' => $component->id,
                'created_at' => Carbon::now(),
                'assigned_qty' => $request->get('assigned_qty', 1),
                'created_by' => auth()->id(),
                'asset_id' => $request->get('assigned_to'),
                'note' => $request->get('note'),
            ]);

            $component->logCheckout($request->input('note'), $asset);

            return response()->json(Helper::formatStandardApiResponse('success', null,  trans('admin/components/message.checkout.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.checkout.unavailable', ['remaining' => $component->numRemaining(), 'requested' => $request->get('assigned_qty')])));
    }

    /**
     * Validate and store checkin data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.1.8]
     * @param Request $request
     * @param $component_asset_id
     */
    public function checkin(Request $request, $component_asset_id) : JsonResponse
    {
        if ($component_assets = DB::table('components_assets')->find($component_asset_id)) {
            if (is_null($component = Component::find($component_assets->component_id))) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/components/message.not_found')));
            }

            $this->authorize('checkin', $component);

            $max_to_checkin = $component_assets->assigned_qty;

            $validator = Validator::make($request->all(), [
                "checkin_qty" => "required|numeric|between:1,$max_to_checkin"
            ]);

            if ($validator->fails()) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Checkin quantity must be between 1 and ' . $max_to_checkin));
            }

            // Validation passed, so let's figure out what we have to do here.
            $qty_remaining_in_checkout = ($component_assets->assigned_qty - (int)$request->input('checkin_qty', 1));

            // We have to modify the record to reflect the new qty that's
            // actually checked out.
            $component_assets->assigned_qty = $qty_remaining_in_checkout;

            Log::debug($component_asset_id.' - '.$qty_remaining_in_checkout.' remaining in record '.$component_assets->id);

            DB::table('components_assets')->where('id', $component_asset_id)->update(['assigned_qty' => $qty_remaining_in_checkout]);

            // If the checked-in qty is exactly the same as the assigned_qty,
            // we can simply delete the associated components_assets record
            if ($qty_remaining_in_checkout === 0) {
                DB::table('components_assets')->where('id', '=', $component_asset_id)->delete();
            }

            $asset = Asset::find($component_assets->asset_id);

            event(new CheckoutableCheckedIn($component, $asset, auth()->user(), $request->input('note'), Carbon::now()));

            return response()->json(Helper::formatStandardApiResponse('success', null,  trans('admin/components/message.checkin.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'No matching checkouts for that component join record'));
    }

}
