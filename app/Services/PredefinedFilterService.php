<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Company;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use DB;
use Exception;
use Throwable;
use App\Models\PredefinedFilter;
use App\Services\FilterService\FilterService;
use App\Services\PredefinedFilterPermissionService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PredefinedFilterService
{

    protected PredefinedFilterPermissionService $predefinedFilterPermissionService;

    public function __construct(PredefinedFilterPermissionService $predefinedFilterPermissionService)
    {
        $this->predefinedFilterPermissionService = $predefinedFilterPermissionService;
    }

    protected ?FilterService $filterService = null;

    public function filterService(): FilterService
    {
        return $this->filterService ??= app(FilterService::class);
    }

    public function getAllViewableFilters(): Collection
    {
        $user = Auth::user();

        return PredefinedFilter::with('permissionGroups')
            ->orderBy('name')
            ->get(['id', 'name', 'created_by', 'is_public'])
            ->filter(function ($filter) use ($user) {

                if ($filter->userHasPermission($user, 'view')) {
                    return true;
                }

                return false;
            })->values();
    }

    public function getFilterWithOptionalPermissionsById(int $id, bool $includePredefinedFilterGroups = true)
    {
        $predefinedFilter = PredefinedFilter::find($id);
        if ($includePredefinedFilterGroups && $predefinedFilter) {
            $permissions = $this->predefinedFilterPermissionService->getPermissionsByPredefinedFilterId($id);
            $predefinedFilter['permissions'] = $permissions;
        }

        return $predefinedFilter;
    }
    
    public function getFilterWithIdAndNameValues(int $id)
    {
        $predefinedFilter = $this->getFilterWithOptionalPermissionsById($id);

        if (!$predefinedFilter){
            return null;
        }

        $fieldsToLookup = [
            'company',
            'model',
            'category',
            'status_label',
            'location',
            'rtd_location',
            'manufacturer',
            'supplier'
        ];
    
        $filters = $predefinedFilter->filter_data;
    
        foreach ($filters as &$filter) {
    
            $model = null;

            if (isset($filter['field']) && !in_array($filter['field'], $fieldsToLookup)){
                continue;
            }
                
            if (!empty($filter['value']) && is_array($filter['value']) && is_int($filter['value'][0])) {
                    
                $values =[];
                    
                foreach ($filter['value'] as $valueId){
                    switch ($filter['field']) {
                        case 'company':
                            $model = Company::find($valueId);
                            break;
                        case 'model':
                            $model = AssetModel::find($valueId);
                            break;
                        case 'category':
                            $model = Category::find($valueId);
                            break;
                        case 'status_label':
                            $model = Statuslabel::find($valueId);
                            break;
                        case 'location':
                        case 'rtd_location':
                            $model = Location::find($valueId);
                            break;
                        case 'manufacturer':
                            $model = Manufacturer::find($valueId);
                            break;
                        case 'supplier':
                            $model = Supplier::find($valueId);
                            break;
                        default:
                            break;
                    }
                    if ($model){
                        $values[] = [
                            'id'    => $model->id,
                            'name'  => $model->name
                        ];
                    }
                    $filter['value'] = $values;
                }
                    
            }
        }
        $predefinedFilter->filter_data = $filters;
        return $predefinedFilter;
    }

    public function createFilter($validated): PredefinedFilter
    {
        $createResponse = PredefinedFilter::create([
            'name' => $validated['name'],
            'filter_data' => $validated['filter_data'],
            'created_by' => Auth::id(),
            'is_public' => $validated['is_public'] ?? 0,
        ]);

        // Set permissions
        if (array_key_exists('permissions', $validated) && count($validated['permissions']) > 0) {
            foreach ($validated['permissions'] as $permission) {
                $permission['predefined_filter_id'] = $createResponse->id;
                $this->predefinedFilterPermissionService->store($permission);
            }
        }

        return $createResponse;
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
            $currentlySetPermssions = $this->predefinedFilterPermissionService->getPermissionsByPredefinedFilterId($filter->id);
            $newPermissions = $validated['permissions'];
            $permission_diff = $this->syncPermissions($currentlySetPermssions->toArray(), $newPermissions);

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
            }
        }

        return $filter;
    }

    public function deleteFilter(PredefinedFilter $filter): ?bool
    {
        return $filter->delete();
    }

    public function selectList(Request $request, bool $visibilityInName = false): LengthAwarePaginator
    {
        $user = Auth::user();

        $filters = PredefinedFilter::with("permissionGroups")
            ->orderBy('name')
            ->get(['id', 'name', 'created_by', 'is_public']);

        $viewableFilters = $filters->filter(function ($filter) use ($user) {
            if ($filter->userHasPermission($user, 'view')) {
                return true;
            }

            return false;
        })->pluck('id');

        $query = PredefinedFilter::select(['id', 'name', 'is_public'])
            ->whereIn('id', $viewableFilters);

        if ($request->filled('search')) {
            $search = trim($request->get('search', ''));
            $upper = strtoupper($search);

            $privateTag = strtoupper(trans('general.private')) . ':';
            $publicTag = strtoupper(trans('general.public')) . ':';

            if (str_starts_with($upper, 'PRIVATE:') || str_starts_with($upper, $privateTag)) {
                $query->where('is_public', 0);
                $search = preg_replace('/^(PRIVATE:|' . preg_quote($privateTag, '/') . ')/i', '', $search);
            } elseif (str_starts_with($upper, 'PUBLIC:') || str_starts_with($upper, $publicTag)) {
                $query->where('is_public', 1);
                $search = preg_replace('/^(PUBLIC:|' . preg_quote($publicTag, '/') . ')/i', '', $search);
            }

            $query->where('name', 'LIKE', '%' . trim($search) . '%');
        }

        $paginated = $query->orderBy('name')->paginate(50);

        foreach ($paginated as $item) {
            if ($visibilityInName === true) {
                $item->use_text = $item->name . ' (' . $this->getVisibilityAsLocalizedString($item->is_public) . ')';
            } else {
                $item->use_text = $item->name;
            }
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

    private function getVisibilityAsLocalizedString(bool $isPublic): string
    {
        return $isPublic == true ? trans('general.public') : trans('general.private');
    }
}
