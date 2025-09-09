<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Statuslabel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;

class StatuslabelsTransformer
{
    public function transformStatuslabels(Collection $statuslabels, $total)
    {
        $array = [];
        foreach ($statuslabels as $statuslabel) {
            $array[] = self::transformStatuslabel($statuslabel);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformStatuslabel(Statuslabel $statuslabel)
    {
        $array = [
            'id' => (int) $statuslabel->id,
            'name' => e($statuslabel->name),
            'type' => $statuslabel->getStatuslabelType(),
            'color' => ($statuslabel->color) ? e($statuslabel->color) : null,
            'show_in_nav' => ($statuslabel->show_in_nav == '1') ? true : false,
            'default_label' => ($statuslabel->default_label == '1') ? true : false,
            'assets_count' => (int) $statuslabel->assets_count,
            'notes' => e($statuslabel->notes),
            'created_by' => ($statuslabel->adminuser) ? [
                'id' => (int) $statuslabel->adminuser->id,
                'name'=> e($statuslabel->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($statuslabel->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($statuslabel->updated_at, 'datetime'),
        ];

        $permissions_array['available_actions'] = [
            'update' => Gate::allows('update', Statuslabel::class) ? true : false,
            'delete' => (Gate::allows('delete', Statuslabel::class) && ($statuslabel->assets_count == 0)) ? true : false,
        ];
        $array += $permissions_array;

        return $array;
    }
}
