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

    public function getFilterWithOptionalPermissionsById(int $id, bool $include_predefined_filter_groups = true)
    {
        $predefinedFilter = PredefinedFilter::find($id);
        if ($include_predefined_filter_groups && $predefinedFilter) {
            $permissions = $this->predefinedFilterPermissionService->getPermissionsByPredefinedFilterId($id);
            $predefinedFilter['permissions'] = $permissions;
        }
        
        if (!$predefinedFilter){
            return null;
        }

        $filters = $predefinedFilter->filter_data;

        foreach ($filters as &$filter) {


            if (!empty($filter['value']) && is_array($filter['value']) && is_int($filter['value'][0])) {
                $values =[];
                foreach ($filter['value'] as $valueId){
                    switch ($filter['field']) {
                        case 'company':
                            $company = Company::find($valueId);
                            if ($company) {
                                $values[] = [
                                    'id' => $company->id,
                                    'name' => $company->name,
                                ];
                            }
                            break;
                        case 'model':
                            $model = AssetModel::find($valueId);
                            if ($model) {
                                $values[] = [
                                    'id' => $model->id,
                                    'name' => $model->name,
                                ];
                            }
                            break;
                        case 'category':
                            $category = Category::find($valueId);
                            if ($category) {
                                $values[] = [
                                    'id' => $category->id,
                                    'name' => $category->name,
                                ];
                            }
                            break;
                        case 'statuslabel':
                            $status = Statuslabel::find($valueId);
                            if ($status) {
                                $values[] = [
                                    'id' => $status->id,
                                    'name' => $status->name,
                                ];
                            }
                            break;
                        case 'location':
                        case 'default_location':
                            $location = Location::find($valueId);
                            if ($location) {
                                $values[] = [
                                    'id' => $location->id,
                                    'name' => $location->name,
                                ];
                            }
                            break;
                        case 'manufacturer':
                            $manufacturer = Manufacturer::find($valueId);
                            if ($manufacturer) {
                                $values[] = [
                                    'id' => $manufacturer->id,
                                    'name' => $manufacturer->name,
                                ];
                            }
                            break;
                        case 'supplier':
                            $supplier = Supplier::find($valueId);
                            if ($supplier) {
                                $values[] = [
                                    'id' => $supplier->id,
                                    'name' => $supplier->name,
                                ];
                            }
                            break;
                        default:
                            break;
                    }
                }

            $filter['value'] = $values;
            $predefinedFilter->filter_data = $filters;

            }
        }
        return $predefinedFilter;
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
        if (array_key_exists('permissions', $validated) && count($validated['permissions']) > 0) {
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
