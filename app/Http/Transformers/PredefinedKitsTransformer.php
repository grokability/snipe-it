<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\PredefinedKit;
use App\Models\SnipeModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;

/**
 * transforms collection of models to array with simple typres
 *
 * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
 * @return array
 */
class PredefinedKitsTransformer
{
    public function transformPredefinedKits(Collection $kits, $total)
    {
        $array = [];
        foreach ($kits as $kit) {
            $array[] = self::transformPredefinedKit($kit);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformPredefinedKit(PredefinedKit $kit)
    {
        $array = [
            'id' => (int) $kit->id,
            'name' => e($kit->name),
            'created_by' => ($kit->adminuser) ? [
                'id' => (int) $kit->adminuser->id,
                'name'=> e($kit->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($kit->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($kit->updated_at, 'datetime'),
        ];

        $permissions_array['available_actions'] = [
            'update' => Gate::allows('update', PredefinedKit::class),
            'delete' => Gate::allows('delete', PredefinedKit::class),
            'checkout' => Gate::allows('checkout', Asset::class),
            // 'clone' => Gate::allows('create', PredefinedKit::class),
            // 'restore' => Gate::allows('create', PredefinedKit::class),
        ];
        $array['user_can_checkout'] = true;
        $array += $permissions_array;

        return $array;
    }

    /**
     * transform collection of any elemets attached to kit
     * @return array
     */
    public function transformElements(Collection $elements, $total)
    {
        $array = [];
        foreach ($elements as $element) {
            $array[] = self::transformElement($element);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformElement(SnipeModel $element)
    {
        $array = [
            'id' => (int) $element->id,
            'pivot_id' => (int) $element->pivot->id,
            'owner_id' => (int) $element->pivot->kit_id,
            'quantity' => (int) $element->pivot->quantity,
            'name' => e($element->name),
        ];

        $permissions_array['available_actions'] = [
            'update' => Gate::allows('update', PredefinedKit::class),
            'delete' => Gate::allows('delete', PredefinedKit::class),
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformPredefinedKitsDatatable($kits)
    {
        return (new DatatablesTransformer)->transformDatatables($kits);
    }
}
