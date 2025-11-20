<?php

namespace App\Services;

use Exception;
use App\Models\PredefinedFilterPermission;
use Illuminate\Support\Facades\Auth;

class PredefinedFilterPermissionService
{
    public function store(array $validated): PredefinedFilterPermission
    {
        $userId = Auth::id();

        $permission = new PredefinedFilterPermission();
        $permission->predefined_filter_id = $validated['predefined_filter_id'];
        $permission->permission_group_id = $validated['permission_group_id'];
        $permission->created_by = $userId;
        if (!$permission->save()) {
            throw new Exception($permission->getErrors());
        }

        return $permission;

    }

    public function show(int $id): PredefinedFilterPermission
    {
        return PredefinedFilterPermission::with('filter')->findOrFail($id)->distinct();
    }

    public function delete(int $id): void
    {
        $permission = PredefinedFilterPermission::findOrFail($id);
        $permission->delete();
    }

    public function deletePermissionByFilterId($filter_id): void
    {
        $permissions = PredefinedFilterPermission::where('predefined_filter_id', '=', $filter_id)->get();
        foreach($permissions as $permission) {
            $permission->delete();
        }
    }

    public function getPermissionsByPredefinedFilterId(int $filter_id)
    {
        return PredefinedFilterPermission::where('predefined_filter_id', '=', $filter_id)->get();
    }
}
