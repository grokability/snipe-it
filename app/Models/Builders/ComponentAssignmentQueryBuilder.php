<?php

namespace App\Models\Builders;

use App\Models\ComponentAssignment;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of ComponentAssignment
 *
 * @extends Builder<TModel>
 */
class ComponentAssignmentQueryBuilder extends Builder
{
    public function orderByCreatedByName(string $order): self
    {
        return $this
            ->leftJoin('users as checkout_users_sort', 'components_assets.created_by', '=', 'checkout_users_sort.id')
            ->addSelect('components_assets.*')
            ->orderBy('checkout_users_sort.first_name', $order)
            ->orderBy('checkout_users_sort.last_name', $order);
    }

    public function orderByComponentName(string $order): self
    {
        return $this
            ->leftJoin('components as component_sort', 'components_assets.id', '=', 'component_sort.id')
            ->addSelect('components_assets.*')
            ->orderBy('component_sort.name', $order);
    }
}
