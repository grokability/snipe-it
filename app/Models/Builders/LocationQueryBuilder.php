<?php

namespace App\Models\Builders;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Location
 *
 * @extends Builder<TModel>
 */
class LocationQueryBuilder extends Builder
{
    public function orderParent(string $order): self
    {
        // Left join here, or it will only return results with parents
        return $this
            ->leftJoin('locations as parent_loc', 'locations.parent_id', '=', 'parent_loc.id')
            ->orderBy('parent_loc.name', $order);
    }

    public function orderManager(string $order): self
    {
        return $this
            ->leftJoin('users as location_user', 'locations.manager_id', '=', 'location_user.id')
            ->orderBy('location_user.first_name', $order)
            ->orderBy('location_user.last_name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('companies as company_sort', 'locations.company_id', '=', 'company_sort.id')
            ->orderBy('company_sort.name', $order);
    }

    public function orderByCreatedByName(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'locations.created_by', '=', 'admin_sort.id')
            ->addSelect('locations.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
