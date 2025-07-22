<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class SuppliersTransformer
{
    public function transformSuppliers(Collection $suppliers, $total)
    {
        $array = [];
        foreach ($suppliers as $supplier) {
            $array[] = self::transformSupplier($supplier);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformSupplier(Supplier $supplier = null)
    {
        if ($supplier) {
            $array = [
                'id' => (int) $supplier->id,
                'name' => e($supplier->name),
                'image' =>   ($supplier->image) ? Storage::disk('public')->url('suppliers/'.e($supplier->image)) : null,
                'url' => e($supplier->url),
                'address' => e($supplier->address),
                'address2' => e($supplier->address2),
                'city' => e($supplier->city),
                'state' => e($supplier->state),
                'country' => e($supplier->country),
                'zip' => e($supplier->zip),
                'fax' => e($supplier->fax),
                'phone' => e($supplier->phone),
                'email' => e($supplier->email),
                'contact' => e($supplier->contact),
                'assets_count' => (int) $supplier->assets_count,
                'accessories_count' => (int) $supplier->accessories_count,
                'licenses_count' => (int) $supplier->licenses_count,
                'consumables_count' => (int) $supplier->consumables_count,
                'components_count' => (int) $supplier->components_count,
                'notes' => ($supplier->notes) ? Helper::parseEscapedMarkedownInline($supplier->notes) : null,
                'created_at' => Helper::getFormattedDateObject($supplier->created_at, 'datetime'),
                'created_by' => $supplier->adminuser ? [
                    'id' => (int) $supplier->adminuser->id,
                    'name'=> e($supplier->adminuser->present()->fullName),
                ]: null,
                'updated_at' => Helper::getFormattedDateObject($supplier->updated_at, 'datetime'),

            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Supplier::class),
                'delete' => (Gate::allows('delete', Supplier::class) && ($supplier->assets_count == 0) && ($supplier->licenses_count == 0) && ($supplier->accessories_count == 0)),
            ];

            $array += $permissions_array;

            return $array;
        }
    }
}
