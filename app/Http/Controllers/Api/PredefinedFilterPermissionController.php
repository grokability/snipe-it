<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PredefinedFilter;
use App\Models\PredefinedFilterPermission;
use App\Services\PredefinedFilterPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredefinedFilterPermissionController extends Controller
{
    protected PredefinedFilterPermissionService $service;

    public function __construct(PredefinedFilterPermissionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request):JsonResponse
    {
        $this -> authorize('view', PredefinedFilter::class);

        $permissions = PredefinedFilterPermission::with(['group', 'filter'])->get();

        return response()->json([
            'total' => $permissions->count(),
            'row' => $permissions
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('edit', PredefinedFilter::class);

        $model = new PredefinedFilterPermission();
        $validated = $request->validate($model->getRules());

        $filter = PredefinedFilter::findOrFail($validated['predefined_filter_id']);
        $this->authorize('update', $filter);

        // Granular Permission
        if ($filter->created_by !== $request->user()->id && !$filter->userHasPermission($request->user(), 'edit')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permission = $this->service->store($validated);

        return response()->json([
            'message' => __('admin/reports/message.create.success'),
            'data' => $permission,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorize('view', PredefinedFilter::class);

        $permission = $this->service->show($id);

        $filter = $permission->filter;

        if (!$filter) {
            return response()->json(['message' => trans('NotFound')], 404);
        }

        $this->authorize('view', $filter);

        return response()->json($permission);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('delete', PredefinedFilterPermission::class);

        $permission = PredefinedFilterPermission::findOrFail($id);
        $this->authorize('delete', $permission->filter);

        $this->service->delete($id);

        return response()->json([
            'message' => __('admin/reports/message.delete.success'),
        ], 204);
    }
}