<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Builder;

class PredefinedFilter extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $casts = [
        "filter_data"=> "array",
    ];

    protected $fillable = [
        'name',
        'created_by',
        'filter_data',
        'permission_group_id',
    ];

    protected $rules = [
        'name'                    => ['required', 'string', 'max:255'],
        'created_by'              => ['required', 'integer', 'exists:users,id'],
        'filter_data'             => ['nullable', 'array']
    ];

    protected function applyArrayOrScalarFilter(Builder $assets, array $filter, string $key, string $column): void
    {
        if (!empty($filter[$key])) {
            $values = is_array($filter[$key]) ? $filter[$key] : [$filter[$key]];
            $assets->whereIn($column, $values);
        }
    }

        protected function applyLikeFilter(Builder $assets, array $filter, string $key, string $column): void
    {
        if (!empty($filter[$key])) {
            $assets->where($column, 'LIKE', '%' . $filter[$key] . '%');
        }
    }

    protected function applyDateRangeFilter(Builder $assets, array $filter, string $base): void
    {
        if (!empty($filter["{$base}_start"])) {
            $assets->whereDate("{$base}", '>=', $filter["{$base}_start"]);
        }
        if (!empty($filter["{$base}_end"])) {
            $assets->whereDate("{$base}", '<=', $filter["{$base}_end"]);
        }
    }

    public function filterAssets(Builder $assets) {
        $filter = $this->filter_data ?? [];
        
        $this->applyArrayOrScalarFilter($assets, $filter, 'company_id', 'assets.company_id');
        $this->applyArrayOrScalarFilter($assets, $filter, 'location_id', 'location_id');
        $this->applyArrayOrScalarFilter($assets, $filter, 'rtd_location_id', 'rtd_location_id');
        $this->applyArrayOrScalarFilter($assets, $filter, 'supplier_id', 'supplier_id');
        $this->applyArrayOrScalarFilter($assets, $filter, 'model_id', 'model_id');
        $this->applyArrayOrScalarFilter($assets, $filter, 'status_id', 'status_id');

        if (!empty($filter['category_id']) || !empty($filter['manufacturer_id'])) {
            $assets->leftJoin('models', 'assets.model_id', '=', 'models.id');
            $this->applyArrayOrScalarFilter($assets, $filter, 'category_id', 'models.category_id');
            $this->applyArrayOrScalarFilter($assets, $filter, 'manufacturer_id', 'models.manufacturer_id');
        }

        $this->applyDateRangeFilter($assets, $filter, 'created_at');
        $this->applyDateRangeFilter($assets, $filter, 'purchase_date');
        $this->applyDateRangeFilter($assets, $filter, 'last_checkout');
        $this->applyDateRangeFilter($assets, $filter, 'last_checkin');
        $this->applyDateRangeFilter($assets, $filter, 'expected_checkin');
        $this->applyDateRangeFilter($assets, $filter, 'asset_eol_date');
        $this->applyDateRangeFilter($assets, $filter, 'last_audit_date');
        $this->applyDateRangeFilter($assets, $filter, 'next_audit_date');
        $this->applyDateRangeFilter($assets, $filter, 'updated_at');

        $this->applyLikeFilter($assets, $filter, 'asset_name', 'assets.name');
        $this->applyLikeFilter($assets, $filter, 'asset_tag', 'assets.asset_tag');
        $this->applyLikeFilter($assets, $filter, 'serial', 'assets.serial');

        // Custom fields
        if (!empty($filter['custom_fields']) && is_array($filter['custom_fields'])) {
            foreach ($filter['custom_fields'] as $key => $value) {
                $assets->where("assets.$key", '=', $value);
            }
        }
        return $assets;
    }
}
