<?php

namespace App\Models\Builders;

use App\Models\License;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of License
 *
 * @extends Builder<TModel>
 */
class LicenseQueryBuilder extends Builder
{
    public function requestable(): self
    {
        return $this->where('licenses.requestable', '1');
    }

    public function activeLicenses(): self
    {
        return $this->whereNull('licenses.deleted_at')
            // The termination date is null or within range
            ->where(function ($query) {
                $query->whereNull('termination_date')
                    ->orWhereDate('termination_date', '>', [Carbon::now()]);
            })
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>', [Carbon::now()]);
            });
    }

    public function expiredLicenses(): self
    {
        return $this->whereDate('termination_date', '<=', Carbon::now())
            ->orWhere(function ($query) {
                $query->whereDate('expiration_date', '<=', Carbon::now());
            })
            ->whereNull('licenses.deleted_at');
    }

    /**
     * This checks if:
     *
     * 1) The license has not been deleted
     * 2) The expiration date is between now and the number of days specified
     * 3) There is an expiration date set and the termination date has not passed
     * 4) The license termination date is null or has not passed
     *
     * @see SendExpiringLicenseNotifications
     */
    public function expiringLicenses(int $days = 60, bool $includeExpired = false): self
    {
        return $this
            // The termination date is null or within range
            ->where(function ($query) use ($days) {
                $query->whereNull('termination_date')
                    ->orWhereBetween('termination_date', [Carbon::now(), Carbon::now()->addDays($days)]);
            })
            ->where(function ($query) use ($days, $includeExpired) {
                $query->whereNotNull('expiration_date')
                    // Handle expiring licenses without termination dates
                    ->where(function ($query) use ($days, $includeExpired) {
                        $query->whereNull('termination_date')
                            ->whereBetween('expiration_date', [Carbon::now(), Carbon::now()->addDays($days)])
                            // include expired licenses if requested
                            ->when($includeExpired, function ($query) {
                                $query->orwhereDate('expiration_date', '<=', Carbon::now());
                            });
                    })
                    // Handle expiring licenses with termination dates in the future
                    ->orWhere(function ($query) use ($days) {
                        $query->whereBetween('termination_date', [Carbon::now(), Carbon::now()->addDays($days)]);
                    });
            });
    }

    public function orderManufacturer(string $order): self
    {
        return $this
            ->leftJoin('manufacturers', 'licenses.manufacturer_id', '=', 'manufacturers.id')
            ->addSelect('licenses.*')
            ->orderBy('manufacturers.name', $order);
    }

    public function orderSupplier(string $order): self
    {
        return $this
            ->leftJoin('suppliers', 'licenses.supplier_id', '=', 'suppliers.id')
            ->addSelect('licenses.*')
            ->orderBy('suppliers.name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('companies as companies', 'licenses.company_id', '=', 'companies.id')
            ->addSelect('licenses.*')
            ->orderBy('companies.name', $order);
    }

    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'licenses.created_by', '=', 'admin_sort.id')
            ->addSelect('licenses.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    /**
     * Mirrors License::percentRemaining(): available_seats / total_seats
     * * 100. free_seats_count is added by withCount() in the API index()
     * as the count of unassigned seats. seats is a real column on
     * licenses. Guards against division by zero for licenses with no
     * seats.
     *
     * PostgreSQL note: references a SELECT-list alias inside a
     * compound ORDER BY expression, which PostgreSQL rejects per SQL
     * standard. Snipe-IT officially supports MySQL/MariaDB and tests
     * on SQLite (both allow this). Moving to PostgreSQL would require
     * inlining the subquery or wrapping the query in an outer SELECT.
     */
    public function orderPercentRemaining(string $order): self
    {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw('CASE WHEN licenses.seats = 0 THEN 0 ELSE (free_seats_count * 100.0 / licenses.seats) END '.$order);
    }
}
