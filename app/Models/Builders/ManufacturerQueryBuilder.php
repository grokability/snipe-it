<?php

namespace App\Models\Builders;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Manufacturer
 *
 * @extends Builder<TModel>
 */
class ManufacturerQueryBuilder extends Builder
{
    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'manufacturers.created_by', '=', 'admin_sort.id')
            ->addSelect('manufacturers.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
