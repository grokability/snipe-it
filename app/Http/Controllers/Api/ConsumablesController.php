<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsumableRequest;
use App\Http\Transformers\ConsumablesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ConsumablesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function index(Request $request) : array
    {
        $this->authorize('index', Consumable::class);

        $consumables = Consumable::with('company', 'location', 'category', 'supplier', 'manufacturer')
            ->withCount('users as consumables_users_count');

        // This array is what determines which fields should be allowed to be sorted on ON the table itself.
        // These must match a column on the consumables table directly.
        $allowed_columns = [
            'id',
            'name',
            'order_number',
            'min_amt',
            'purchase_date',
            'purchase_cost',
            'company',
            'category',
            'model_number',
            'item_no',
            'manufacturer',
            'location',
            'qty',
            'image',
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


        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);

            $filter = array_filter($filter, function ($key) use ($allowed_columns) {
                return in_array($key, $allowed_columns);
            }, ARRAY_FILTER_USE_KEY);

        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $consumables->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $consumables->TextSearch($request->input('search'));
        }


        if ($request->filled('name')) {
            $consumables->where('name', '=', $request->input('name'));
        }

        if ($request->filled('company_id')) {
            $consumables->where('consumables.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('category_id')) {
            $consumables->where('category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('model_number')) {
            $consumables->where('model_number','=',$request->input('model_number'));
        }

        if ($request->filled('manufacturer_id')) {
            $consumables->where('manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $consumables->where('supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $consumables->where('location_id','=',$request->input('location_id'));
        }

        if ($request->filled('notes')) {
            $consumables->where('notes','=',$request->input('notes'));
        }


        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $consumables->count()) ? $consumables->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'category':
                $consumables = $consumables->OrderCategory($order);
                break;
            case 'location':
                $consumables = $consumables->OrderLocation($order);
                break;
            case 'manufacturer':
                $consumables = $consumables->OrderManufacturer($order);
                break;
            case 'company':
                $consumables = $consumables->OrderCompany($order);
                break;
            case 'remaining':
                $consumables = $consumables->OrderRemaining($order);
                break;
            case 'supplier':
                $consumables = $consumables->OrderSupplier($order);
                break;
            case 'created_by':
                $consumables = $consumables->OrderByCreatedBy($order);
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
                $consumables = $consumables->orderBy($sort, $order);
                break;
        }

        $total = $consumables->count();
        $consumables = $consumables->skip($offset)->take($limit)->get();

        return (new ConsumablesTransformer)->transformConsumables($consumables, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     */
    public function store(StoreConsumableRequest $request) : JsonResponse
    {
        $this->authorize('create', Consumable::class);
        $consumable = new Consumable;
        $consumable->fill($request->all());
        $consumable = $request->handleImages($consumable);

        if ($consumable->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $id
     */
    public function show($id) : array
    {
        $this->authorize('view', Consumable::class);
        $consumable = Consumable::with('users')->findOrFail($id);

        return (new ConsumablesTransformer)->transformConsumable($consumable);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     * @param  int $id
     */
    public function update(StoreConsumableRequest $request, $id) : JsonResponse
    {
        $this->authorize('update', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        $consumable->fill($request->all());
        $consumable = $request->handleImages($consumable);
        
        if ($consumable->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $consumable, trans('admin/consumables/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $consumable->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int $id
     */
    public function destroy($id) : JsonResponse
    {
        $this->authorize('delete', Consumable::class);
        $consumable = Consumable::findOrFail($id);
        $this->authorize('delete', $consumable);
        $consumable->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.delete.success')));
    }

    /**
    * Returns a JSON response containing details on the users associated with this consumable.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see \App\Http\Controllers\Consumables\ConsumablesController::getView() method that returns the form.
    * @since [v1.0]
    * @param int $consumableId
     */
    public function getDataView($consumableId) : array
    {
        $consumable = Consumable::with(['consumableAssignments'=> function ($query) {
            $query->orderBy($query->getModel()->getTable().'.created_at', 'DESC');
        },
        'consumableAssignments.adminuser'=> function ($query) {
        },
        'consumableAssignments.user'=> function ($query) {
        },
        ])->find($consumableId);

        if (! Company::isCurrentUserHasAccess($consumable)) {
            return ['total' => 0, 'rows' => []];
        }
        $this->authorize('view', Consumable::class);
        $rows = [];

        foreach ($consumable->consumableAssignments as $consumable_assignment) {
            $rows[] = [
                'avatar' => ($consumable_assignment->user) ? e($consumable_assignment->user->present()->gravatar) : '',
                'user' => ($consumable_assignment->user) ? [
                    'id' => (int) $consumable_assignment->user->id,
                    'name'=> e($consumable_assignment->user->display_name),
                ] : null,
                'created_at' => Helper::getFormattedDateObject($consumable_assignment->created_at, 'datetime'),
                'note' => ($consumable_assignment->note) ? e($consumable_assignment->note) : null,
                'created_by' => ($consumable_assignment->adminuser) ? [
                    'id' => (int) $consumable_assignment->adminuser->id,
                    'name'=> e($consumable_assignment->adminuser->display_name),
                ] : null,
            ];
        }

        $consumableCount = $consumable->users->count();
        $data = ['total' => $consumableCount, 'rows' => $rows];

        return $data;
    }

    /**
     * Checkout a consumable
     *
     * @author [A. Gutierrez] [<andres@baller.tv>]
     * @param int $id
     * @since [v4.9.5]
     */
    public function checkout(Request $request, $id) : JsonResponse
    {
        // Check if the consumable exists
        if (!$consumable = Consumable::with('users')->find($id)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.does_not_exist')));
        }

        $this->authorize('checkout', $consumable);

        $consumable->checkout_qty = $request->input('checkout_qty', 1);

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.checkout.unavailable')));
        }

        // Make sure there is a valid category
        if (!$consumable->category){
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.invalid_item_category_single', ['type' => trans('general.consumable')])));
        }

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0 || $consumable->checkout_qty > $consumable->numRemaining()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/consumables/message.checkout.unavailable', ['requested' => $consumable->checkout_qty, 'remaining' => $consumable->numRemaining() ])));
        }



        // Check if the user exists - @TODO:  this should probably be handled via validation, not here??
        if (!$user = User::find($request->input('assigned_to'))) {
            // Return error message
            return response()->json(Helper::formatStandardApiResponse('error', null, 'No user found'));
        }

        // Update the consumable data
        $consumable->assigned_to = $request->input('assigned_to');

        for ($i = 0; $i < $consumable->checkout_qty; $i++) {
            $consumable->users()->attach($consumable->id,
                [
                    'consumable_id' => $consumable->id,
                    'created_by' => $user->id,
                    'assigned_to' => $request->input('assigned_to'),
                    'note' => $request->input('note'),
                ]
            );
        }


        event(new CheckoutableCheckedOut($consumable, $user, auth()->user(), $request->input('note')));

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/consumables/message.checkout.success')));

    }

    /**
    * Gets a paginated collection for the select2 menus
    *
    * @see \App\Http\Transformers\SelectlistTransformer
    */
    public function selectlist(Request $request) : array
    {
        $consumables = Consumable::select([
            'consumables.id',
            'consumables.name',
        ]);

        if ($request->filled('search')) {
            $consumables = $consumables->where('consumables.name', 'LIKE', '%'.$request->get('search').'%');
        }

        $consumables = $consumables->orderBy('name', 'ASC')->paginate(50);

        return (new SelectlistTransformer)->transformSelectlist($consumables);
    }
}
