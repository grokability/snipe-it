<?php

namespace App\Models\Builders;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Supplier
 *
 * @extends Builder<TModel>
 */
class SupplierQueryBuilder extends Builder
{
    public function orderByCreatedByName(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'suppliers.created_by', '=', 'admin_sort.id')
            ->addSelect('suppliers.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
