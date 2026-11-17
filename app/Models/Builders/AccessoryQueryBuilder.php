<?php

namespace App\Models\Builders;

use App\Models\Accessory;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Accessory
 *
 * @extends Builder<TModel>
 */
class AccessoryQueryBuilder extends Builder
{
    public function requestable(): self
    {
        return $this->where('accessories.requestable', '1')
            ->notArchived();
    }

    public function archived(): self
    {
        return $this->where('accessories.archived', '=', 1);
    }

    public function notArchived(): self
    {
        return $this->where('accessories.archived', '=', 0);
    }

    /**
     * Default list-page scope. Hides archived rows unless the
     * `show_archived_in_list` setting is on.
     */
    public function accessoriesForShow(): self
    {
        if (Setting::getSettings()->show_archived_in_list != 1) {
            return $this->notArchived();
        }

        return $this;
    }

    public function orderByCreatedByName(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'accessories.created_by', '=', 'admin_sort.id')
            ->addSelect('accessories.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('companies', 'accessories.company_id', '=', 'companies.id')
            ->orderBy('companies.name', $order);
    }

    public function orderCategory(string $order): self
    {
        return $this
            ->leftJoin('categories', 'accessories.category_id', '=', 'categories.id')
            ->orderBy('categories.name', $order);
    }

    public function orderLocation(string $order): self
    {
        return $this
            ->leftJoin('locations', 'accessories.location_id', '=', 'locations.id')
            ->orderBy('locations.name', $order);
    }

    public function orderManufacturer(string $order): self
    {
        return $this
            ->leftJoin('manufacturers', 'accessories.manufacturer_id', '=', 'manufacturers.id')
            ->orderBy('manufacturers.name', $order);
    }

    public function orderSupplier(string $order): self
    {
        return $this
            ->leftJoin('suppliers', 'accessories.default_supplier_id', '=', 'suppliers.id')
            ->orderBy('suppliers.name', $order);
    }

    /**
     * Mirrors Accessory::percentRemaining(): (qty - checkouts_count) /
     * qty * 100. checkouts_count is added by withCount() in the API
     * index() before this scope runs. Guards against division by zero
     * for accessories with qty of 0.
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

        return $this->orderByRaw('CASE WHEN accessories.qty = 0 THEN 0 ELSE ((accessories.qty - checkouts_count) * 100.0 / accessories.qty) END '.$order);
    }

    /**
     * Sort by the raw `remaining` column (qty minus current checkouts).
     * Same withCount-added alias as orderPercentRemaining above. The
     * difference is that this one sorts by absolute count rather than
     * percentage, so items with the same absolute stock left group
     * together regardless of their total qty.
     */
    public function orderRemaining(string $order): self
    {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw('(accessories.qty - checkouts_count) '.$order);
    }
}
