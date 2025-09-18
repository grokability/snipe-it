<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PredefinedFilter;
use App\Http\Transformers\PredefinedFiltersTransformer;
use App\Http\Transformers\SelectlistTransformer;


class PredefinedFilterController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get all Filters
        $filters = PredefinedFilter::with("permissionGroups")
            ->orderBy('name')
            ->get(['id', 'name', 'created_by', 'is_public']);


        // Permission per filter

        $viewableFilters = $filters->filter(function ($filter) use ($user) {
            //own filter
            if ($filter->created_by == $user->id) {
                return true;
            }

            // public and view permission
            if ($filter->is_public && $filter->userHasPermission($user, 'view')) {
                return true;
            }

            return false;
        })->values();

        $transformer = new PredefinedFiltersTransformer();

        return $transformer->transformPredefinedFilters($viewableFilters, $viewableFilters->count());
    }

    public function show(Request $request, int $id)
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json([
                'message' => trans('admin/predefinedFilters/message.does_not_exist'),
            ], 404);
        }

        if ($filter->created_by == $user->id) {
            return response()->json($filter->toArray());
        }

        if ($filter->is_public && $filter->userHasPermission($user, 'view')) {
            return response()->json($filter->toArray());
        }

        return response()->json([
            'message' => trans('admin/predefinedFilters/message.show.not_allowed'),
        ], 403);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $rules = (new PredefinedFilter)->getRules();
        $validated = $request->validate($rules);

        if (!empty($validated['is_public'] ?? false)) {
            if (!$user->hasAccess('predefinedFilter.create')) {
                return response()->json([
                    'message' => trans('admin/predefinedFilters/message.create.not_allowed'),
                ], 403);
            }
        }

        if (!empty($validated['is_public'] ?? false)) {
            if (!$user->hasAccess('predefinedFilter.create')) {
                return response()->json([
                    'message' => trans('admin/predefinedFilters/message.create.not_allowed'),
                ], 403);
            }
        }

        //dump($request);
        $predefined_filter = PredefinedFilter::create([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
            'created_by' => $user->id,
            'is_public' => $validated['is_public'] ?? 0,
        ]);

        return response()->json([
            'message' => trans('admin/predefinedFilters/message.create.success'),
            'filter_data' => $predefined_filter
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json([
                'message' => ('admin/predefinedFilters/message.does_not_exist'),
            ], 404);
        }

        $rules = (new PredefinedFilter)->getRules();
        $validated = $request->validate($rules);

        $currentIsPublic = $filter->is_public;
        $newIsPublic = $validated['is_public'];

        if ($filter->created_by === $user->id) {
            if (!$currentIsPublic && $newIsPublic) {
                // private -> public requires create permission
                if (!$user->hasAccess('predefinedFilter.create')) {
                    return response()->json([
                        'message' => trans('admin/predefinedFilters/message.update.not_allowed_to_change_isPublic'),
                    ], 403);
                }
            }
        } elseif ($currentIsPublic) {
            if (!$filter->userHasPermission($user, 'update')) {
                return response()->json([
                    'message' => trans('admin/predefinedFilters/message.not_allowed_to_edit'),
                ], 403);
            }
        } else {
            return response()->json([
                'message' => trans('admin/predefinedFilters/message.not_allowed_to_edit'),
            ], 403);
        }

        $filter->name = $validated['name'];
        $filter->filter_data = $validated['filter_data'];
        $filter->is_public = $newIsPublic;
        dump($filter->toRawSql());
        $filter->update();
        return response()->json([
            'message' => trans('admin/predefinedFilters/message.update.success'),
            'filter_data' => $filter,
        ], 200);
    }

    public function destroy(Request $request, int $id)
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json([
                'message' => trans('admin/predefinedFilters/message.does_not_exist'),
            ], 404);
        }

        if ($filter->created_by === $user->id) {
            $filter->delete();
        } elseif ($filter->is_public) {
            if (!$filter->userHasPermission($user, 'destroy')) {
                return response()->json([
                    'message' => trans('admin/predefinedFilters/message.not_allowed_to_delete'),
                ], 403);
            }
            $filter->delete();
        } else {

            return response()->json([
                'message' => trans('admin/predefinedFilters/message.delete.not_allowed_to_delete'),
            ], 403);
        }

        return response()->json([
            'message' => trans('admin/predefinedFilters/message.delete.success'),
        ], 200);
    }

    // permission endpoints | atm not used
    public function syncPermissionGroups(Request $request, int $id)
    {
        $user = auth()->user();

        $filter = PredefinedFilter::findOrFail($id);

        // Authorization check (only creator or someone with update rights)
        if ($filter->created_by !== $user->id && !$filter->userHasPermission($user, 'update')) {
            return response()->json([
                'message' => trans('admin/predefinedFilters/message.update.not_allowed_to_edit'),
            ], 403);
        }

        $groupIds = $request->input('group_ids', []);

        $filter->permissionGroups()->sync($groupIds);

        return response()->json([
            'message' => 'Permission groups synced successfully.',
        ], 200);
    }

    public function attachPermissionsGroup(Request $request, int $id)
    {
        // possibly not needed
        $user = auth()->user();

        $filter = PredefinedFilter::findOrFail($id);

        if ($filter->created_by !== $user->id && !$filter->userHasPermission($user, 'update')) {
            return response()->json([
                'message' => trans('admin/predefinedFilters/message.update.not_allowed_to_edit'),
            ], 403);
        }

        $groupId = $request->input('group_id');

        $filter->permissionGroups()->attach($groupId);

        return response()->json([
            'message' => 'Permission group attached successfully.',
        ], 200);
    }

    public function detachPermissionsGroups(Request $request, int $id)
    {
        // possibly not needed
        $user = auth()->user();

        $filter = PredefinedFilter::findOrFail($id);

        if ($filter->created_by !== $user->id && !$filter->userHasPermission($user, 'update')) {
            return response()->json([
                'message' => trans('admin/predefinedFilters/message.update.not_allowed_to_edit'),
            ], 403);
        }

        $groupId = $request->input('group_id');

        $filter->permissionGroups()->detach($groupId);

        return response()->json([
            'message' => 'Permission group detached successfully.',
        ], 200);
    }

    public function selectlist(Request $request)
    {
        $this->authorize('view.selectlists');
        $predefinedFilters = PredefinedFilter::select([
            'id',
            'name',
        ]);

        if ($request->filled('search')) {
            $predefinedFilters = $predefinedFilters->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $predefinedFilters = $predefinedFilters->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($predefinedFilters as $predefinedFiler) {
            $predefinedFiler->use_text = $predefinedFiler->name;
            //$manufacturer->use_image = ($manufacturer->image) ? Storage::disk('public')->url('manufacturers/'.$manufacturer->image, $manufacturer->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($predefinedFilters);
    }
}