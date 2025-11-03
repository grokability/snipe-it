<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\PredefinedFiltersTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\PredefinedFilter;
use App\Services\PredefinedFilterService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Log;

class PredefinedFilterController extends Controller
{
    protected PredefinedFilterService $service;

    public function __construct(PredefinedFilterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $filters = $this->service->getAllViewableFilters();
        return (new PredefinedFiltersTransformer)->transformPredefinedFilters($filters, $filters->count());
    }

    public function show(int $id)
    {
        $filter = $this->service->getFilterById($id);
        
        if (!$filter) {
            return response()->json(['message' => trans('admin/predefinedFilters/message.does_not_exist')], 404);
        }

        if ($filter->userHasPermission(Auth::user(), 'view')){
            return response()->json($filter->toArray());
        }

        return response()->json(['message' => trans('admin/predefinedFilters/message.show.not_allowed')], 403);
    }

    public function store(Request $request): JsonResponse | array
    {

        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'filter_data' => 'required|array',
            'is_public' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse(422, null, $validator->errors()),422);
        }
        
        $validated = $validator->validated();

        if (!empty($validated['is_public']) && !$user->hasAccess('predefinedFilter.create')) {
            return response()->json(['message' => trans('admin/predefinedFilters/message.create.not_allowed')], 403);
        }

        $filter = $this->service->createFilter($validated);

        return response()->json([
            'message' => trans('admin/predefinedFilters/message.create.success'),
            'filter_data' => $filter,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json(['message' => trans('admin/predefinedFilters/message.does_not_exist')], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'filter_data' => 'required|array',
            'is_public' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse(422, null, $validator->errors()),422);
        }
        
        $validated = $validator->validated();
        
        $newIsPublic = $validated['is_public'] ?? $filter->is_public;
        $currentIsPublic = $filter->is_public;

        if (!$filter->userHasPermission($user, 'edit')){
            return response()->json(['message' => trans('admin/predefinedFilters/message.not_allowed_to_edit')], 403);
        }

        //create permission
        if ((!$currentIsPublic && $newIsPublic) && !$filter->userHasPermission($user, 'create')){
            return response()->json(['message' => trans('admin/predefinedFilters/message.update.not_allowed_to_change_isPublic')], 403);
        }

        $updated = $this->service->updateFilter($filter, $validated);

        return response()->json([
            'message' => trans('admin/predefinedFilters/message.update.success'),
            'filter_data' => $updated,
        ]);
    }
    public function destroy(Request $request, int $id)
    {
        $user = auth()->user();
        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json(['message' => trans('admin/predefinedFilters/message.does_not_exist')], 404);
        }

        if ($filter->userHasPermission($user, 'delete')) {
            $this->service->deleteFilter($filter);
            return response()->json(['message' => trans('admin/predefinedFilters/message.delete.success')]);
        }

        return response()->json(['message' => trans('admin/predefinedFilters/message.delete.not_allowed_to_delete')], 403);
    }

    public function selectlist(Request $request)
    {
        $this->authorize('view.selectlists');
        
        $filters = $this->service->selectList($request);

        return (new SelectlistTransformer)->transformSelectlist($filters);
    }

    // Optional: You can refactor the permission syncing methods similarly
}