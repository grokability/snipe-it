<?php

namespace App\Models\Builders;

use App\Models\Consumable;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Consumable
 *
 * @extends Builder<TModel>
 */
class ConsumableQueryBuilder extends Builder
{
    public function requestable(): self
    {
        return $this->notArchived()->where('consumables.requestable', '1');
    }

    public function archived(): self
    {
        return $this->where('consumables.archived', '=', 1);
    }

    public function notArchived(): self
    {
        return $this->where('consumables.archived', '=', 0);
    }

    /**
     * Default list scope. Archived rows drop out unless the admin has
     * flipped the show_archived_in_list setting on, matching the shape
     * of Asset::assetsForShow / Accessory::accessoriesForShow.
     */
    public function consumablesForShow(): self
    {
        if (Setting::getSettings()->show_archived_in_list == 1) {
            return $this;
        }

        return $this->notArchived();
    }

    public function orderCategory(string $order): self
    {
        return $this
            ->join('categories', 'consumables.category_id', '=', 'categories.id')
            ->orderBy('categories.name', $order);
    }

    public function orderLocation(string $order): self
    {
        return $this
            ->leftJoin('locations', 'consumables.location_id', '=', 'locations.id')
            ->orderBy('locations.name', $order);
    }

    public function orderManufacturer(string $order): self
    {
        return $this
            ->leftJoin('manufacturers', 'consumables.manufacturer_id', '=', 'manufacturers.id')
            ->orderBy('manufacturers.name', $order);
    }

    public function orderCompany(string $order): self
    {
        return $this
            ->leftJoin('companies', 'consumables.company_id', '=', 'companies.id')
            ->orderBy('companies.name', $order);
    }

    public function orderRemaining(string $order): self
    {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw('consumables.qty - consumables_users_count '.$order);
    }

    public function orderSupplier(string $order): self
    {
        return $this
            ->leftJoin('suppliers', 'consumables.default_supplier_id', '=', 'suppliers.id')
            ->orderBy('suppliers.name', $order);
    }

    public function orderByCreatedBy(string $order): self
    {
        return $this
            ->leftJoin('users as users_sort', 'consumables.created_by', '=', 'users_sort.id')
            ->addSelect('consumables.*')
            ->orderBy('users_sort.first_name', $order)
            ->orderBy('users_sort.last_name', $order);
    }

    /**
     * Mirrors Consumable::percentRemaining(): (qty -
     * consumables_users_count) / qty * 100. consumables_users_count is
     * added by withCount() in the API index() before this scope runs.
     * Guards against division by zero for consumables with qty of 0.
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

        return $this->orderByRaw('CASE WHEN consumables.qty = 0 THEN 0 ELSE ((consumables.qty - consumables_users_count) * 100.0 / consumables.qty) END '.$order);
    }
}
