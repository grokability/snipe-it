<?php

namespace App\Models\Builders;

use App\Models\Statuslabel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Statuslabel
 *
 * @extends Builder<TModel>
 */
class StatuslabelQueryBuilder extends Builder
{
    public function pending(): self
    {
        return $this->where('pending', '=', 1)
            ->where('archived', '=', 0)
            ->where('deployable', '=', 0);
    }

    public function archived(): self
    {
        return $this->where('pending', '=', 0)
            ->where('archived', '=', 1)
            ->where('deployable', '=', 0);
    }

    public function deployable(): self
    {
        return $this->where('pending', '=', 0)
            ->where('archived', '=', 0)
            ->where('deployable', '=', 1);
    }

    public function undeployable(): self
    {
        return $this->where('pending', '=', 0)
            ->where('archived', '=', 0)
            ->where('deployable', '=', 0);
    }

    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'status_labels.created_by', '=', 'admin_sort.id')
            ->addSelect('status_labels.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
