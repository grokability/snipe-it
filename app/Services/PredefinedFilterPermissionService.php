<?php

namespace App\Services;

use App\Models\PredefinedFilter;
use App\Models\PredefinedFilterPermission;
use Illuminate\Support\Facades\Auth;

class PredefinedFilterPermissionService
{
    public function store(array $validated): PredefinedFilterPermission
    {
        \DB::enableQueryLog();
        $userId = Auth::id();

        $permission = new PredefinedFilterPermission();
        $permission->predefined_filter_id = $validated['predefined_filter_id'];
        $permission->permission_group_id = $validated['permission_group_id'];
        $permission->created_by = $userId;

        if (!$permission->save()) {
            dd($permission->getErrors());
        }

        return $permission;

    }

    public function show(int $id): PredefinedFilterPermission
    {
        return PredefinedFilterPermission::with('filter')->findOrFail($id);
    }

    public function delete(int $id): void
    {
        $permission = PredefinedFilterPermission::findOrFail($id);
        $permission->delete();
    }

    public function get(int $id)
    {
        return PredefinedFilterPermission::with('filter')->get();
    }
}
