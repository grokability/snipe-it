<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Depreciable;
use App\Models\Depreciation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class DepreciationsTransformer
{
    public function transformDepreciations(Collection $depreciations, $total)
    {
        $array = [];
        foreach ($depreciations as $depreciation) {
            $array[] = self::transformDepreciation($depreciation);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformDepreciation(Depreciation $depreciation)
    {
        $array = [
            'id' => (int) $depreciation->id,
            'name' => e($depreciation->name),
            'months' => trans_choice('general.months_plural', $depreciation->months),
            'depreciation_min' => $depreciation->depreciation_type === 'percent' ? $depreciation->depreciation_min.'%' : $depreciation->depreciation_min,
            'assets_count' => ($depreciation->assets_count > 0) ? (int) $depreciation->assets_count : 0,
            'models_count' => ($depreciation->models_count > 0) ? (int) $depreciation->models_count : 0,
            'licenses_count' => ($depreciation->licenses_count > 0) ? (int) $depreciation->licenses_count : 0,
            'created_by' => ($depreciation->adminuser) ? [
                'id' => (int) $depreciation->adminuser->id,
                'name'=> e($depreciation->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($depreciation->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($depreciation->updated_at, 'datetime')
        ];

        $permissions_array['available_actions'] = [
            'update' => Gate::allows('update', Depreciation::class),
            'delete' => Gate::allows('delete', Depreciation::class),
        ];

        $array += $permissions_array;

        return $array;
    }
}