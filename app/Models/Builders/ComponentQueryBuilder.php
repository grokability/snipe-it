<?php

namespace App\Models\Builders;

use App\Models\Component;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Component
 *
 * @extends Builder<TModel>
 */
class ComponentQueryBuilder extends Builder
{
    public function requestable(): self
    {
        return $this->where('components.requestable', '1')
            ->notArchived();
    }

    public function archived(): self
    {
        return $this->where('components.archived', '=', 1);
    }

    public function notArchived(): self
    {
        return $this->where('components.archived', '=', 0);
    }

    /**
     * Default list-page scope. Hides archived rows unless the
     * `show_archived_in_list` setting is on.
     */
    public function componentsForShow(): self
    {
        if (Setting::getSettings()->show_archived_in_list != 1) {
            return $this->notArchived();
        }

        return $this;
    }

    public function orderCategory(string $order): self
    {
        return $this
            ->join('categories', 'components.category_id', '=', 'categories.id')
            ->orderBy('categories.name', $order);
    }

    public function orderLocation(string $order): self
    {
        return $this
            ->leftJoin('locations', 'components.location_id', '=', 'locations.id')
            ->orderBy('locations.name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('companies', 'components.company_id', '=', 'companies.id')
            ->orderBy('companies.name', $order);
    }

    public function orderSupplier(string $order): self
    {
        return $this
            ->leftJoin('suppliers', 'components.default_supplier_id', '=', 'suppliers.id')
            ->orderBy('suppliers.name', $order);
    }

    public function orderManufacturer(string $order): self
    {
        return $this
            ->leftJoin('manufacturers', 'components.manufacturer_id', '=', 'manufacturers.id')
            ->orderBy('manufacturers.name', $order);
    }

    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'components.created_by', '=', 'admin_sort.id')
            ->addSelect('components.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    /**
     * Mirrors Component::percentRemaining(): (qty - numCheckedOut) /
     * qty * 100. sum_unconstrained_assets is added by withSum() in the
     * API index() as the total checked-out quantity. Guards against
     * division by zero for components with qty of 0.
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

        return $this->orderByRaw('CASE WHEN components.qty = 0 THEN 0 ELSE ((components.qty - COALESCE(sum_unconstrained_assets, 0)) * 100.0 / components.qty) END '.$order);
    }

    /**
     * Sort by the raw `remaining` column (qty minus current
     * checkouts). Same sum_unconstrained_assets alias as
     * orderPercentRemaining above. The difference is that this one
     * sorts by absolute count rather than percentage, so items with
     * the same absolute stock left group together regardless of their
     * total qty.
     */
    public function orderRemaining(string $order): self
    {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw('(components.qty - COALESCE(sum_unconstrained_assets, 0)) '.$order);
    }
}
