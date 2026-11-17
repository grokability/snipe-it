<?php

namespace App\Models\Builders;

use App\Models\AssetModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of AssetModel
 *
 * @extends Builder<TModel>
 */
class AssetModelQueryBuilder extends Builder
{
    /**
     * @param  array<int, int>  $categoryIdListing
     */
    public function inCategory(array $categoryIdListing): self
    {
        return $this->whereIn('category_id', $categoryIdListing);
    }

    public function requestable(): self
    {
        return $this->where('requestable', '1');
    }

    public function searchByManufacturerOrCat(string $search): self
    {
        return $this->where('models.name', 'LIKE', "%$search%")
            ->orWhere('model_number', 'LIKE', "%$search%")
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('category', function ($query) use ($search) {
                    $query->where('categories.name', 'LIKE', '%'.$search.'%');
                });
            })
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('manufacturer', function ($query) use ($search) {
                    $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                });
            });
    }

    public function orderManufacturer(string $order): self
    {
        return $this
            ->leftJoin('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')
            ->orderBy('manufacturers.name', $order);
    }

    public function orderCategory(string $order): self
    {
        return $this
            ->leftJoin('categories', 'models.category_id', '=', 'categories.id')
            ->orderBy('categories.name', $order);
    }

    public function orderFieldset(string $order): self
    {
        return $this
            ->leftJoin('custom_fieldsets', 'models.fieldset_id', '=', 'custom_fieldsets.id')
            ->orderBy('custom_fieldsets.name', $order);
    }

    public function orderByCreatedByName(string $order): self
    {
        return $this
            ->leftJoin('users as admin_sort', 'models.created_by', '=', 'admin_sort.id')
            ->addSelect('models.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    /**
     * `% remaining` is (available / total) * 100. See
     * AssetModel::percentRemaining(). The caller
     * (Api\AssetModelsController::index) already adds the `remaining`
     * and `assets_count` withCount aliases before applying this scope,
     * so we reference them directly in ORDER BY. Guarded against
     * division by zero for models with no assets. Uses orderByRaw
     * because the query builder has no arithmetic API for ORDER BY
     * expressions.
     *
     * PostgreSQL note: this expression references SELECT-list aliases
     * inside a compound ORDER BY expression, which PostgreSQL rejects
     * per SQL standard. Snipe-IT officially supports MySQL/MariaDB and
     * tests on SQLite (both allow this). Moving to PostgreSQL would
     * require inlining the subqueries or wrapping the query in an
     * outer SELECT.
     */
    public function orderPercentRemaining(string $order): self
    {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw('CASE WHEN assets_count = 0 THEN 0 ELSE (remaining * 100.0 / assets_count) END '.$order);
    }
}
