<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Transformers\MaintenanceTypesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\MaintenanceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceTypesController extends Controller
{
    public function index(FilterRequest $request): JsonResponse|array
    {
        $this->authorize('view', MaintenanceType::class);

        $types = MaintenanceType::select(['id', 'name', 'tag_color', 'created_by', 'created_at', 'updated_at', 'deleted_at'])
            ->with('adminuser')
            ->withCount('maintenances as maintenances_count');

        if ($request->input('deleted') == 'true') {
            $types->onlyTrashed();
        }

        if ($request->filled('filter') || $request->filled('search')) {
            $types->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('name')) {
            $types->where('name', '=', $request->input('name'));
        }

        $total = $types->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), ['id', 'name', 'maintenances_count', 'created_by', 'created_at', 'updated_at']) ? $request->input('sort') : 'name';

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
        $type->tag_color = $request->input('tag_color');
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
        $maintenanceType->tag_color = $request->input('tag_color');

        if ($maintenanceType->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new MaintenanceTypesTransformer)->transformMaintenanceType($maintenanceType), trans('admin/maintenance_types/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $maintenanceType->getErrors()));
    }

    public function destroy(MaintenanceType $maintenanceType): JsonResponse
    {
        $this->authorize('delete', $maintenanceType);

        // Refuse the delete if any maintenance rows still reference this
        // type. Enforced here as well as at the UI layer so the API
        // doesn't strand orphaned maintenance_type_id values pointing
        // at a soft-deleted row. Message uses the shared
        // general.bulk_delete_associations.assoc_maintenances key so
        // the reason surfaces the current in-use count.
        if (! $maintenanceType->isDeletable()) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('general.bulk_delete_associations.assoc_maintenances', [
                    'item_name' => $maintenanceType->name,
                    'maintenance_count' => $maintenanceType->maintenances()->count(),
                    'item' => trans('admin/maintenance_types/general.maintenance_type'),
                ])),
                409,
            );
        }

        $maintenanceType->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/maintenance_types/message.delete.success')));
    }

    /**
     * select2-shaped list of maintenance types for the advanced-search
     * modal's maintenance_type_details picker.
     */
    public function selectlist(Request $request): array
    {
        $this->authorize('view.selectlists');

        $types = MaintenanceType::select(['id', 'name', 'tag_color']);

        if ($request->filled('search')) {
            $types = $types->where('name', 'LIKE', '%'.$request->input('search').'%');
        }

        $types = $types->orderBy('name', 'ASC')->paginate(50);

        foreach ($types as $type) {
            $type->use_text = $type->name;
            $type->use_image = null;
        }

        return (new SelectlistTransformer)->transformSelectlist($types);
    }
}
