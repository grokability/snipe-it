<?php

namespace App\Services;

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

        return PredefinedFilter::with('permissionGroups')
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
    }

    public function getFilterById(int $id)
    {
        return PredefinedFilter::find($id);
    }

    public function canUserViewFilter($filter): bool
    {
        $user = Auth::user();
        return $filter->created_by == $user->id ||
            ($filter->is_public && $filter->userHasPermission($user, 'view'));
    }

    public function createFilter($validated): PredefinedFilter
    {
        //dump($validated);
        $filter_create_response =  PredefinedFilter::create([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
            'created_by' => Auth::id(),
            'is_public' => $validated['is_public'] ?? 0,
        ]);

        if (array_key_exists('permissions', $validated)) {
            //dump($validated->permissions);
            foreach ($validated['permissions'] as $permission) {
                $permission['predefined_filter_id'] = $filter_create_response->id;
                //dump($permission);
                $this->predefinedFilterPermissionService->store($permission);
            }
        }


        dump($filter_create_response);
        dump($this->predefinedFilterPermissionService->get($filter_create_response->id));
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

        return $filter;
    }

    public function deleteFilter(PredefinedFilter $filter): void
    {
        $filter->delete();
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
}
