<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccessoryCheckoutRequest;
use App\Http\Requests\StoreAccessoryRequest;
use App\Http\Transformers\AccessoriesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use App\Models\AccessoryCheckout;

class AccessoriesController extends Controller
{
    use CheckInOutRequest;

    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->cannot('reports.view')) {
            $this->authorize('view', Accessory::class);
        }


        // This array is what determines which fields should be allowed to be sorted on ON the table itself, no relations
        // Relations will be handled in query scopes a little further down.
        $allowed_columns = 
            [
                'id',
                'name',
                'model_number',
                'eol',
                'notes',
                'created_at',
                'min_amt',
                'company_id',
                'notes',
                'checkouts_count',
                'qty',
                // These are *relationships* so we wouldn't normally include them in this array,
                // since they would normally create a `column not found` error,
                // BUT we account for them in the ordering switch down at the end of this method
                // DO NOT ADD ANYTHING TO THIS LIST WITHOUT CHECKING THE ORDERING SWITCH BELOW!
                'company',
                'location',
                'category',
                'supplier',
                'manufacturer',
            ];


        $accessories = Accessory::select('accessories.*')
            ->with('category', 'company', 'manufacturer', 'checkouts', 'location', 'supplier', 'adminuser')
            ->withCount('checkouts as checkouts_count');

        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);
            $filter = array_filter($filter, function ($key) use ($allowed_columns) {
                return in_array($key, $allowed_columns);
            }, ARRAY_FILTER_USE_KEY);

        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $accessories->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $accessories->TextSearch($request->input('search'));
        }


        if ($request->filled('company_id')) {
            $accessories->where('accessories.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('category_id')) {
            $accessories->where('category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $accessories->where('manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $accessories->where('supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $accessories->where('location_id','=',$request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $accessories->where('notes','=',$request->input('notes'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $accessories->count()) ? $accessories->count() : abs($request->input('offset'));
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'category':
                $accessories = $accessories->OrderCategory($order);
                break;
            case 'company':
                $accessories = $accessories->OrderCompany($order);
                break;
            case 'location':
                $accessories = $accessories->OrderLocation($order);
                break;
            case 'manufacturer':
                $accessories = $accessories->OrderManufacturer($order);
                break;    
            case 'supplier':
                $accessories = $accessories->OrderSupplier($order);
                break;
            case 'created_by':
                $accessories = $accessories->OrderByCreatedByName($order);
                break;
            default:
                $accessories = $accessories->orderBy($column_sort, $order);
                break;
        }
 
        $total = $accessories->count();
        $accessories = $accessories->skip($offset)->take($limit)->get();

        return (new AccessoriesTransformer)->transformAccessories($accessories, $total);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ImageUploadRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function store(StoreAccessoryRequest $request)
    {
        $accessory = new Accessory;
        $accessory->fill($request->all());
        $accessory = $request->handleImages($accessory);

        if ($accessory->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $accessory, trans('admin/accessories/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $accessory->getErrors()));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return array
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function show($id)
    {
        $this->authorize('view', Accessory::class);
        $accessory = Accessory::withCount('checkouts as checkouts_count')->findOrFail($id);

        return (new AccessoriesTransformer)->transformAccessory($accessory);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return array
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function accessory_detail($id)
    {
        $this->authorize('view', Accessory::class);
        $accessory = Accessory::findOrFail($id);

        return (new AccessoriesTransformer)->transformAccessory($accessory);
    }


    /**
     * Get the list of checkouts for a specific accessory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return  | array
     */
    public function checkedout(Request $request, $id)
    {
        $this->authorize('view', Accessory::class);

        $accessory = Accessory::with('lastCheckout')->findOrFail($id);
        $offset = request('offset', 0);
        $limit = request('limit', 50);

        // Total count of all checkouts for this asset
        $accessory_checkouts = $accessory->checkouts();

        // Check for search text in the request
        if ($request->filled('search')) {
            $accessory_checkouts = $accessory_checkouts->TextSearch($request->input('search'));
        }

        $total = $accessory_checkouts->count();
        $accessory_checkouts = $accessory_checkouts->skip($offset)->take($limit)->get();

        return (new AccessoriesTransformer)->transformCheckedoutAccessory($accessory_checkouts, $total);
    }


    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ImageUploadRequest $request, $id)
    {
        $this->authorize('update', Accessory::class);
        $accessory = Accessory::findOrFail($id);
        $accessory->fill($request->all());
        $accessory = $request->handleImages($accessory);

        if ($accessory->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $accessory, trans('admin/accessories/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $accessory->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('delete', Accessory::class);
        $accessory = Accessory::withCount('checkouts as checkouts_count')->findOrFail($id);
        $this->authorize($accessory);

        if ($accessory->checkouts_count > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/general.delete_disabled')));
        }

        $accessory->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/accessories/message.delete.success')));
    }


    /**
     * Save the Accessory checkout information.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @param  int  $accessoryId
     * @return \Illuminate\Http\JsonResponse
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function checkout(AccessoryCheckoutRequest $request, Accessory $accessory)
    {
        $this->authorize('checkout', $accessory);
        $target = $this->determineCheckoutTarget();
        $accessory->checkout_qty = $request->input('checkout_qty', 1);

        for ($i = 0; $i < $accessory->checkout_qty; $i++) {

            $accessory_checkout = new AccessoryCheckout([
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'assigned_to' => $target->id,
                'assigned_type' => $target::class,
                'note' => $request->input('note'),
            ]);


            $accessory_checkout->created_by = auth()->id();
            $accessory_checkout->save();

            $payload = [
                'accessory_id' => $accessory->id,
                'assigned_to' => $target->id,
                'assigned_type' => $target::class,
                'note' => $request->input('note'),
                'created_by' => auth()->id(),
                'pivot' => $accessory_checkout->id,
            ];
        }

        // Set this value to be able to pass the qty through to the event
        event(new CheckoutableCheckedOut($accessory, $target, auth()->user(), $request->input('note')));

        return response()->json(Helper::formatStandardApiResponse('success', $payload, trans('admin/accessories/message.checkout.success')));

    }

    /**
     * Check in the item so that it can be checked out again to someone else
     *
     * @param Request $request
     * @param int $accessoryUserId
     * @param string $backto
     * @return JsonResponse
     * @uses Accessory::checkin_email() to determine if an email can and should be sent
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @internal param int $accessoryId
     */
    public function checkin(Request $request, $accessoryUserId = null)
    {
        if (is_null($accessory_checkout = AccessoryCheckout::find($accessoryUserId))) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.does_not_exist', ['id' => $accessoryUserId])));
        }

        $accessory = Accessory::find($accessory_checkout->accessory_id);
        $this->authorize('checkin', $accessory);

        $accessory->logCheckin(User::find($accessory_checkout->assigned_to), $request->input('note'));

        // Was the accessory updated?
        if ($accessory_checkout->delete()) {
            if (! is_null($accessory_checkout->assigned_to)) {
                $user = User::find($accessory_checkout->assigned_to);
            }

            $payload = [
                'accessory_id' => $accessory->id,
                'note' => $request->input('note'),
                'created_by' => auth()->id(),
                'pivot' => $accessory_checkout->id,
            ];

            return response()->json(Helper::formatStandardApiResponse('success', $payload,  trans('admin/accessories/message.checkin.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/accessories/message.checkin.error')));

    }


    /**
    * Gets a paginated collection for the select2 menus
    *
    * @see \App\Http\Transformers\SelectlistTransformer
    *
    */
    public function selectlist(Request $request)
    {

        $accessories = Accessory::select([
            'accessories.id',
            'accessories.name',
        ]);

        if ($request->filled('search')) {
            $accessories = $accessories->where('accessories.name', 'LIKE', '%'.$request->get('search').'%');
        }

        $accessories = $accessories->orderBy('name', 'ASC')->paginate(50);

        return (new SelectlistTransformer)->transformSelectlist($accessories);
    }

}
