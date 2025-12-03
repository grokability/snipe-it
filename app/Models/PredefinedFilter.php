<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PredefinedFilter extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $casts = [
        "filter_data" => "array",
        "is_public" => "boolean"
    ];

    protected $fillable = [
        'name',
        'created_by',
        'filter_data',
        'is_public',
        'object_type',
    ];

    protected $rules = [
        'name' => ['required', 'string', 'max:191'],
        'filter_data' => ['required', 'array'],
        'permissions' => ['sometimes', 'array'],
        'is_public' => 'sometimes|boolean'
    ];

    public function permissionGroups()
    {
        return $this->belongsToMany(
            PermissionGroup::class,
            'predefined_filter_permissions',
            'predefined_filter_id',
            'permission_group_id'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userHasPermission(User $user, string $action): bool
    {
        // Give the superuser all permissions no matter in which groups he is
        if ($user->isSuperUser()) {
            return true;
        }

        // If filter is private AND is_owner AND action != create he can do everything
        // such as create private, edit and delete
        // note the 'create' permission is only for creating public filters. 
        if ($user->id == $this->created_by && !$this->is_public && $action != 'create') {
            return true;
        }

        switch ($action) {
            case 'create':
                return $user->hasAccess('predefinedFilter.create');
            case 'view':
                if ($this->checkPermissions($user, 'view')) {
                    return true;
                }
                //cascade for edit and view
                return $this->userHasPermission($user, 'edit') || $this->userHasPermission($user, 'delete');
            case 'edit':
            case 'delete':
                // If filter is private AND is_owner AND action != create he can do everything
                // such as create private, edit and delete
                // note the 'create' permission is only for creating public filters. 
                if ($user->id == $this->created_by && !$this->is_public && $action != 'create') {
                    return true;
                }

                return $this->checkPermissions($user, $action);

            default:
                return false;
        } //end switch
    }

    private function checkPermissions(User $user, $action): bool
    {
        $userGroupIds = $user->groups()->pluck('id')->toArray();

        if (!$user->relationLoaded('groups')) {
            $user->load('groups');
        }

        foreach ($this->permissionGroups as $group) {
            if (in_array($group->id, $userGroupIds)) {
                $permissions = json_decode($group->permissions, true);
                if (isset($permissions["predefinedFilter.$action"]) && $permissions["predefinedFilter.$action"] == '1') {
                    return true;
                }
            }
        }

        return false;
    }

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

    protected function applyDateRangeFilter(Builder $assets, array $filter, string $field): void
    {
        $startKey = $field . '_start';
        $endKey   = $field . '_end';

        $start = $filter[$startKey] ?? null;
        $end   = $filter[$endKey] ?? null;


        if (!$start && !$end) {
            return;
        }

        $table  = $assets->getModel()->getTable();
        $column = $table . '.' . $field;

        if ($start) {
            $assets->whereDate($column, '>=', $start);
        }

        if ($end) {
            $assets->whereDate($column, '<=', $end);
        }
    }


    public function filterAssets(Builder $assets)
    {
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

        $this->applyLikeFilter($assets, $filter, 'name', 'assets.name');
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

    public function checkIfNameAlreadyExists(string $name, int $id=null): bool
    {
        if ($id === null) {
            $query = $this->where('name', '=', $name);
            return $query->exists();
        }

        $query = $this->where('name', '=', $name);
        $query->where('id', '<>', $id);
        return sizeof($query->get()->toArray()) > 1;

    }
}
