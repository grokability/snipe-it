<?php

namespace App\Models\Builders;

use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Group
 *
 * @extends Builder<TModel>
 */
class GroupQueryBuilder extends Builder
{
    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'permission_groups.created_by', '=', 'admin_sort.id')
            ->addSelect('permission_groups.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
