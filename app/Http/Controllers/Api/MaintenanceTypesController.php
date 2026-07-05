<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Transformers\MaintenanceTypesTransformer;
use App\Models\MaintenanceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceTypesController extends Controller
{
    public function index(FilterRequest $request): JsonResponse|array
    {
        $this->authorize('view', MaintenanceType::class);

        $types = MaintenanceType::select(['id', 'name', 'created_at', 'updated_at', 'deleted_at']);

        if ($request->input('deleted') == 'true') {
            $types->onlyTrashed();
        }

        if ($request->filled('search')) {
            $types->where('name', 'LIKE', '%'.$request->input('search').'%');
        }

        if ($request->filled('name')) {
            $types->where('name', '=', $request->input('name'));
        }

        $offset = ($request->input('offset') > $types->count()) ? $types->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), ['id', 'name', 'created_at', 'updated_at']) ? $request->input('sort') : 'name';

        $total = $types->count();
        $types = $types->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        return (new MaintenanceTypesTransformer)->transformMaintenanceTypes($types, $total);
    }

    public function show(MaintenanceType $maintenanceType): JsonResponse|array
    {
        $this->authorize('view', $maintenanceType);

        return (new MaintenanceTypesTransformer)->transformMaintenanceType($maintenanceType);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', MaintenanceType::class);

        $type = new MaintenanceType;
        $type->name = $request->input('name');
        $type->created_by = auth()->id();

        if ($type->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new MaintenanceTypesTransformer)->transformMaintenanceType($type), trans('admin/maintenance_types/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $type->getErrors()));
    }

    public function update(Request $request, MaintenanceType $maintenanceType): JsonResponse
    {
        $this->authorize('update', $maintenanceType);

        $maintenanceType->name = $request->input('name');

        if ($maintenanceType->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new MaintenanceTypesTransformer)->transformMaintenanceType($maintenanceType), trans('admin/maintenance_types/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $maintenanceType->getErrors()));
    }

    public function destroy(MaintenanceType $maintenanceType): JsonResponse
    {
        $this->authorize('delete', $maintenanceType);

        $maintenanceType->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/maintenance_types/message.delete.success')));
    }
}
