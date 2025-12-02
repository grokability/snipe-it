<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use Illuminate\Support\Collection;
use App\Models\Setting;

class PredefinedFiltersTransformer
{
    public function transformPredefinedFilters(Collection $filters, $total)
    {
        $array = [];
        foreach ($filters as $filter) {
            $array[] = self::transformPredefinedFilter($filter);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformPredefinedFilter($filter)
    {

        $array = [
            'id' => (int) $filter->id,
            'name'=> e($filter->name),
            'filter_data' => json_decode($filter->filter_data),
            'is_public' => (bool)$filter->is_public,
            'object_type' => e($filter->object_type),
            'created_by' => $filter->createdBy ? [
                'id' => (int) $filter->createdBy->id,
                'name' => $filter->createdBy->present()->nameUrl(),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($filter->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($filter->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($filter->deleted_at, 'datetime'),
        ];

        if ($filter->relationLoaded('permissionGroups')) {

            $permissionGroups = $filter->permissionGroups;

            $groups = [
                'total' => $permissionGroups->count(),
                'rows' => []
            ];

            foreach ($permissionGroups as $group) {
                $groups['rows'][] = [
                    'id' => $group->id,
                    'name' => $group->name
                ];
            }
            $array['groups'] = $groups;
        } else {
            $array['groups'] = null;
        }

        $permissionsArray['available_actions'] = [
            'update' => $filter->created_by === auth()->id() || $filter->userHasPermission(auth()->user(), 'edit'),
            'delete' => $filter->created_by === auth()->id() || $filter->userHasPermission(auth()->user(), 'delete')
        ];
        return $array += $permissionsArray;
    }
}
