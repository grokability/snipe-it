<?php

namespace App\Models\Builders;

use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of LicenseSeat
 *
 * @extends Builder<TModel>
 */
class LicenseSeatQueryBuilder extends Builder
{
    public function orderDepartments(string $order): self
    {
        return $this
            ->leftJoin('users as license_seat_users', 'license_seats.assigned_to', '=', 'license_seat_users.id')
            ->leftJoin('departments as license_user_dept', 'license_user_dept.id', '=', 'license_seat_users.department_id')
            ->whereNotNull('license_seats.assigned_to')
            ->orderBy('license_user_dept.name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('users as license_seat_users', 'license_seats.assigned_to', '=', 'license_seat_users.id')
            ->leftJoin('companies as license_user_company', 'license_user_company.id', '=', 'license_seat_users.company_id')
            ->whereNotNull('license_seats.assigned_to')
            ->orderBy('license_user_company.name', $order);
    }

    public function byAssigned(): self
    {
        return $this->where(function ($query) {
            $query->whereNotNull('assigned_to')
                ->orWhereNotNull('asset_id');
        });
    }
}
