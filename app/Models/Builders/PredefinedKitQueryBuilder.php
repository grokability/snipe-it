<?php

namespace App\Models\Builders;

use App\Models\PredefinedKit;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of PredefinedKit
 *
 * @extends Builder<TModel>
 */
class PredefinedKitQueryBuilder extends Builder
{
    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'kits.created_by', '=', 'admin_sort.id')
            ->addSelect('kits.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
