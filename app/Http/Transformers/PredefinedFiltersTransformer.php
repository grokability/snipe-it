<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Labels\Label;
use App\Models\Labels\Sheet;
use App\Models\Labels\RectangleSheet;
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

        return (new DatatablesTransformer) -> transformDatatables($array, $total);
    }

    public function transformPredefinedFilter($filter)
    {
        $setting = Setting::getSettings();

        $array = [
            'id' => (int) $filter->id,
            'name'=> e($filter->name),
            'filter_data' => json_decode($filter->filter_data),
            'is_public' => (bool)$filter->is_public,
            'object_type' => e($filter->object_type),
            'created_by' => $filter->createdBy ? [
                'id' => (int) $filter-> createdBy ->id,
                'name'=> e($filter->createdBy->present()->fullName()),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($filter->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($filter->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($filter->deleted_at, 'datetime'),
        ];

        if ($filter->relationLoaded('permissionGroups')) {
            $array['permission_groups'] = $filter->permissionGroups->pluck('name')->toArray();
        }

        $permissions_array['available_actions'] = [
            'update' => $filter->created_by === auth()->id() || $filter->userHasPermission(auth()->user(), 'update'),
            'delete' => $filter->created_by === auth()->id() || $filter->userHasPermission(auth()->user(), 'destroy')
        ];
        $array += $permissions_array;
        return $array;
    }
}