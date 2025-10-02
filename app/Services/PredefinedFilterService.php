<?php

namespace App\Services;

use DB;
use Exception;
use Throwable;
use App\Models\PredefinedFilter;
use App\Services\PredefinedFilterPermissionService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class PredefinedFilterService
{

    protected PredefinedFilterPermissionService $predefinedFilterPermissionService;

    public function __construct(PredefinedFilterPermissionService $predefinedFilterPermissionService)
    {
        $this->predefinedFilterPermissionService = $predefinedFilterPermissionService;
    }

    public function getAllViewableFilters(): Collection
    {
        $user = Auth::user();

        $response = PredefinedFilter::with('permissionGroups')
            ->orderBy('name')
            ->get(['id', 'name', 'created_by', 'is_public'])
            ->filter(function ($filter) use ($user) {
                if ($filter->created_by == $user->id) {
                    return true;
                }

                if ($filter->is_public && $filter->userHasPermission($user, 'view')) {
                    return true;
                }

                return false;
            })->values();

        return $response;
    }

    //TODO different Naming because it does more than only get a filter by ID
    //TODO discuss because there is the built-in with() ['predefinedFilter::with('permissionGroups')->find(id)']
    public function getFilterById(int $id, bool $include_predefined_filter_groups = true)
    {
        $predefinedFilter = PredefinedFilter::find($id);
        if($include_predefined_filter_groups && $predefinedFilter) {
            $permissions = $this->predefinedFilterPermissionService->getPermissionsByPredefinedFilterId($id);
            $predefinedFilter['permissions'] = $permissions;
        }
        return $predefinedFilter;
    }

    public function canUserViewFilter($filter): bool
    {
        $user = Auth::user();
        return $filter->created_by == $user->id ||
            ($filter->is_public && $filter->userHasPermission($user, 'view'));
    }

    public function createFilter($validated): PredefinedFilter
    {
        $filter_create_response = PredefinedFilter::create([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
            'created_by' => Auth::id(),
            'is_public' => $validated['is_public'] ?? 0,
        ]);

        // Set permissions
        if (array_key_exists('permissions', $validated)) {
            foreach ($validated['permissions'] as $permission) {
                $permission['predefined_filter_id'] = $filter_create_response->id;
                $this->predefinedFilterPermissionService->store($permission);
            }
        }
        return $filter_create_response;
    }

    public function updateFilter(PredefinedFilter $filter, array $validated): PredefinedFilter
    {
        $filter->fill([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
            'is_public' => $validated['is_public'],
        ]);

        $filter->save();

        // Update permissions
        if (array_key_exists('permissions', $validated)) {
            $currently_set_permssions = $this->predefinedFilterPermissionService->getPermissionsByPredefinedFilterId($filter->id);
            $new_permissions = $validated['permissions'];
            $permission_diff = $this->syncPermissions($currently_set_permssions->toArray(), $new_permissions);

            try {
                DB::transaction(function () use ($permission_diff, $filter) {
                    if (!empty($permission_diff['to_delete'])) {
                        foreach ($permission_diff['to_delete'] as $permission) {
                            $this->predefinedFilterPermissionService->deletePermissionByFilterId($permission['predefined_filter_id']);
                        }
                    }

                    if (!empty($permission_diff['to_add'])) {
                        foreach ($permission_diff['to_add'] as $permission) {
                            $permission['predefined_filter_id'] = $filter->id;
                            $this->predefinedFilterPermissionService->store($permission);
                        }
                    }
                });
            } catch (Throwable $e) {
                // If any exception occurs, the transaction is automatically rolled back.
                throw new Exception($e->getMessage());
                //abort(500,message: "Something went wrong");
            }
        }

        return $filter;
    }

    public function deleteFilter(PredefinedFilter $filter): ?bool
    {
        return $filter->delete();
    }

    public function selectList(Request $request): LengthAwarePaginator
    {
        $user = Auth::user();

        $filters = PredefinedFilter::with("permissionGroups")
            ->orderBy('name')
            ->get(['id', 'name', 'created_by', 'is_public']);

        $viewableFilters = $filters->filter(function ($filter) use ($user) {
            if ($filter->created_by == $user->id) {
                return true;
            }

            if ($filter->is_public && $filter->userHasPermission($user, 'view')) {
                return true;
            }

            return false;
        })->pluck('id');

        $query = PredefinedFilter::select(['id', 'name'])
            ->whereIn('id', $viewableFilters);

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $paginated = $query->orderBy('name')->paginate(50);

        foreach ($paginated as $item) {
            $item->use_text = $item->name;
        }

        return $paginated;
    }

private function syncPermissions($currentPermissions, $newPermissions): array
{
    $toAdd = array_udiff(
        $newPermissions,
        $currentPermissions,
        function ($a, $b) {
            return $a['permission_group_id'] <=> $b['permission_group_id'];
        }
    );

    $toDelete = array_udiff(
        $currentPermissions,
        $newPermissions,
        function ($a, $b) {
            return $a['permission_group_id'] <=> $b['permission_group_id'];
        }
    );

    return [
        'to_add' => $toAdd,
        'to_delete' => $toDelete
    ];
}


}
