<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PredefinedFilter;
use App\Models\PredefinedFilterPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredefinedFilterPermissionController extends Controller
{
    public function store (Request $request) : JsonResponse
    {
        // Global Permissions
        $this->authorize('edit', PredefinedFilter::class);

        
        $model = new PredefinedFilterPermission();
        
        $validated = $request->validate($model->getRules());
        
        $filter = PredefinedFilter::findOrFail($validated['predefined_filter_id']);
        
        $this->authorize('update', $filter);
        
        
        // Granular Permission
        if ($filter->created_by !== $request->user()->id && !$filter->userHasPermission($request->user(), 'edit')) {
            return response()->json(['error' => 'Unauthorized'], 403); // TODO check for lang
        }

        $permission = PredefinedFilterPermission::updateOrCreate(
            [
                'predefined_filter_id'  => $validated['predefined_filter_id'],
                'permission_group_id'   => $validated['permission_group_id'],
            ],
            array_merge($validated,[
                'created_by' => $request->user()->id,
            ])
        );

        return response()->json([
            'message' => __('admin/reports/message.create.success'),
            'data'  => $permission
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorize('view', PredefinedFilter::class);

        $permission = PredefinedFilterPermission::with('filter')->findOrFail($id);

        $filter = $permission->filter;

        if (!$filter) 
        {
            return response()->json(['message' => trans('NotFound')],404);
        }

        $this->authorize('view', $filter);

        return response()->json($permission);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('delete', PredefinedFilterPermission::class);

        $permission = PredefinedFilterPermission::findOrFail($id);
        $filter = $permission->filter;

        $this->authorize('delete', $filter);

        $permission->delete();

        return response()->json([
            'message' => __('admin/reports/message.delete.success'),
        ],204);
    }
}