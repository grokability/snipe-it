<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\License;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;

class LicensesTransformer
{
    public function transformLicenses(Collection $licenses, $total)
    {
        $array = [];
        foreach ($licenses as $license) {
            $array[] = self::transformLicense($license);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLicense(License $license)
    {
        $array = [
            'id' => (int) $license->id,
            'name' => e($license->name),
            'company' => ($license->company) ? ['id' => (int) $license->company->id, 'name'=> e($license->company->name)] : null,
            'manufacturer' =>  ($license->manufacturer) ? ['id' => (int) $license->manufacturer->id, 'name'=> e($license->manufacturer->name)] : null,
            'product_key' => (Gate::allows('viewKeys', License::class)) ? e($license->serial) : '------------',
            'order_number' => ($license->order_number) ? e($license->order_number) : null,
            'purchase_order' => ($license->purchase_order) ? e($license->purchase_order) : null,
            'purchase_date' => Helper::getFormattedDateObject($license->purchase_date, 'date'),
            'termination_date' => Helper::getFormattedDateObject($license->termination_date, 'date'),
            'expiration_date' => Helper::getFormattedDateObject($license->expiration_date, 'date'),
            'depreciation' => ($license->depreciation) ? ['id' => (int) $license->depreciation->id,'name'=> e($license->depreciation->name)] : null,
            'purchase_cost' => Helper::formatCurrencyOutput($license->purchase_cost),
            'purchase_cost_numeric' => $license->purchase_cost,
            'notes' => Helper::parseEscapedMarkedownInline($license->notes),
            'seats' => (int) $license->seats,
            'free_seats_count' => (int) $license->free_seats_count - License::unReassignableCount($license),
            'remaining' => (int) $license->free_seats_count,
            'min_amt' => ($license->min_amt) ? (int) ($license->min_amt) : null,
            'license_name' =>  ($license->license_name) ? e($license->license_name) : null,
            'license_email' => ($license->license_email) ? e($license->license_email) : null,
            'reassignable' => ($license->reassignable == 1) ? true : false,
            'maintained' => ($license->maintained == 1) ? true : false,
            'supplier' =>  ($license->supplier) ? ['id' => (int) $license->supplier->id, 'name'=> e($license->supplier->name)] : null,
            'category' =>  ($license->category) ? ['id' => (int) $license->category->id, 'name'=> e($license->category->name)] : null,
            'created_by' => ($license->adminuser) ? [
                'id' => (int) $license->adminuser->id,
                'name'=> e($license->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($license->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($license->updated_at, 'datetime'),
            'deleted_at' => Helper::getFormattedDateObject($license->deleted_at, 'datetime'),
            'user_can_checkout' => (bool) ($license->free_seats_count > 0),
            'disabled' => $license->isInactive(),
        ];

        $permissions_array['available_actions'] = [
            'checkout' => Gate::allows('checkout', License::class),
            'checkin' => Gate::allows('checkin', License::class),
            'clone' => Gate::allows('create', License::class),
            'update' => Gate::allows('update', License::class),
            'delete' => (Gate::allows('delete', License::class) && ($license->free_seats_count == $license->seats)) ? true : false,
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformAssetsDatatable($licenses)
    {
        return (new DatatablesTransformer)->transformDatatables($licenses);
    }



}
