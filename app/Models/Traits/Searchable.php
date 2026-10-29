<?php

namespace App\Models\Traits;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This trait allows for cleaner searching of models,
 * moving from complex queries to an easier declarative syntax.
 *
 * This handles all the out of the box advanced search stuff (using the "advanced search" bootstrap table plugin),
 * allowing you to just define which attributes and relations should be searched, and then it does the rest.
 *
 * You can override these trait methods (for example, advancedSearch) if you need different behavior, but this really
 * should cover most of the use cases, and allows you to easily add searching to your models without having to
 * write complex queries.
 *
 * To use this:
 *
 * 1. Make sure the model has $searchableAttributes and $searchableRelations set
 * 2. Make sure you import the App\Models\Traits\Searchable trait and use Searchable in the model
 * 3. Make sure you check the request for the request input filter or search and then invoke the TextSearch scope, like:
 *
 * if ($request->filled('filter') || $request->filled('search')) {
 *       $whateverModel->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
 * }
 * 4. Set the "data-advanced-search="true" in the
 *
 *
 * @author Till Deeke <kontakt@tilldeeke.de>
 */
trait Searchable
{
    /**
     * Per-class cache for the custom field filter map, keyed by db_column / lowercase name.
     * Populated lazily; cleared via flushCustomFieldFilterMap().
     *
     * @var array<string, string>|null
     */
    private static ?array $customFieldFilterMapCache = null;

    /**
     * Performs a search on the model, using the provided search terms
     *
     * @param  Builder  $query  The query to start the search on
     * @param  string  $search
     * @return Builder A query with added "where" clauses
     */
    public function scopeTextSearch($query, $search)
    {
        $preparedSearch = $this->prepareSearchInput(is_array($search) ? implode(' ', $search) : (string) $search);
        $terms = $preparedSearch['terms'];
        $filters = $preparedSearch['filters'];
        $filterOperator = $preparedSearch['filter_operator'];

        if (! empty($filters)) {
            // Structured advanced-search filters are mutually exclusive with free-text terms.
            // Once we detect structured payloads, we avoid the broad OR-based free-text path.
            return $this->applySearchFilters($query, $filters, $filterOperator);
        }

        if (empty($terms)) {
            return $query;
        }

        // Wrap the search methods in a nested where so the OR clauses
        // they add stay confined inside one AND group with any
        // surrounding scope (FMCS CompanyableScope, per-controller
        // WHERE filters, etc). Without this nesting a multi-term
        // search produces SQL shaped like
        //   WHERE scope AND col LIKE '%a%' OR col LIKE '%b%'
        // which binds AND tighter than OR, letting the second term
        // match rows outside the scope.
        //
        // advancedTextSearch overrides receive two builders: $outer
        // for leftJoin calls (Eloquent nested-where closures cannot
        // carry joins) and $nested for the orWhere clauses that
        // reference the joined columns. The $nested parameter keeps
        // the join-column where clauses inside the scope AND group.
        return $query->where(function (Builder $nested) use ($query, $terms) {
            /**
             * Search the attributes of this model
             */
            $nested = $this->searchAttributes($nested, $terms);

            /**
             * Search through the custom fields of the model
             */
            $nested = $this->searchCustomFields($nested, $terms);

            /**
             * Search through the relations of the model
             */
            $nested = $this->searchRelations($nested, $terms);

            /**
             * Search for additional attributes defined by the model
             */
            $this->advancedTextSearch($query, $nested, $terms);
        });
    }

    /**
     * Parse free-text terms and structured filters for TextSearch.
     *
     * Supported filter inputs:
     * - {"field":"value"}
     * - filter:{"field":"value"}
     */
    private function prepareSearchInput(string $search): array
    {
        $search = trim($search);

        $parsedFilters = $this->parseStructuredFilterPayload($search);

        if ($parsedFilters !== null) {
            return [
                'terms' => [],
                'filters' => $parsedFilters,
                'filter_operator' => $this->resolveStructuredFilterOperator(),
            ];
        }

        return [
            'terms' => $this->prepeareSearchTerms($search),
            'filters' => [],
            'filter_operator' => 'and',
        ];
    }

    /**
     * Resolve the structured advanced-search operator from the current request.
     */
    private function resolveStructuredFilterOperator(): string
    {
        $operator = strtolower((string) request()->input('filter_operator', 'and'));

        return $operator === 'or' ? 'or' : 'and';
    }

    /**
     * Normalize a structured filter payload into scalar string filters.
     */
    private function parseStructuredFilterPayload(string $search): ?array
    {
        if ($search === '') {
            return null;
        }

        $payload = $search;

        if (str_starts_with($search, 'filter:')) {
            // Some callers send filter payloads with an explicit "filter:" prefix.
            $payload = substr($search, 7);
        } elseif (! (str_starts_with($search, '{') && str_ends_with($search, '}'))) {
            return null;
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return null;
        }

        $filters = [];

        foreach ($decoded as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (! is_scalar($value) && $value !== null) {
                continue;
            }

            $normalizedValue = trim((string) ($value ?? ''));

            if ($normalizedValue === '') {
                // Ignore empty fields so clearing an input does not create noisy no-op filters.
                continue;
            }

            $filters[$key] = $normalizedValue;
        }

        return $filters;
    }

    /**
     * Prepares the search term, splitting and cleaning it up
     *
     * @TODO: see if there's a way to tweak the advanced search plugin to split the terms on the frontend, so we don't have to do it here. This is pretty hacky and fragile, since it relies on the user inputting " OR " between search terms, which is not very user-friendly, but we could potentially hack the advanced search extension itself to add an operator. (That extension's UI is pretty terrible, but it's what we have)
     *
     * @param  string  $search  The search term
     * @return array An array of search terms
     */
    private function prepeareSearchTerms($search)
    {
        return explode(' AND ', $search);
    }

    /**
     * Apply structured filters to searchable attributes and relations.
     *
     * @param  array<string, string>  $filters
     */
    private function applySearchFilters(Builder $query, array $filters, string $filterOperator = 'and'): Builder
    {
        if ($filterOperator === 'or') {
            $query->where(function (Builder $filterQuery) use ($filters) {
                foreach ($filters as $filterKey => $filterValue) {
                    $this->applySingleSearchFilter($filterQuery, $filterKey, $filterValue, 'or');
                }
            });

            return $query;
        }

        foreach ($filters as $filterKey => $filterValue) {
            $this->applySingleSearchFilter($query, $filterKey, $filterValue);
        }

        return $query;
    }

    /**
     * Parse a raw filter value for an optional negation, null-check, or exact-match prefix.
     *
     * Supported syntax:
     *   - "!flarb"      → operator = not_like,    value = "flarb"
     *   - "not:flarb"   → operator = not_like,    value = "flarb"
     *   - "is:null"     → operator = is_null,      value = ""   (reserved token)
     *   - "is:not_null" → operator = is_not_null,  value = ""   (reserved token)
     *   - "is:flarb"    → operator = exact,        value = "flarb"  (exact equality)
     *   - "is_not:flarb"→ operator = exact_not,    value = "flarb"  (exact inequality)
     *
     * `is:null` and `is:not_null` are checked before the generic `is:` prefix so they always
     * resolve to their dedicated null-check operators regardless of casing.
     *
     * The legacy `negate` boolean is preserved alongside `operator` so that
     * existing callers that only check `negate` still work correctly.
     *
     * @return array{value: string, negate: bool, operator: string}
     */
    private function parseFilterValue(string $raw): array
    {
        $lower = strtolower($raw);

        if ($lower === 'is:null') {
            // Reserved token: interpreted as null-check operator, not exact match string.
            return ['value' => '', 'negate' => false, 'operator' => 'is_null'];
        }

        if ($lower === 'is:not_null') {
            // Reserved token: interpreted as non-null check operator.
            return ['value' => '', 'negate' => false, 'operator' => 'is_not_null'];
        }

        if (str_starts_with($lower, 'is:')) {
            // Generic exact-match prefix. This is checked after reserved is:null/is:not_null tokens.
            $exactValue = ltrim(substr($raw, 3));

            return ['value' => $exactValue, 'negate' => false, 'operator' => 'exact'];
        }

        if (str_starts_with($lower, 'is_not:')) {
            $exactNotValue = ltrim(substr($raw, 7));

            return ['value' => $exactNotValue, 'negate' => true, 'operator' => 'exact_not'];
        }

        if (str_starts_with($raw, '!')) {
            return ['value' => substr($raw, 1), 'negate' => true, 'operator' => 'not_like'];
        }

        if (str_starts_with($lower, 'not:')) {
            return ['value' => substr($raw, 4), 'negate' => true, 'operator' => 'not_like'];
        }

        return ['value' => $raw, 'negate' => false, 'operator' => 'like'];
    }

    /**
     * Apply a single structured filter using the provided boolean operator.
     *
     * Negation: if the filter value is prefixed with "!" or "not:", the filter
     * uses NOT LIKE (for attributes/custom fields) or whereDoesntHave (for
     * relations), effectively excluding records matching the value.
     *
     * For relation filters, negation uses NOT LIKE inside whereHas, meaning
     * "has a related record where the column does NOT contain the value".
     * Records with no related record (e.g. unassigned assets) are excluded;
     * use a plain empty-string filter if you need to match NULLs.
     */
    private function applySingleSearchFilter(Builder $query, string $filterKey, string $filterValue, string $boolean = 'and'): Builder
    {
        $parsed = $this->parseFilterValue($filterValue);
        $value = $parsed['value'];
        $negate = $parsed['negate'];
        $operator = $parsed['operator'];

        // IS NULL / IS NOT NULL are handled before value-based filtering,
        // because there is no meaningful value to pass to LIKE for them.
        if ($operator === 'is_null' || $operator === 'is_not_null') {
            return $this->applyNullFilter($query, $filterKey, $operator === 'is_null', $boolean);
        }

        // Skip gracefully if stripping the prefix leaves an empty value.
        if ($value === '') {
            return $query;
        }

        $searchableAttributes = $this->getSearchableAttributes();
        $searchableCounts = $this->getSearchableCounts();
        $searchableRelations = $this->getSearchableRelations();
        $table = $this->getTable();
        $whereMethod = $boolean === 'or' ? 'orWhere' : 'where';
        $likeOperator = $negate ? 'NOT LIKE' : 'LIKE';
        $isExactOperator = in_array($operator, ['exact', 'exact_not'], true);
        $exactComparisonOperator = $operator === 'exact_not' ? '!=' : '=';

        if (in_array($filterKey, $searchableAttributes, true)) {
            if ($isExactOperator) {
                $query->{$whereMethod}($table.'.'.$filterKey, $exactComparisonOperator, $value);
            } else {
                $query->{$whereMethod}($table.'.'.$filterKey, $likeOperator, '%'.$value.'%');
            }

            return $query;
        }

        // Handle virtual columns — keys that are not real DB columns but map to a set
        // of real columns searched via CONCAT (e.g. "name" → first_name + last_name on User).
        $virtualColumns = $this->getSearchableVirtualColumns();

        if (array_key_exists($filterKey, $virtualColumns)) {
            $qualifiedColumns = array_map(
                fn ($col) => $table.'.'.$col,
                $virtualColumns[$filterKey]
            );

            if ($isExactOperator) {
                // Exact match on the full CONCAT'd value, e.g. "John Smith" matches only
                // users whose first_name + ' ' + last_name equals exactly "John Smith".
                $concatSql = $this->buildMultipleColumnSearch($qualifiedColumns);
                // buildMultipleColumnSearch intentionally returns a fragment ending in "LIKE ?";
                // for exact matches we rewrite only the operator and keep the same SQL scaffold.
                $concatSql = str_replace(' LIKE ?', $operator === 'exact_not' ? ' <> ?' : ' = ?', $concatSql);
                $rawMethod = $boolean === 'or' ? 'orWhereRaw' : 'whereRaw';
                $query->{$rawMethod}($concatSql, [$value]);
            } else {
                $concatSql = $this->buildMultipleColumnSearch($qualifiedColumns);

                if ($negate) {
                    $concatSql = str_replace(' LIKE ?', ' NOT LIKE ?', $concatSql);
                }

                $rawMethod = $boolean === 'or' ? 'orWhereRaw' : 'whereRaw';
                $query->{$rawMethod}($concatSql, ['%'.$value.'%']);
            }

            return $query;
        }

        if (in_array($filterKey, $searchableCounts, true)) {
            return $this->applyCountAliasFilter($query, $filterKey, $value, $boolean, $negate, $isExactOperator);
        }

        // Check if this is a custom field (only for Assets - for *now*).
        // Only db_column keys (e.g. "_snipeit_cpu_4") are accepted to avoid
        // collisions with standard attributes or relation filter keys.
        if ($this instanceof Asset) {
            $dbColumn = $this->resolveCustomFieldDbColumn($filterKey);

            if ($dbColumn !== null) {
                if ($isExactOperator) {
                    $query->{$whereMethod}($table.'.'.$dbColumn, $exactComparisonOperator, $value);
                } else {
                    $query->{$whereMethod}($table.'.'.$dbColumn, $likeOperator, '%'.$value.'%');
                }

                return $query;
            }
        }

        // Columns that live on a related table but surface as a
        // top-level filter key (e.g. the "Title" column on the hardware
        // list renders users.jobtitle but admins expect to filter it
        // like a native asset attribute). Falls through to the normal
        // relation-key path if no mapping is defined.
        $relationColumnMap = $this->getSearchableRelationColumns();
        if (array_key_exists($filterKey, $relationColumnMap)) {
            return $this->applyRelationColumnFilter(
                query: $query,
                mapping: $relationColumnMap[$filterKey],
                value: $value,
                boolean: $boolean,
                operator: $operator,
            );
        }

        $resolvedRelationKey = $this->resolveSearchableRelationKey($filterKey, $searchableRelations);

        if ($resolvedRelationKey === null) {
            return $query;
        }

        if ($this->isAssignedToRelationKey($resolvedRelationKey)) {
            return $this->applyAssignedToRelationFilter($query, $resolvedRelationKey, $value, $boolean, $negate, $operator);
        }

        $relationColumns = $this->getStructuredFilterRelationColumns(
            filterKey: $filterKey,
            resolvedRelationKey: $resolvedRelationKey,
            searchableRelations: $searchableRelations,
        );

        // For negated relation filters (e.g. location: !dam), include rows with
        // no related record as well as rows with related records that do not match.
        // This aligns advanced-search behavior with user expectation for "not X".
        if ($operator === 'not_like' || $operator === 'exact_not') {
            $compoundMethod = $boolean === 'or' ? 'orWhere' : 'where';

            $query->{$compoundMethod}(function (Builder $compoundQuery) use ($resolvedRelationKey, $relationColumns, $value, $operator): void {
                // Critical behavior: "not X" on relations should include records with no relation.
                // Example: location=!dam should include users without a location.
                $compoundQuery->doesntHave($resolvedRelationKey)
                    ->orWhereHas($resolvedRelationKey, function (Builder $relationQuery) use ($resolvedRelationKey, $relationColumns, $value, $operator): void {
                        $relationTable = $this->getRelationTable($resolvedRelationKey);
                        $firstConditionAdded = false;
                        $relationComparisonOperator = $operator === 'exact_not' ? '!=' : 'NOT LIKE';
                        $relationComparisonValue = $operator === 'exact_not' ? $value : '%'.$value.'%';

                        foreach ($relationColumns as $relationColumn) {
                            if (! $firstConditionAdded) {
                                $relationQuery->where($relationTable.'.'.$relationColumn, $relationComparisonOperator, $relationComparisonValue);
                                $firstConditionAdded = true;

                                continue;
                            }

                            // For negation we AND the NOT LIKE conditions so all columns must not match.
                            $relationQuery->where($relationTable.'.'.$relationColumn, $relationComparisonOperator, $relationComparisonValue);
                        }

                        if (($resolvedRelationKey === 'adminuser') || ($resolvedRelationKey === 'user')) {
                            $concatSql = $this->buildMultipleColumnSearch([
                                'users.first_name',
                                'users.last_name',
                                'users.display_name',
                            ]);

                            if ($operator === 'exact_not') {
                                $relationQuery->whereRaw(str_replace(' LIKE ?', ' <> ?', $concatSql), [$value]);
                            } else {
                                $relationQuery->whereRaw(str_replace('LIKE', 'NOT LIKE', $concatSql), ["%{$value}%"]);
                            }
                        }
                    });
            });

            return $query;
        }

        $relationMethod = $boolean === 'or' ? 'orWhereHas' : 'whereHas';

        $query->{$relationMethod}($resolvedRelationKey, function (Builder $relationQuery) use ($resolvedRelationKey, $relationColumns, $value, $likeOperator, $operator) {
            $relationTable = $this->getRelationTable($resolvedRelationKey);
            $firstConditionAdded = false;

            foreach ($relationColumns as $relationColumn) {
                if (! $firstConditionAdded) {
                    if ($operator === 'exact') {
                        $relationQuery->where($relationTable.'.'.$relationColumn, '=', $value);
                    } elseif ($operator === 'exact_not') {
                        $relationQuery->where($relationTable.'.'.$relationColumn, '!=', $value);
                    } else {
                        $relationQuery->where($relationTable.'.'.$relationColumn, $likeOperator, '%'.$value.'%');
                    }
                    $firstConditionAdded = true;

                    continue;
                }

                if ($operator === 'exact') {
                    // For exact matches across multiple columns, OR them — any column matching
                    // the exact value is sufficient (e.g. name OR slug).
                    $relationQuery->orWhere($relationTable.'.'.$relationColumn, '=', $value);
                } elseif ($operator === 'exact_not') {
                    // For exact exclusions we AND the conditions so no column can equal the value.
                    $relationQuery->where($relationTable.'.'.$relationColumn, '!=', $value);
                } elseif ($likeOperator === 'NOT LIKE') {
                    // For negation we AND the NOT LIKE conditions so all columns must not match.
                    $relationQuery->where($relationTable.'.'.$relationColumn, $likeOperator, '%'.$value.'%');
                } else {
                    // For normal LIKE we OR them so any column matching is sufficient.
                    $relationQuery->orWhere($relationTable.'.'.$relationColumn, $likeOperator, '%'.$value.'%');
                }
            }

            if (($resolvedRelationKey === 'adminuser') || ($resolvedRelationKey === 'user')) {
                $concatSql = $this->buildMultipleColumnSearch([
                    'users.first_name',
                    'users.last_name',
                    'users.display_name',
                ]);

                if ($operator === 'exact') {
                    $concatSql = str_replace(' LIKE ?', ' = ?', $concatSql);
                    $relationQuery->orWhereRaw($concatSql, [$value]);
                } elseif ($operator === 'exact_not') {
                    $concatSql = str_replace(' LIKE ?', ' <> ?', $concatSql);
                    $relationQuery->whereRaw($concatSql, [$value]);
                } elseif ($likeOperator === 'NOT LIKE') {
                    $relationQuery->whereRaw(str_replace('LIKE', 'NOT LIKE', $concatSql), ["%{$value}%"]);
                } else {
                    $relationQuery->orWhereRaw($concatSql, ["%{$value}%"]);
                }
            }
        });

        return $query;
    }

    /**
     * Resolve alias keys to configured searchable relation keys.
     *
     * Resolution order:
     *  1. Direct match in $searchableRelations (relation name used as-is by the API)
     *  2. $searchableRelationAliases (API/transformer key → Eloquent relation name)
     *  3. Built-in assigned_to ↔ assignedTo camel/snake alias
     */
    private function resolveSearchableRelationKey(string $filterKey, array $searchableRelations): ?string
    {
        // 1. Direct match — the filter key is already the relation name.
        if (array_key_exists($filterKey, $searchableRelations)) {
            return $filterKey;
        }

        // 2. Model-defined aliases — e.g. 'status_label' => 'status'.
        $aliases = $this->getSearchableRelationAliases();

        if (array_key_exists($filterKey, $aliases)) {
            $aliasedRelation = $aliases[$filterKey];

            if (array_key_exists($aliasedRelation, $searchableRelations)) {
                return $aliasedRelation;
            }
        }

        // 3. Built-in camel/snake alias for the polymorphic assignee relation.
        if ($filterKey === 'assigned_to' && array_key_exists('assignedTo', $searchableRelations)) {
            return 'assignedTo';
        }

        if ($filterKey === 'assignedTo' && array_key_exists('assigned_to', $searchableRelations)) {
            return 'assigned_to';
        }

        return null;
    }

    /**
     * Determine whether a relation key represents polymorphic assignee lookups.
     */
    private function isAssignedToRelationKey(string $relationKey): bool
    {
        return in_array($relationKey, ['assigned_to', 'assignedTo'], true);
    }

    /**
     * Apply filters for assignees with type-specific searchable columns.
     *
     * When $negate is true, NOT LIKE is used inside whereHasMorph, so results
     * are records that have an assignee whose columns do NOT contain $filterValue.
     * (Records with no assignee are excluded; they do not satisfy "has an assignee
     * where column NOT LIKE '%value%'".)
     */
    private function applyAssignedToRelationFilter(Builder $query, string $relationKey, string $filterValue, string $boolean = 'and', bool $negate = false, string $operator = 'like'): Builder
    {
        $relationName = $this->resolveAssignedToRelationName();

        if ($relationName === null) {
            return $query;
        }

        $likeOperator = $negate ? 'NOT LIKE' : 'LIKE';
        $isExactOperator = in_array($operator, ['exact', 'exact_not'], true);
        $exactComparisonOperator = $operator === 'exact_not' ? '!=' : '=';
        $relationMethod = $boolean === 'or' ? 'orWhereHasMorph' : 'whereHasMorph';

        return $query->{$relationMethod}(
            $relationName,
            [User::class, Asset::class, Location::class],
            function (Builder $assigneeQuery, string $assigneeType) use ($filterValue, $likeOperator, $negate, $operator, $isExactOperator, $exactComparisonOperator) {
                $columns = $this->getAssigneeColumnsByType($assigneeType);

                if (empty($columns)) {
                    return;
                }

                $table = (new $assigneeType)->getTable();
                $firstConditionAdded = false;

                foreach ($columns as $column) {
                    if (! $firstConditionAdded) {
                        if ($isExactOperator) {
                            $assigneeQuery->where($table.'.'.$column, $exactComparisonOperator, $filterValue);
                        } else {
                            $assigneeQuery->where($table.'.'.$column, $likeOperator, '%'.$filterValue.'%');
                        }
                        $firstConditionAdded = true;

                        continue;
                    }

                    // For negation, AND the conditions (all columns must not match).
                    // For normal LIKE, OR them (any column matching is sufficient).
                    if ($operator === 'exact') {
                        $assigneeQuery->orWhere($table.'.'.$column, '=', $filterValue);
                    } elseif ($operator === 'exact_not') {
                        $assigneeQuery->where($table.'.'.$column, '!=', $filterValue);
                    } else {
                        $negate
                            ? $assigneeQuery->where($table.'.'.$column, $likeOperator, '%'.$filterValue.'%')
                            : $assigneeQuery->orWhere($table.'.'.$column, $likeOperator, '%'.$filterValue.'%');
                    }
                }

                if ($assigneeType === User::class) {
                    $concatSql = $this->buildMultipleColumnSearch(['users.first_name', 'users.last_name']);

                    if ($operator === 'exact') {
                        $assigneeQuery->orWhereRaw(str_replace(' LIKE ?', ' = ?', $concatSql), [$filterValue]);
                    } elseif ($operator === 'exact_not') {
                        $assigneeQuery->whereRaw(str_replace(' LIKE ?', ' <> ?', $concatSql), [$filterValue]);
                    } else {
                        $negate
                            ? $assigneeQuery->whereRaw(str_replace('LIKE', 'NOT LIKE', $concatSql), ["%{$filterValue}%"])
                            : $assigneeQuery->orWhereRaw($concatSql, ["%{$filterValue}%"]);
                    }
                }
            }
        );
    }

    /**
     * Get the searchable columns for a given assignee morph type.
     *
     * Users have no "name" column, only first_name/last_name/username/display_name.
     * Assets use asset_tag as the primary identifier (name is nullable).
     * Locations use name.
     */
    private function getAssigneeColumnsByType(string $assigneeType): array
    {
        return match ($assigneeType) {
            User::class => ['first_name', 'last_name', 'username', 'display_name'],
            Asset::class => ['asset_tag', 'name'],
            Location::class => ['name'],
            default => [],
        };
    }

    /**
     * Resolve the actual relation method name for the assignedTo polymorphic relation.
     *
     * Models may define it as "assignedTo" (camelCase) or "assigned_to" (snake_case).
     * We prefer "assignedTo" when both exist.
     */
    private function resolveAssignedToRelationName(): ?string
    {
        if (method_exists($this, 'assignedTo')) {
            return 'assignedTo';
        }

        if (method_exists($this, 'assigned_to')) {
            return 'assigned_to';
        }

        return null;
    }

    /**
     * Apply filtering on computed count aliases (for example withCount aliases).
     */
    private function applyCountAliasFilter(Builder $query, string $countAlias, string $filterValue, string $boolean = 'and', bool $negate = false, bool $exact = false): Builder
    {
        $havingMethod = $boolean === 'or' ? 'orHaving' : 'having';

        if (is_numeric($filterValue)) {
            $operator = $negate ? '!=' : '=';

            return $query->{$havingMethod}($countAlias, $operator, (int) $filterValue);
        }

        if ($exact) {
            $operator = $negate ? '!=' : '=';

            return $query->{$havingMethod}($countAlias, $operator, $filterValue);
        }

        $likeOperator = $negate ? 'NOT LIKE' : 'LIKE';

        return $query->{$havingMethod}($countAlias, $likeOperator, '%'.$filterValue.'%');
    }

    /**
     * Apply an IS NULL / IS NOT NULL filter for the given filter key.
     *
     * Supported targets:
     *
     *   Direct attributes  → WHERE col IS [NOT] NULL
     *
     *   Virtual columns    → IS NULL:     all constituent columns must be null
     *                        IS NOT NULL: at least one constituent column must not be null
     *
     *   Relation keys      → IS NULL:     doesntHave (no related record)
     *                        IS NOT NULL: whereHas   (has a related record)
     *
     * Any unrecognised key is silently ignored.
     */
    private function applyNullFilter(Builder $query, string $filterKey, bool $isNull, string $boolean = 'and'): Builder
    {
        $table = $this->getTable();
        $searchableAttributes = $this->getSearchableAttributes();

        // Custom field db_column key (Asset only).
        if ($this instanceof Asset) {
            $dbColumn = $this->resolveCustomFieldDbColumn($filterKey);

            if ($dbColumn !== null) {
                $column = $table.'.'.$dbColumn;
                $isText = $this->isTextTypeColumn($table, $dbColumn);

                $method = $boolean === 'or' ? 'orWhere' : 'where';

                $query->{$method}(function (Builder $subQuery) use ($column, $isNull, $isText): void {
                    if ($isNull) {
                        $subQuery->whereNull($column);
                        if ($isText) {
                            $subQuery->orWhere($column, '=', '');
                        }

                        return;
                    }

                    $subQuery->whereNotNull($column);
                    if ($isText) {
                        $subQuery->where($column, '!=', '');
                    }
                });

                return $query;
            }
        }

        // Direct attribute column.
        if (in_array($filterKey, $searchableAttributes, true)) {
            $column = $table.'.'.$filterKey;
            $isText = $this->isTextTypeColumn($table, $filterKey);
            $method = $boolean === 'or' ? 'orWhere' : 'where';

            $query->{$method}(function (Builder $subQuery) use ($column, $isNull, $isText): void {
                if ($isNull) {
                    $subQuery->whereNull($column);
                    if ($isText) {
                        $subQuery->orWhere($column, '=', '');
                    }

                    return;
                }

                $subQuery->whereNotNull($column);
                if ($isText) {
                    $subQuery->where($column, '!=', '');
                }
            });

            return $query;
        }

        // Virtual columns (e.g. 'name' → ['first_name', 'last_name'] on User).
        $virtualColumns = $this->getSearchableVirtualColumns();

        if (array_key_exists($filterKey, $virtualColumns)) {
            $qualifiedColumns = array_map(
                fn ($col) => $table.'.'.$col,
                $virtualColumns[$filterKey]
            );

            if ($isNull) {
                // All constituent columns must be null (= no name at all).
                foreach ($qualifiedColumns as $col) {
                    $query->whereNull($col);
                }
            } else {
                // At least one constituent column must have a value.
                $query->where(function (Builder $sub) use ($qualifiedColumns): void {
                    foreach ($qualifiedColumns as $col) {
                        $sub->orWhereNotNull($col);
                    }
                });
            }

            return $query;
        }

        // Relation column: filter key is a column on a related table
        // surfaced as a top-level filter (e.g. jobtitle = users.jobtitle
        // via assignedTo on assets). Dispatched the same way as regular
        // relation-column filtering above (#19708).
        $relationColumnMap = $this->getSearchableRelationColumns();
        if (array_key_exists($filterKey, $relationColumnMap)) {
            return $this->applyRelationColumnNullFilter(
                query: $query,
                mapping: $relationColumnMap[$filterKey],
                isNull: $isNull,
                boolean: $boolean,
            );
        }

        // Relation key: no related record = "null", has a related record = "not null".
        $searchableRelations = $this->getSearchableRelations();
        $resolvedRelationKey = $this->resolveSearchableRelationKey($filterKey, $searchableRelations);

        if ($resolvedRelationKey !== null && $this->isAssignedToRelationKey($resolvedRelationKey)) {
            $method = $boolean === 'or' ? 'orWhere' : 'where';
            // Polymorphic assignment is present only when both columns are set; null matches either side missing.

            if ($isNull) {
                $query->{$method}(function (Builder $assigneeNullQuery) use ($table): void {
                    $assigneeNullQuery->whereNull($table.'.assigned_to')
                        ->orWhereNull($table.'.assigned_type');
                });
            } else {
                $query->{$method}(function (Builder $assigneeNotNullQuery) use ($table): void {
                    $assigneeNotNullQuery->whereNotNull($table.'.assigned_to')
                        ->whereNotNull($table.'.assigned_type');
                });
            }

            return $query;
        }

        if ($resolvedRelationKey !== null) {
            if ($isNull) {
                $method = $boolean === 'or' ? 'orDoesntHave' : 'doesntHave';
                $query->{$method}($resolvedRelationKey);
            } else {
                $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';
                $query->{$method}($resolvedRelationKey);
            }

            return $query;
        }

        return $query;
    }

    /**
     * Searches the models attributes for the search terms
     *
     * @param  $query  Builder
     * @param  $terms  array
     * @return Builder
     */
    private function searchAttributes(Builder $query, array $terms)
    {
        $table = $this->getTable();

        $firstConditionAdded = false;

        foreach ($this->getSearchableAttributes() as $column) {
            foreach ($terms as $term) {
                /**
                 * Making sure to only search in date columns if the search term consists of characters that can make up a MySQL timestamp!
                 *
                 * @see https://github.com/grokability/snipe-it/issues/4590
                 */
                if (! preg_match('/^[0-9 :-]++$/', $term) && in_array($column, $this->getDates())) {
                    continue;
                }

                /**
                 * We need to form the query properly, starting with a "where",
                 * otherwise the generated select is wrong.
                 *
                 * @todo This does the job, but is inelegant and fragile
                 */
                if (! $firstConditionAdded) {
                    $query = $query->where($table.'.'.$column, 'LIKE', '%'.$term.'%');

                    $firstConditionAdded = true;

                    continue;
                }

                $query = $query->orWhere($table.'.'.$column, 'LIKE', '%'.$term.'%');
            }
        }

        return $query;
    }

    /**
     * Searches the models custom fields for the search terms
     *
     * @param  $query  Builder
     * @param  $terms  array
     * @return Builder
     */
    private function searchCustomFields(Builder $query, array $terms)
    {

        /**
         * If we are searching on something other that an asset, skip custom fields.
         */
        if (! $this instanceof Asset) {
            return $query;
        }

        // Only pull unencrypted fields, since encrypted fields cannot be searched on
        $customFields = CustomField::query()
            ->whereNotNull('db_column')
            ->where('field_encrypted', 0)
            ->get(['db_column']);

        if ($customFields->isEmpty()) {
            return $query;
        }

        // Group custom-fields so all custom fields behave consistently as OR conditions.
        return $query->orWhere(function (Builder $customFieldQuery) use ($customFields, $terms): void {
            $firstConditionAdded = false;

            foreach ($customFields as $field) {
                foreach ($terms as $term) {
                    if (! $firstConditionAdded) {
                        $customFieldQuery->where($this->getTable().'.'.$field->db_column_name(), 'LIKE', '%'.$term.'%');
                        $firstConditionAdded = true;

                        continue;
                    }

                    $customFieldQuery->orWhere($this->getTable().'.'.$field->db_column_name(), 'LIKE', '%'.$term.'%');
                }
            }
        });
    }

    /**
     * Searches the models relations for the search terms
     *
     * @param  $query  Builder
     * @param  $terms  array
     */
    private function searchRelations(Builder $query, array $terms): Builder
    {
        foreach ($this->getSearchableRelations() as $relation => $columns) {

            // Polymorphic assignee relations need special per-type column handling
            // because users, assets, and locations each have different identifier columns.
            if ($this->isAssignedToRelationKey($relation)) {
                $query = $this->searchAssignedToRelation($query, $terms);

                continue;
            }

            $isUserRelation = in_array($relation, ['adminuser', 'user'], true);

            // Pre-build the concat SQL outside the closure so $this->buildMultipleColumnSearch()
            // doesn't need to be called inside a nested closure context.
            $concatSql = $isUserRelation
                ? $this->buildMultipleColumnSearch(['users.first_name', 'users.last_name'])
                : null;

            $query = $query->orWhereHas(
                $relation, function (Builder $relationQuery) use ($relation, $columns, $terms, $isUserRelation, $concatSql) {

                    // $table must be resolved inside the closure for self-referential relations
                    // (e.g. User->manager, User->adminuser). getRelationTable relies on the
                    // alias counter that orWhereHas increments before this callback runs.
                    $table = $this->getRelationTable($relation);

                    /**
                     * We need to form the query properly, starting with a "where",
                     * otherwise the generated nested select is wrong.
                     *
                     * @todo This does the job, but is inelegant and fragile
                     */
                    $firstConditionAdded = false;

                    foreach ($columns as $column) {
                        foreach ($terms as $term) {
                            if (! $firstConditionAdded) {
                                $relationQuery->where($table.'.'.$column, 'LIKE', '%'.$term.'%');
                                $firstConditionAdded = true;

                                continue;
                            }

                            $relationQuery->orWhere($table.'.'.$column, 'LIKE', '%'.$term.'%');
                        }
                    }

                    // Also search first+last name concatenated for user relations so that
                    // "John Smith" matches even when the terms are split across columns.
                    if ($isUserRelation && $concatSql !== null) {
                        foreach ($terms as $term) {
                            $relationQuery->orWhereRaw($concatSql, ["%{$term}%"]);
                        }
                    }
                }
            );
        }

        return $query;
    }

    /**
     * Search across the polymorphic assignee relation (assignedTo / assigned_to).
     *
     * Uses whereHasMorph so that each possible assignee type is constrained to the
     * columns that actually exist on that type:
     *   - User     → first_name, last_name, username, display_name
     *   - Asset    → asset_tag, name
     *   - Location → name
     */
    private function searchAssignedToRelation(Builder $query, array $terms): Builder
    {
        $relationName = $this->resolveAssignedToRelationName();

        if ($relationName === null) {
            return $query;
        }

        return $query->orWhereHasMorph(
            $relationName,
            [User::class, Asset::class, Location::class],
            function (Builder $morphQuery, string $morphType) use ($terms) {
                $columns = $this->getAssigneeColumnsByType($morphType);

                if (empty($columns)) {
                    return;
                }

                $table = (new $morphType)->getTable();
                $firstConditionAdded = false;

                foreach ($columns as $column) {
                    foreach ($terms as $term) {
                        if (! $firstConditionAdded) {
                            $morphQuery->where($table.'.'.$column, 'LIKE', '%'.$term.'%');
                            $firstConditionAdded = true;

                            continue;
                        }

                        $morphQuery->orWhere($table.'.'.$column, 'LIKE', '%'.$term.'%');
                    }
                }

                // Also search first+last concatenated for users.
                if ($morphType === User::class) {
                    foreach ($terms as $term) {
                        $morphQuery->orWhereRaw(
                            $this->buildMultipleColumnSearch(['users.first_name', 'users.last_name']),
                            ["%{$term}%"]
                        );
                    }
                }
            }
        );
    }

    /**
     * Run additional, advanced searches that can't be done using the
     * attributes or relations. No-op in this trait. Override in the
     * implementing model to add relation-aware search terms.
     *
     * $outer is the top-level query builder, used for leftJoin calls
     * (Eloquent nested-where closures cannot carry joins). $nested is
     * the where-clause builder confined inside scopeTextSearch's
     * nested group, used for orWhere calls that reference the joined
     * columns. Keeping the orWhere clauses on $nested closes the
     * multi-term oracle described on scopeTextSearch.
     *
     * @param  array<int, string>  $terms
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function advancedTextSearch(Builder $outer, Builder $nested, array $terms): void
    {
        // No-op in the base trait.
    }

    /**
     * Get the searchable attributes, if defined. Otherwise it returns an empty array
     *
     * @return array The attributes to search in
     */
    private function getSearchableAttributes()
    {
        return $this->searchableAttributes ?? [];
    }

    /**
     * Get the searchable relations, if defined. Otherwise it returns an empty array
     *
     * @return array The relations to search in
     */
    private function getSearchableRelations()
    {
        return $this->searchableRelations ?? [];
    }

    /**
     * Get searchable computed count aliases, if defined.
     */
    private function getSearchableCounts(): array
    {
        return $this->searchableCounts ?? [];
    }

    /**
     * Get virtual column aliases defined on the model.
     *
     * These are filter keys that map to a set of real columns searched via
     * CONCAT — for example, "name" → ['first_name', 'last_name'] on User,
     * because "name" is not a real database column on that table.
     *
     * @return array<string, list<string>>
     */
    private function getSearchableVirtualColumns(): array
    {
        return $this->searchableVirtualColumns ?? [];
    }

    /**
     * Get the relation aliases defined on the model.
     *
     * Maps the field names that the API / transformers expose to the actual
     * Eloquent relation names used in $searchableRelations.  For example:
     *
     *   protected $searchableRelationAliases = [
     *       'status_label' => 'status',
     *   ];
     *
     * Override this method in a model if you need dynamic alias resolution.
     *
     * @return array<string, string> [ api_key => relation_name ]
     */
    protected function getSearchableRelationAliases(): array
    {
        return $this->searchableRelationAliases ?? [];
    }

    /**
     * Columns that live on a related table but surface as a top-level
     * filter key on the current model's advanced-search UI. The hardware
     * list's "Title" column (= users.jobtitle via the assignedTo
     * polymorphic morph) is the first case: admins filter it like a
     * native attribute, but it isn't in $searchableAttributes and
     * doesn't match the $searchableRelations key dispatch. Define it
     * here and both the LIKE / exact filter path and the is:null /
     * is:not_null null-check path will route through whereHas /
     * whereHasMorph on the right table (#19708).
     *
     * Shape: `[filter_key => ['relation' => string, 'column' => string, 'types' => ?array<class-string>]]`.
     * `types` is only required for polymorphic morphs so the trait
     * knows which concrete morph classes host the column. For plain
     * belongsTo / hasOne relations, leave it unset.
     *
     * @return array<string, array{relation: string, column: string, types?: array<int, class-string>}>
     */
    protected function getSearchableRelationColumns(): array
    {
        return $this->searchableRelationColumns ?? [];
    }

    /**
     * @param  array{relation: string, column: string, types?: array<int, class-string>}  $mapping
     */
    private function applyRelationColumnFilter(Builder $query, array $mapping, string $value, string $boolean, string $operator): Builder
    {
        $isExact = in_array($operator, ['exact', 'exact_not'], true);
        $isNegate = in_array($operator, ['not_like', 'exact_not'], true);
        $comparison = match (true) {
            $operator === 'exact' => '=',
            $operator === 'exact_not' => '!=',
            $isNegate => 'NOT LIKE',
            default => 'LIKE',
        };
        $compareValue = $isExact ? $value : '%'.$value.'%';

        $apply = function (Builder $relationQuery) use ($mapping, $comparison, $compareValue): void {
            $relationQuery->where($mapping['column'], $comparison, $compareValue);
        };

        if (! empty($mapping['types'])) {
            // Polymorphic morph: constrain to the listed concrete
            // classes so the inner where runs against the right table.
            // Negated filters additionally include records whose morph
            // target is a different class (or no target at all), so
            // "jobtitle NOT LIKE X" doesn't silently drop every
            // asset assigned to a non-user.
            if ($isNegate) {
                $compoundMethod = $boolean === 'or' ? 'orWhere' : 'where';
                $query->{$compoundMethod}(function (Builder $compound) use ($mapping, $apply): void {
                    $compound->doesntHaveMorph($mapping['relation'], $mapping['types'])
                        ->orWhereHasMorph($mapping['relation'], $mapping['types'], $apply);
                });
            } else {
                $method = $boolean === 'or' ? 'orWhereHasMorph' : 'whereHasMorph';
                $query->{$method}($mapping['relation'], $mapping['types'], $apply);
            }

            return $query;
        }

        if ($isNegate) {
            $compoundMethod = $boolean === 'or' ? 'orWhere' : 'where';
            $query->{$compoundMethod}(function (Builder $compound) use ($mapping, $apply): void {
                $compound->doesntHave($mapping['relation'])
                    ->orWhereHas($mapping['relation'], $apply);
            });
        } else {
            $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';
            $query->{$method}($mapping['relation'], $apply);
        }

        return $query;
    }

    /**
     * @param  array{relation: string, column: string, types?: array<int, class-string>}  $mapping
     */
    private function applyRelationColumnNullFilter(Builder $query, array $mapping, bool $isNull, string $boolean): Builder
    {
        // is:not_null = "a related record exists whose column is
        //                 non-null and (for text columns) non-empty".
        // is:null     = exactly the complement: unassigned, assigned to
        //                 a different morph type, or related record
        //                 with a null / empty column. The UI column
        //                 renders those identically, so admins expect
        //                 them to filter identically too.
        //
        // Using whereDoesntHaveMorph with the "non-empty" constraint
        // expresses "null" without having to enumerate the three
        // sub-cases by hand, and the empty-string flip matches the
        // isTextTypeColumn fix on attribute columns (#19708).
        $visiblyPresent = function (Builder $relationQuery) use ($mapping): void {
            $table = $relationQuery->getModel()->getTable();
            $column = $mapping['column'];
            $qualified = $table.'.'.$column;

            $relationQuery->whereNotNull($qualified);
            if ($this->isTextTypeColumn($table, $column)) {
                $relationQuery->where($qualified, '!=', '');
            }
        };

        if (! empty($mapping['types'])) {
            if ($isNull) {
                // whereDoesntHaveMorph adds `assigned_type = <Type>`
                // to the outer WHERE, which excludes unassigned rows
                // (assigned_type IS NULL) and rows whose morph type
                // isn't in the list. Wrap it with an explicit
                // null-or-mismatched-type branch so the three ways
                // the UI column reads as "empty" all match.
                $table = $query->getModel()->getTable();
                $compoundMethod = $boolean === 'or' ? 'orWhere' : 'where';
                $query->{$compoundMethod}(function (Builder $compound) use ($table, $mapping, $visiblyPresent): void {
                    $compound->whereNull($table.'.assigned_type')
                        ->orWhereNotIn($table.'.assigned_type', $mapping['types'])
                        ->orWhereDoesntHaveMorph($mapping['relation'], $mapping['types'], $visiblyPresent);
                });
            } else {
                $method = $boolean === 'or' ? 'orWhereHasMorph' : 'whereHasMorph';
                $query->{$method}($mapping['relation'], $mapping['types'], $visiblyPresent);
            }

            return $query;
        }

        if ($isNull) {
            $method = $boolean === 'or' ? 'orWhereDoesntHave' : 'whereDoesntHave';
        } else {
            $method = $boolean === 'or' ? 'orWhereHas' : 'whereHas';
        }
        $query->{$method}($mapping['relation'], $visiblyPresent);

        return $query;
    }

    /**
     * Get structured-filter relation columns for a given filter key.
     *
     * By default, this uses all configured searchable relation columns for the
     * resolved relation key. Models can narrow specific advanced-search fields
     * via $searchableRelationFilterColumns, keyed by the incoming filter key
     * shown in the UI/API (for example: 'location' => ['name']).
     *
     * @param  array<string, array<int, string>>  $searchableRelations
     * @return array<int, string>
     */
    private function getStructuredFilterRelationColumns(string $filterKey, string $resolvedRelationKey, array $searchableRelations): array
    {
        $defaultColumns = (array) ($searchableRelations[$resolvedRelationKey] ?? []);

        $overrides = $this->searchableRelationFilterColumns ?? [];

        if (! array_key_exists($filterKey, $overrides)) {
            return $defaultColumns;
        }

        $overrideColumns = array_values(array_filter((array) $overrides[$filterKey], 'is_string'));

        // Keep only columns that are actually searchable on the resolved relation,
        // so model-level overrides cannot accidentally reference unknown columns.
        $validColumns = array_values(array_intersect($overrideColumns, $defaultColumns));

        return $validColumns !== [] ? $validColumns : $defaultColumns;
    }

    /**
     * Get the table name of a relation.
     *
     * This method loops over a relation name,
     * getting the table name of the last relation in the series.
     * So "category" would get the table name for the Category model,
     * "model.manufacturer" would get the tablename for the Manufacturer model.
     *
     * @param  string  $relation
     * @return string The table name
     */
    private function getRelationTable($relation)
    {
        $related = $this;

        foreach (explode('.', $relation) as $relationName) {
            $related = $related->{$relationName}()->getRelated();
        }

        /**
         * Are we referencing the model that called?
         * Then get the internal join-tablename, since laravel
         * has trouble selecting the correct one in this type of
         * parent-child self-join.
         *
         * @todo Does this work with deeply nested resources? Like "category.assets.model.category" or something like that?
         */
        if ($this instanceof $related) {

            /**
             * Since laravel increases the counter on the hash on retrieval, we have to count it down again.
             *
             * This causes side effects! Every time we access this method, laravel increases the counter!
             *
             * Format: laravel_reserved_XXX
             */
            $relationCountHash = $this->{$relationName}()->getRelationCountHash();

            $parts = collect(explode('_', $relationCountHash));

            $counter = $parts->pop();

            $parts->push($counter - 1);

            return implode('_', $parts->toArray());
        }

        return $related->getTable();
    }

    /**
     * Builds a search string for either MySQL or sqlite by separating the provided columns with a space.
     *
     * @param  array  $columns  Columns to include in search string.
     */
    private function buildMultipleColumnSearch(array $columns): string
    {
        // This method deliberately returns only an SQL fragment ending with "LIKE ?"
        // so callers can reuse it and swap operators (NOT LIKE / =) without duplicating
        // driver-specific CONCAT syntax.
        $mappedColumns = collect($columns)->map(fn ($column) => DB::getTablePrefix().$column)->toArray();

        $driver = config('database.connections.'.config('database.default').'.driver');

        if ($driver === 'sqlite') {
            return implode("||' '||", $mappedColumns).' LIKE ?';
        }

        // Default to MySQL's concatenation method
        return 'CONCAT('.implode('," ",', $mappedColumns).') LIKE ?';
    }

    /**
     * Search a string across multiple columns separated with a space.
     *
     * @param  Builder  $query
     * @param  array  $columns  - Columns to include in search string.
     * @return Builder
     */
    public function scopeOrWhereMultipleColumns($query, array $columns, $term)
    {
        return $query->orWhereRaw($this->buildMultipleColumnSearch($columns), ["%{$term}%"]);
    }

    /**
     * Resolve a filter key to the actual database column name for a custom field.
     *
     * Accepts only raw db_column slugs (e.g. "_snipeit_cpu_4") as filter keys.
     *
     * Returns null when the key cannot be matched to any known custom field.
     *
     * Only applicable to the Asset model.
     */
    private function resolveCustomFieldDbColumn(string $filterKey): ?string
    {
        if (! $this instanceof Asset) {
            return null;
        }

        $map = $this->buildCustomFieldFilterMap();

        // Exact match on db_column (e.g. "_snipeit_cpu_4") only.
        return $map[$filterKey] ?? null;
    }

    /**
     * Whether the given column is a text-shaped type (varchar, text, etc.).
     *
     * Used by applyNullFilter to decide whether the empty-string ('') check
     * makes sense alongside IS NULL / IS NOT NULL. On DATE / DATETIME / numeric
     * columns, comparing to '' in strict MySQL modes yields UNKNOWN and
     * silently excludes every row (the exact bug in GH #19708 for
     * asset_eol_date). Skipping the empty-string check for those columns
     * fixes it without changing behavior on text columns.
     *
     * Answers are cached per table.column for the request.
     */
    private function isTextTypeColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table.'.'.$column;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $type = strtolower(Schema::getColumnType($table, $column));
        } catch (\Throwable) {
            return $cache[$key] = true;
        }

        return $cache[$key] = str_contains($type, 'char') || str_contains($type, 'text');
    }

    /**
     * Build a lookup map for custom field filter resolution.
     *
     * The returned array contains db_column entries only:
     *   - db_column (exact) → db_column, e.g. "_snipeit_cpu_4" => "_snipeit_cpu_4"
     *
     * Results are cached statically for the duration of the request.
     * Call flushCustomFieldFilterMap() to reset the cache (useful in tests).
     *
     * @return array<string, string>
     */
    private function buildCustomFieldFilterMap(): array
    {
        if (isset(static::$customFieldFilterMapCache)) {
            return static::$customFieldFilterMapCache;
        }

        $map = [];

        try {
            CustomField::query()
                ->whereNotNull('db_column')
                ->where('field_encrypted', 0)
                ->get(['db_column'])
                ->each(function (CustomField $field) use (&$map): void {
                    $dbColumn = $field->db_column;

                    // Exact db_column key (e.g. "_snipeit_cpu_4")
                    $map[$dbColumn] = $dbColumn;
                });
        } catch (\Exception $e) {
            // Guard against missing table or schema issues during migrations / tests
        }

        static::$customFieldFilterMapCache = $map;

        return $map;
    }

    /**
     * Flush the custom field filter map cache.
     *
     * Useful in tests or after custom fields are added/modified.
     */
    public static function flushCustomFieldFilterMap(): void
    {
        static::$customFieldFilterMapCache = null;
    }
}
