<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ManufacturersTransformer
{
    public function transformManufacturers(Collection $manufacturers, $total)
    {
        $array = [];
        foreach ($manufacturers as $manufacturer) {
            $array[] = self::transformManufacturer($manufacturer);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformManufacturer(Manufacturer $manufacturer = null)
    {
        if ($manufacturer) {
            $array = [
                'id' => (int) $manufacturer->id,
                'name' => e($manufacturer->name),
                'url' => e($manufacturer->url),
                'image' =>   ($manufacturer->image) ? Storage::disk('public')->url('manufacturers/'.e($manufacturer->image)) : null,
                'support_url' => e($manufacturer->support_url),
                'warranty_lookup_url' => e($manufacturer->warranty_lookup_url),
                'support_phone' => e($manufacturer->support_phone),
                'support_email' => e($manufacturer->support_email),
                'assets_count' => (int) $manufacturer->assets_count,
                'licenses_count' => (int) $manufacturer->licenses_count,
                'consumables_count' => (int) $manufacturer->consumables_count,
                'accessories_count' => (int) $manufacturer->accessories_count,
                'components_count' => (int) $manufacturer->components_count,
                'notes' => Helper::parseEscapedMarkedownInline($manufacturer->notes),
                'created_by' => ($manufacturer->adminuser) ? [
                    'id' => (int) $manufacturer->adminuser->id,
                    'name'=> e($manufacturer->adminuser->display_name),
                ] : null,
                'created_at' => Helper::getFormattedDateObject($manufacturer->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($manufacturer->updated_at, 'datetime'),
                'deleted_at' => Helper::getFormattedDateObject($manufacturer->deleted_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => (($manufacturer->deleted_at == '') && (Gate::allows('update', Manufacturer::class))),
                'restore' => (($manufacturer->deleted_at != '') && (Gate::allows('create', Manufacturer::class))),
                'delete' => $manufacturer->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }
    }
}
