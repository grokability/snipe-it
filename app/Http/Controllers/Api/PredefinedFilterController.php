<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PredefinedFilter;



class PredefinedFilterController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get all Filters
        $filters = PredefinedFilter::with("permissionGroups")
            ->orderBy('name')
            ->get(['id','name', 'created_by', 'is_public']);


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

        return response()->json($viewableFilters);
    }

    public function show(Request $request, int $id)
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json([
            'message' => __('admin/reports/message.NotFound'), // TODO check for lang
        ], 404);
        }

        if ($filter->created_by == $user->id) {
            return response()->json($filter->toArray());
        }

        if ($filter->is_public && $filter->userHasPermission($user,'view')) {
            return response()->json($filter->toArray());
        }

        return response()->json([
            'message' => __('admin/reports/message.NotAllowed'), // TODO check for lang
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
                    'message' => __('admin/reports/message.NotAllowed'), // TODO check for lang
            ], 403);}
        }

        $predefined_filter = PredefinedFilter::create([
            'name'=> $validated['name'],
            'filter_data'=> $validated['filter_data'],
            'created_by'=> $user->id,
            'is_public' => $validated['is_public'] ?? 0,
        ]);

        return response()->json([
            'message' => __('admin/reports/message.create.success'),
            'filter_data' => $predefined_filter
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();

        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json(['error' => 'Filter not found'], 404);
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
                    'message' => __('admin/reports/message.NotAllowedToChangePublicStatus'),
                ], 403);
            }
        }
        } elseif ($currentIsPublic) {
            if (!$filter->userHasPermission($user, 'update')) {
                return response()->json([
                    'message' => __('admin/reports/message.NotAllowed'),
                ], 403);
            }
        } else {
            return response()->json([
                'message' => __('admin/reports/message.NotAllowed'),
            ], 403);
        }

        $filter->name = $validated['name'];
        $filter->filter_data = $validated['filter_data'];
        $filter->is_public = $newIsPublic;
        $filter->save();

        return response()->json([
            'message' => __('admin/reports/message.update.success'),
            'filter_data' => $filter,
        ], 200);
    }
    
    public function destroy(Request $request, int $id)
    {
        $user = auth()->user();
        
        $filter = PredefinedFilter::find($id);

        if (!$filter) {
            return response()->json(['error' => 'Filter not found'], 404);
        }

        if ($filter->created_by === $user->id) {
            $filter->delete();
        } elseif ($filter->is_public) {
            if (!$filter->userHasPermission($user, 'destroy')) {
                return response()->json([
                    'message' => __('admin/reports/message.NotAllowed'),
                ], 403);
            }
            $filter->delete();
        } else {
        
            return response()->json([
                'message' => __('admin/reports/message.NotAllowed'),
            ], 403);
        }

        return response()->json([
            'message'=> __('admin/reports/message.delete.success'),
        ],200);
    }
}