<?php

namespace App\Models\Builders;

use App\Models\Depreciation;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Depreciation
 *
 * @extends Builder<TModel>
 */
class DepreciationQueryBuilder extends Builder
{
    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'depreciations.created_by', '=', 'admin_sort.id')
            ->addSelect('depreciations.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
