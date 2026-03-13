<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\DepreciationsTransformer;
use App\Models\Depreciation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepreciationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', Depreciation::class);
        $allowed_columns = [
            'id',
            'name',
            'months',
            'depreciation_min',
            'depreciation_type',
            'created_at',
            'assets_count',
            'models_count',
            'licenses_count',
        ];

        $depreciations = Depreciation::select('id','name','months','depreciation_min','depreciation_type','created_at','updated_at', 'created_by')
            ->with('adminuser')
            ->withCount('assets as assets_count')
            ->withCount('models as models_count')
            ->withCount('licenses as licenses_count');

        if ($request->filled('search')) {
            $depreciations = $depreciations->TextSearch($request->input('search'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $depreciations->count()) ? $depreciations->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'created_by':
                $depreciations = $depreciations->OrderByCreatedBy($order);
                break;
            default:
                $depreciations = $depreciations->orderBy($column_sort, $order);
                break;
        }

        $total = $depreciations->count();
        $depreciations = $depreciations->skip($offset)->take($limit)->get();

        return (new DepreciationsTransformer)->transformDepreciations($depreciations, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request) : JsonResponse
    {
        $this->authorize('create', Depreciation::class);
        $depreciation = new Depreciation;
        $depreciation->fill($request->all());

        if ($depreciation->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $depreciation, trans('admin/depreciations/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $depreciation->getErrors()));
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
        $this->authorize('view', Depreciation::class);
        $depreciation = Depreciation::findOrFail($id);

        return (new DepreciationsTransformer)->transformDepreciation($depreciation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id) : JsonResponse
    {
        $this->authorize('update', Depreciation::class);
        $depreciation = Depreciation::findOrFail($id);
        $depreciation->fill($request->all());

        if ($depreciation->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $depreciation, trans('admin/depreciations/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $depreciation->getErrors()));
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
        $this->authorize('delete', Depreciation::class);
        $depreciation = Depreciation::withCount('models as models_count')->findOrFail($id);
        $this->authorize('delete', $depreciation);

        if ($depreciation->models_count > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', trans('admin/depreciations/message.assoc_users')));
        }

        $depreciation->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/depreciations/message.delete.success')));
    }
}
