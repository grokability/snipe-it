<?php

namespace App\Models\Builders;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Category
 *
 * @extends Builder<TModel>
 */
class CategoryQueryBuilder extends Builder
{
    public function requiresAcceptance(): self
    {
        return $this->where('require_acceptance', '=', true);
    }

    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'categories.created_by', '=', 'admin_sort.id')
            ->addSelect('categories.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
