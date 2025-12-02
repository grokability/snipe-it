<?php

namespace App\Services\FilterService;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Location;
use App\Models\Asset;
use UnexpectedValueException;

class FilterService
{

    // === Field Mapping Arrays ===
    protected array $skipFields = [
        'purchase_date',
        'asset_eol_date',
        'created_at',
        'updated_at',
    ];

    protected array $simpleFields = [
        'asset_tag' => 'assets.asset_tag',
        'name' => 'assets.name',
        'serial' => 'assets.serial',
        'purchase_date' => 'assets.purchase_date',
        'purchase_cost' => 'assets.purchase_cost',
        'notes' => 'assets.notes',
        'order_number' => 'assets.order_number',
    ];

    protected array $relationMap = [
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
    ];

    public function searchByFilter($query, $filters)
    {
        return $query->where(function (Builder $query) use ($filters) {

            $this->applyDateRangeFilter($query, 'assets.purchase_date', $filters, false);
            $this->applyDateRangeFilter($query, 'assets.asset_eol_date', $filters, false);

            $this->applyDateRangeFilter($query, 'assets.created_at', $filters, true);
            $this->applyDateRangeFilter($query, 'assets.updated_at', $filters, true);

            foreach ($filters as $filterItem) {
                if (!isset($filterItem['field'], $filterItem['operator'], $filterItem['logic'], $filterItem['value'])) {
                    continue;
                }
                if ($filterItem['value'] === ['']) {
                    continue;
                }
                if (in_array($filterItem['field'], $this->skipFields, true)) {
                    continue;
                }

                $this->applySingleFilter($query, $filterItem);
            }
        });
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
        $value = $filterObj['value'];
        $operator = strtolower($filterObj['operator'] ?? 'equals'); // "equals" or "contains"
        $logic = strtoupper($filterObj['logic'] ?? 'AND');       // "AND", "NOT"

        $callback = function (Builder $inner) use ($fieldname, $value, $operator) {

            // === 1. Simple Fields ===
            if (array_key_exists($fieldname, $this->simpleFields)) {
                $column = $this->simpleFields[$fieldname];

                $this->applyWhereWithOperator($inner, $column, $value, $operator);
                return;
            }

            // === 2. Relational or Morph ===
            if (isset($this->relationMap[$fieldname])) {
                $meta = $this->relationMap[$fieldname];

                // --- Morph Relation ---
                if (!empty($meta['morph'])) {
                    $this->applyMorphRelation($inner, $meta, $value, $operator);

                    return;
                }

                // --- Normal Relation ---
                $this->applyNormalRelation($meta, $inner, $value, $operator);

                return;
            }

            
            // // === 3. Handle assignedTo ===
            if ($fieldname === 'assigned_to') {
                $this->handleAssignedTo($value, $inner, $operator);
                return;
            }

            // === 4. Direct column - CustomFields ===
            $column = 'assets.' . $fieldname;

            if (!Schema::hasColumn('assets', $fieldname)) {
                return;
            }

            $this->applyWhereWithOperator($inner, $column, $value, $operator);
        };

        // === Apply logic ===
        $this->applyLogic($logic, $q, $callback, $fieldname);
    }

    protected function applyNormalRelation($meta, $inner, $value, $operator){
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
    }

    protected function applyMorphRelation($inner, $meta, $value, $operator){
        
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
    }

    protected function applyAssignedToLocation($inner, $value, $operator){
        $inner->where(function ($query) use ($value, $operator) {
            $query->whereHas('assignedToLocation', function ($q) use ($value, $operator) {
                $this->applyRelationalValue($q, $value['value'], $operator, ['column' => 'locations.name']);
            });
        });
    }

    protected function handleAssignedTo($value, $inner, $operator){

        // Check if type is valid
        $validTypes = [Asset::class, Location::class, User::class];
        if (!in_array($value['type'], $validTypes)) {
            throw new UnexpectedValueException('You\'ve provided an invalid type');
        }
                
        if ($value['value'] == '') {
            return;
        }

        // === 3a. Handle assignedTo location ===
        if ($value['type'] === Location::class) {
            $this->applyAssignedToLocation($inner, $value, $operator);
        }

        // === 3b. Handle assignedTo asset ===
        else if ($value['type'] === Asset::class) {
            $assignedValue = $value['value'];

            $this->applyAssignedToAsset($inner, $assignedValue, $operator);
        }
        // === 3c. Handle assignedTo user ===
        else if ($value['type'] === User::class) {
            $assignedValue = trim((string) ($value['value'] ?? ''));

            $this->applyAssignedToUser($inner, $assignedValue, $operator);
        }
    }

    protected function applyAssignedToUser($inner, $assignedValue, $operator){
        // Non-empty search: split into tokens
        $tokens = preg_split('/\s+/', $assignedValue, -1, PREG_SPLIT_NO_EMPTY);
        $inner->where(function ($q) use ($tokens, $operator) {
            $q->whereHas('assignedToUser', function ($qq) use ($tokens, $operator) {
                if (count($tokens) === 1) {
                    $term = $tokens[0];

                    // single token: match first_name OR last_name
                    $qq->where(function ($r) use ($term, $operator) {
                        $this->applyRelationalValue($r, $term, $operator, ['column' => 'users.first_name']);
                    })->orWhere(function ($r) use ($term, $operator) {
                        $this->applyRelationalValue($r, $term, $operator, ['column' => 'users.last_name']);
                    });
                    return;
                }

                // multiple tokens: first => first_name, rest => last_name
                $first = array_shift($tokens);
                $last = implode(' ', $tokens);

                $qq->where(function ($r) use ($first, $operator) {
                    $this->applyRelationalValue($r, $first, $operator, ['column' => 'users.first_name']);
                })->where(function ($r) use ($last, $operator) {
                    $this->applyRelationalValue($r, $last, $operator, ['column' => 'users.last_name']);
                });
            });
        });
    }
    protected function applyAssignedToAsset($inner, $assignedValue, $operator){
        // Ensure parent asset has an assigned asset and the assigned type is Asset.
        // Use a whereExists subquery that selects a real column (b.id) — no DB::raw required.
        $inner->where(function ($q) use ($assignedValue, $operator) {
            $q->whereNotNull('assets.assigned_to')
            ->where('assets.assigned_type', Asset::class)
                ->whereExists(function ($sub) use ($assignedValue, $operator) {
                    $sub->from('assets as b')
                ->select('b.id')
                    ->whereColumn('b.id', 'assets.assigned_to')
                    ->where(function ($q2) use ($assignedValue, $operator) {
                        if ($operator === 'equals') {
                            $q2->where('b.asset_tag', '=', $assignedValue)
                            ->orWhere('b.name', '=', $assignedValue);
                        } else {
                            $q2->where('b.asset_tag', 'LIKE', '%' . $assignedValue . '%')
                            ->orWhere('b.name', 'LIKE', '%' . $assignedValue . '%');
                        }
                });
            });
        });
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
        } else {
            $query->where(function ($q) use ($column, $value) {
                foreach ($value as $v) {
                    $q->orWhere($column, 'LIKE', '%' . $v . '%');
                }
            });
        }
    }

    protected function applyLogic($logic, $q, $callback, $fieldname){
        switch ($logic) {
            case 'NOT':
                $q->where(function ($outer) use ($callback, $fieldname) {
                    $outer->whereNot($callback);

                    // Only add "OR IS NULL" for direct columns (not relationships)
                    if (!Str::contains($fieldname, '.')  && Schema::hasColumn('assets', $fieldname)) {
                        $outer->orWhereNull('assets.' . $fieldname);
                    }
                });
                break;
            case 'AND':
            default:
                $q->where($callback);
                break;
        }
    }

    /**
     *
     *
     * @param Builder $query
     * @param string  $qualifiedField
     * @param array   $filters
     * @param bool    $isDateTime
     */

    public function applyDateRangeFilter($query, $qualifiedField, $filters, bool $isDateTime = false)
    {
        $start = null;
        $end = null;

        $fieldNameOnly = \Illuminate\Support\Str::afterLast($qualifiedField, '.');

        foreach ($filters as $filter) {
            if (!isset($filter['field'], $filter['value']) || !is_array($filter['value'])) {
                continue;
            }
            if ($filter['field'] !== $fieldNameOnly) {
                continue;
            }
            if (array_key_exists('startDate', $filter['value'])) {
                $start = $filter['value']['startDate'];
            }
            if (array_key_exists('endDate', $filter['value'])) {
                $end = $filter['value']['endDate'];
            }
        }

        if (!$start && !$end) {
            return $query;
        }

        if ($isDateTime) {

            if ($start && $end) {
                $query->whereBetween($qualifiedField, [
                    \Carbon\Carbon::parse($start)->startOfDay(),
                    \Carbon\Carbon::parse($end)->endOfDay(),
                ]);
            } elseif ($start) {
                $query->where($qualifiedField, '>=', \Carbon\Carbon::parse($start)->startOfDay());
            } elseif ($end) {
                $query->where($qualifiedField, '<=', \Carbon\Carbon::parse($end)->endOfDay());
            }
        } else {
            if ($start && $end) {
                $query->whereBetween($qualifiedField, [
                    \Carbon\Carbon::parse($start)->toDateString(),
                    \Carbon\Carbon::parse($end)->toDateString(),
                ]);
            } elseif ($start) {
                $query->whereDate($qualifiedField, '>=', \Carbon\Carbon::parse($start)->toDateString());
            } elseif ($end) {
                $query->whereDate($qualifiedField, '<=', \Carbon\Carbon::parse($end)->toDateString());
            }
        }

        return $query;
    }
}
