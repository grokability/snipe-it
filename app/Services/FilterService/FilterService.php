<?php

namespace App\Services\FilterService;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Location;
use App\Models\Asset;
use Log;
use function Laravel\Prompts\note;

class FilterService
{
    
    public function searchByFilter( $query, $filters){
        $q = $query->where(
            function(Builder $query) use ($filters){

                $dateFields = [
                    'purchase_date',
                    'asset_eol_date',
                    'created_at',
                    'updated_at',
                ];

                $query = $this->applyDateRangeFilter($query, 'purchase_date', $filters);
                $query = $this->applyDateRangeFilter($query, 'asset_eol_date', $filters);
                $query = $this->applyDateRangeFilter($query, 'assets.created_at', $filters);
                $query = $this->applyDateRangeFilter($query, 'assets.updated_at', $filters);

                $skipFields = [
                    'purchase_date',
                    'asset_eol_date',
                    'created_at',
                    'updated_at',
                ];

                //changed to Array and loop through each item
                foreach($filters as $filterItem) {
                            // this legacy part does break tests with new structure covers the tests we had
                    // if (!is_array($filterItem) || !isset($filterItem['field'])) {
                    //     foreach ($filters as $key => $value){
                    //         // if (is_string($key) && !is_array($value) && !in_array($key, $skipFields)) {
                    //         //     $filterObj = [
                    //         //         'field'     => $key,
                    //         //         'value'     => $value,
                    //         //         'operator'  => 'contains', // default
                    //         //         'logic'     => 'AND' // default
                    //         //     ];
                    //         //     $this->applySingleFilter($query, $filterObj);
                    //         // }
                    //     }
                    //     break;
                    // }
                    if (!isset($filterItem['field'], $filterItem['operator'], $filterItem['logic'], $filterItem['value'])) {
                        continue;
                    }
                    if ($filterItem['value'] === ['']) {
                        continue;
                    }
                    if (in_array($filterItem['field'], $skipFields)) {
                        continue;
                    }
                    $this->applySingleFilter($query, $filterItem);
                }
            }
        );
        return $q;
    }

    /**
    * Apply a single filter object into the query builder, using operator & logic.
    *
    * @param Builder $q
    * @param array $filterObj  keys: field, value, operator, logic
    * @return void
    */
    protected function applySingleFilter(Builder &$q, array $filterObj)
    {
        $fieldname = $filterObj['field'];
        $value     = $filterObj['value'];
        $operator  = strtolower($filterObj['operator'] ?? 'equals'); // "equals" or "contains"
        $logic     = strtoupper($filterObj['logic'] ?? 'AND');       // "AND", "OR", "NOT"

        $callback = function (Builder $inner) use ($fieldname, $value, $operator, $filterObj) {
            // === 1. Custom Field Support ===

            // if (Str::startsWith($fieldname, ['_snipeit_'])) {
            //     Log::error("fieldName: {$fieldname}");
            //     Log::error("value: {$value}");
            //     $fieldLabel = Str::after($fieldname, '_snipeit_');

            //     $this->applyCustomFieldFilter($inner, $filterObj);

            //     return;
            // }

            // === 2. Field Mapping for Relational Fields ===
            $simpleFields = [
                'asset_tag'     => 'assets.asset_tag',
                'name'          => 'assets.name',
                'serial'        => 'assets.serial',
                'purchase_date' => 'assets.purchase_date',
                'purchase_cost' => 'assets.purchase_cost',
                'notes'         => 'assets.notes',
                'order_number'  => 'assets.order_number',
            ];

            $relationMap = [
                'model' => [
                    'relation' => 'model',
                    'id' => 'models.id',
                    'name' => 'models.name',
                ],
                'category' => [
                    'relation' => 'model.category',
                    'id' => 'categories.id',
                    'name' => 'categories.name',
                ],
                'manufacturer' => [
                    'relation' => 'model.manufacturer',
                    'id' => 'manufacturers.id',
                    'name' => 'manufacturers.name',
                ],
                'company' => [
                    'relation' => 'company',
                    'id' => 'companies.id',
                    'name' => 'companies.name',
                ],
                'supplier' => [
                    'relation' => 'supplier',
                    'id' => 'suppliers.id',
                    'name' => 'suppliers.name',
                ],
                'location' => [
                    'relation' => 'location',
                    'id' => 'locations.id',
                    'name' => 'locations.name',
                ],
                'rtd_location' => [
                    'relation' => 'defaultLoc',
                    'id' => 'locations.id',
                    'name' => 'locations.name',
                ],
                'status_label' => [
                    'relation' => 'assetstatus',
                    'id' => 'status_labels.id',
                    'name' => 'status_labels.name',
                ],
                'model_number' => [
                    'relation' => 'model',
                    'column' => 'models.model_number',
                ],
                'jobtitle' => [
                    'relation' => 'assignedTo',
                    'morph' => true,
                    'type' => User::class,
                    'column' => 'users.jobtitle',
                ],
                'assigned_to' => [
                    'relation' => 'assignedTo',
                    'morph' => true,
                    'types' => [User::class, Asset::class, Location::class],
                ],
            ];

            // === 3. Simple Fields ===
            if (array_key_exists($fieldname, $simpleFields)) {
                $column = $simpleFields[$fieldname];

            $this->applyWhereWithOperator($inner, $column, $value, $operator);
                return;
            }

            // === 4. Relational or Morph ===
            if (isset($relationMap[$fieldname])) {
                $meta = $relationMap[$fieldname];

                // --- Morph Relation ---
                if (!empty($meta['morph'])) {
                    if (is_array($value) && isset($value[0]['assignedType'])){
                        $grouped = collect($value)->groupBy('assignedType');

                        $inner->where(function ($q2) use ($grouped, $meta){
                            foreach ($grouped as $type => $items) {
                                $q2->orWhereHasMorph($meta['relation'],[$type], function ($morphQ) use ($items, $type) {
                                    $ids = collect($items)->pluck('assigned_to')->filter((fn($v)=>is_numeric($v)));
                                    $names = collect($items)->pluck('assigned_to')->filter(fn($v) => is_string($v));
                                
                                    if ($ids->isNotEmpty()){
                                        $morphQ->whereIn('id', $ids);
                                    }

                                    if ($names->isNotEmpty()){
                                        $morphQ->where(function($query) use ($names, $type) {

                                            foreach ($names as $name) {
                                                if ($type === \App\Models\User::class) {
                                                
                                                    $query->orWhere(function($sq) use ($name) {
                                                        $sq->where('first_name', 'LIKE', '%' . $name . '%')
                                                        ->orWhere('last_name', 'LIKE', '%' . $name . '%');
                                                    });
                                                
                                                }else{
                                                    $query->orWhere('name', 'LIKE', '%' . $name . '%');
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        });

                    return;
                }

                $types = $meta['types'] ?? [$meta['type']];

                $inner->where(function ($q2) use ($types, $value, $operator, $meta) {
                    foreach ($types as $type) {
                        $q2->orWhereHasMorph($meta['relation'], [$type], function ($morphQ) use ($type, $value, $operator, $meta) {
                            if ($meta['column'] ?? false) {
                                $field = $meta['column'];
                                if (is_array($value)) {
                                    $morphQ->whereIn($field, $value);
                                } else {
                                    $morphQ->where($field, $operator === 'equals' ? '=' : 'LIKE', $operator === 'equals' ? $value : '%' . $value . '%');
                                }
                            } else {
                                if ($type === User::class) {
                                    $morphQ->where(function ($sq) use ($value) {
                                        $sq->where('first_name', 'LIKE', '%' . $value . '%')
                                           ->orWhere('last_name', 'LIKE', '%' . $value . '%');
                                    });
                                } else {
                                    $morphQ->where('name', 'LIKE', '%' . $value . '%');
                                }
                            }
                        });
                    }
                });
                return;
            }

            // --- Normal Relation ---
            $relationPath = explode('.', $meta['relation']);
            $first = array_shift($relationPath);

            $inner->whereHas($first, function ($subQ) use ($relationPath, $value, $operator, $meta) {
                foreach ($relationPath as $relation) {
                    $subQ->whereHas($relation, function ($q) use ($value, $operator, $meta) {
                        $this->applyRelationalValue($q, $value, $operator, $meta);
                    });
                }

                if (empty($relationPath)) {
                    $this->applyRelationalValue($subQ, $value, $operator, $meta);
                }
            });

            return;
        }

        // === 5. Fallback: Direct column ===
        $column = 'assets.' . $fieldname;

        if (!Schema::hasColumn('assets', $fieldname)) {
            return;
        }

         $this->applyWhereWithOperator($inner, $column, $value, $operator);
    };

        // === Apply logic ===
        switch ($logic) {
            case 'NOT':
            $q->where(function ($outer) use ($callback, $fieldname) {
                $outer->whereNot($callback);

                // Only add "OR IS NULL" for direct columns (not relationships)
                if (!Str::contains($fieldname, '.')) {
                    // Also double-check column existence
                    if (Schema::hasColumn('assets', $fieldname)) {
                        $outer->orWhereNull('assets.' . $fieldname);
                    }
                }
            });
        break;
        case 'AND':
        default:
            $q->where($callback);
            break;
        }
    }
    protected function applyCustomFieldFilter(Builder $query, array $filter)
    {
        $fieldname = $filter['field'];
        $value = $filter['value'];
        $operator = strtolower($filter['operator'] ?? 'contains');

            Log::error($fieldname);
            Log::error($value);

            $column = $fieldname;

            if (!$column || !Schema::hasColumn('assets', $column)) {
                return;
            }

            $column = 'assets.' . $column;

            $this->applyWhereWithOperator($query, $column, $value, $operator);
    }

protected function applyRelationalValue(Builder $q, $value, string $operator, array $meta): void
{
    $idField = $meta['id'] ?? null;
    $nameField = $meta['name'] ?? null;
    $column = $meta['column'] ?? null;

    if ($column) {
        $this->applyWhereWithOperator($q, $column, $value, $operator);
        return;
    }

    // Fallback safety check
    if (!$idField && !$nameField) {
        return;
    }
    $values = is_array($value) ? $value : [$value];

    $ids = array_filter($values, 'is_int');
    $names = array_filter($values, 'is_string');

    $q->where(function ($subQ) use ($ids, $names, $idField, $nameField, $operator) {
        $first = true;

        // IDs only
        if (!empty($ids)) {
            if ($first) {
                $subQ->whereIn($idField, $ids);
                $first = false;
            } else {
                $subQ->orWhereIn($idField, $ids);
            }
        }

        // Names only
        foreach ($names as $name) {
            if ($first) {
                $this->applyWhereWithOperator($subQ, $nameField, $name, $operator);
                $first = false;
            } else {
                $subQ->orWhere(function ($q) use ($nameField, $name, $operator) {
                    $this->applyWhereWithOperator($q, $nameField, $name, $operator);
                });
            }
        }
    });
}

protected function applyWhereWithOperator(Builder $query, string $column, $value, string $operator)
{
    $value = is_array($value) ? $value : [$value];

    if ($operator === 'equals') {
        $query->whereIn($column, $value);
    } else { // contains
        $query->where(function ($q) use ($column, $value) {
            foreach ($value as $v) {
                $q->orWhere($column, 'LIKE', '%' . $v . '%');
            }
        });
    }
}

    /**
     * Query builder scope to filter by a date range on a given field
     *
     * @param \Illuminate\Database\Eloquent\Builder $query      Query builder instance
     * @param string                                $field      Database column name
     * @param string                                $startKey   Filter array key for start date
     * @param string                                $endKey     Filter array key for end date
     * @param array                                 $filter     Filter array
     *
     * @return \Illuminate\Database\Eloquent\Builder            Modified query builder
     */
    public function applyDateRangeFilter($query, $field, $filters)
    {
        $start = null;
        $end = null;

        foreach ($filters as $filter) {
            if (!isset($filter['field'], $filter['value']) || !is_array($filter['value'])) {
                continue;
            }

            // Normalize: Remove table prefix (e.g., 'assets.')
            $fieldNameOnly = Str::afterLast($field, '.');

            if ($filter['field'] !== $fieldNameOnly) {
                continue;
            }

            if (isset($filter['value']['startDate'])) {
                $start = $filter['value']['startDate'];
            }

            if (isset($filter['value']['endDate'])) {
                $end = $filter['value']['endDate'];
            }
        }
        
        if (!empty($start)) {
            $query->whereDate($field, '>=', $start);
        }
        
        if (!empty($end)) {
            $query->whereDate($field, '<=', $end);
        }
        return $query;
    }
}
