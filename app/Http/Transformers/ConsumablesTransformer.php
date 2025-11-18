<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Consumable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ConsumablesTransformer
{
    public function transformConsumables(Collection $consumables, $total)
    {
        $array = [];
        foreach ($consumables as $consumable) {
            $array[] = self::transformConsumable($consumable);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformConsumable(Consumable $consumable)
    {
        $array = [
            'id'            => (int) $consumable->id,
            'name'          => e($consumable->name),
            'image' =>   ($consumable->getImageUrl()) ? ($consumable->getImageUrl()) : null,
            'category'      => ($consumable->category) ? [
                'id' => $consumable->category->id,
                'name' => e($consumable->category->name),
                'tag_color' => $consumable->category->tag_color ? e($consumable->category->tag_color) : null,
                ] : null,
            'company'   => ($consumable->company) ? [
                'id' => (int) $consumable->company->id,
                'name' => e($consumable->company->name),
                'tag_color' => $consumable->company->tag_color ? e($consumable->company->tag_color) : null,
            ] : null,
            'item_no'       => e($consumable->item_no),
            'location'      => ($consumable->location) ? [
                'id' => (int) $consumable->location->id,
                'name' => e($consumable->location->name),
                'tag_color' => $consumable->location->tag_color ? e($consumable->location->tag_color) : null,
            ] : null,
            'manufacturer'  => ($consumable->manufacturer) ? [
                'id' => (int) $consumable->manufacturer->id,
                'name' => e($consumable->manufacturer->name),
                'tag_color' => $consumable->manufacturer->tag_color ? e($consumable->manufacturer->tag_color) : null,
            ] : null,
            'supplier' => ($consumable->supplier) ? [
                'id' => $consumable->supplier->id,
                'name'=> e($consumable->supplier->name),
                'tag_color' => $consumable->supplier->tag_color ? e($consumable->supplier->tag_color) : null,
            ] : null,
            'min_amt'       => (int) $consumable->min_amt,
            'model_number'  => ($consumable->model_number != '') ? e($consumable->model_number) : null,
            'remaining'  => $consumable->numRemaining(),
            'order_number'  => e($consumable->order_number),
            'purchase_cost'  => Helper::formatCurrencyOutput($consumable->purchase_cost),
            'total_cost' => Helper::formatCurrencyOutput($consumable->totalCostSum()),
            'purchase_date'  => Helper::getFormattedDateObject($consumable->purchase_date, 'date'),
            'qty'           => (int) $consumable->qty,
            'notes'         => ($consumable->notes) ? Helper::parseEscapedMarkedownInline($consumable->notes) : null,
            'created_by' => ($consumable->adminuser) ? [
                'id' => (int) $consumable->adminuser->id,
                'name'=> e($consumable->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($consumable->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($consumable->updated_at, 'datetime'),
        ];

        $permissions_array['user_can_checkout'] = false;

        if ($consumable->numRemaining() > 0) {
            $permissions_array['user_can_checkout'] = true;
        }

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', Consumable::class),
            'checkin' => Gate::allows('checkin', Consumable::class),
            'update' => Gate::allows('update', Consumable::class),
            'delete' => Gate::allows('delete', Consumable::class),
            'clone' => (Gate::allows('create', Consumable::class) && ($consumable->deleted_at == '')),
        ];
        $array += $permissions_array;

        return $array;
    }

    public function transformCheckedoutConsumables(Collection $consumables_users, $total)
    {
        $array = [];
        foreach ($consumables_users as $user) {
            $array[] = (new UsersTransformer)->transformUser($user);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }
}
