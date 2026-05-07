<?php

namespace App\Models\Traits;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        $preparedSearch = $this->prepareSearchInput((string) $search);
        $terms = $preparedSearch['terms'];
        $filters = $preparedSearch['filters'];
        $filterOperator = $preparedSearch['filter_operator'];

        if (! empty($filters)) {
            // Structured advanced-search filters are mutually exclusive with free-text terms.
            // Once we detect structured payloads, we avoid the broad OR-based free-text path.
            return $this->applySearchFilters($query, $filters, $filterOperator);
        }

        /**
         * Search the attributes of this model
         */
        $query = $this->searchAttributes($query, $terms);

        /**
         * Search through the custom fields of the model
         */
        $query = $this->searchCustomFields($query, $terms);

        /**
         * Search through the relations of the model
         */
        $query = $this->searchRelations($query, $terms);

        /**
         * Search for additional attributes defined by the model
         */
        $query = $this->advancedTextSearch($query, $terms);

        return $query;
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

        if (in_array($filterKey, $searchableAttributes, true)) {
            if ($operator === 'exact') {
                $query->{$whereMethod}($table.'.'.$filterKey, '=', $value);
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

            if ($operator === 'exact') {
                // Exact match on the full CONCAT'd value, e.g. "John Smith" matches only
                // users whose first_name + ' ' + last_name equals exactly "John Smith".
                $concatSql = $this->buildMultipleColumnSearch($qualifiedColumns);
                // buildMultipleColumnSearch intentionally returns a fragment ending in "LIKE ?";
                // for exact matches we rewrite only the operator and keep the same SQL scaffold.
                $concatSql = str_replace(' LIKE ?', ' = ?', $concatSql);
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
            return $this->applyCountAliasFilter($query, $filterKey, $value, $boolean, $negate);
        }

        // Check if this is a custom field (only for Assets - for *now*).
        // Only db_column keys (e.g. "_snipeit_cpu_4") are accepted to avoid
        // collisions with standard attributes or relation filter keys.
        if ($this instanceof Asset) {
            $dbColumn = $this->resolveCustomFieldDbColumn($filterKey);

            if ($dbColumn !== null) {
                if ($operator === 'exact') {
                    $query->{$whereMethod}($table.'.'.$dbColumn, '=', $value);
                } else {
                    $query->{$whereMethod}($table.'.'.$dbColumn, $likeOperator, '%'.$value.'%');
                }

                return $query;
            }
        }

        $resolvedRelationKey = $this->resolveSearchableRelationKey($filterKey, $searchableRelations);

        if ($resolvedRelationKey === null) {
            return $query;
        }

        if ($this->isAssignedToRelationKey($resolvedRelationKey)) {
            return $this->applyAssignedToRelationFilter($query, $resolvedRelationKey, $value, $boolean, $negate);
        }

        $relationColumns = $this->getStructuredFilterRelationColumns(
            filterKey: $filterKey,
            resolvedRelationKey: $resolvedRelationKey,
            searchableRelations: $searchableRelations,
        );

        // For negated relation filters (e.g. location: !dam), include rows with
        // no related record as well as rows with related records that do not match.
        // This aligns advanced-search behavior with user expectation for "not X".
        if ($operator !== 'exact' && $likeOperator === 'NOT LIKE') {
            $compoundMethod = $boolean === 'or' ? 'orWhere' : 'where';

            $query->{$compoundMethod}(function (Builder $compoundQuery) use ($resolvedRelationKey, $relationColumns, $value): void {
                // Critical behavior: "not X" on relations should include records with no relation.
                // Example: location=!dam should include users without a location.
                $compoundQuery->doesntHave($resolvedRelationKey)
                    ->orWhereHas($resolvedRelationKey, function (Builder $relationQuery) use ($resolvedRelationKey, $relationColumns, $value): void {
                        $relationTable = $this->getRelationTable($resolvedRelationKey);
                        $firstConditionAdded = false;

                        foreach ($relationColumns as $relationColumn) {
                            if (! $firstConditionAdded) {
                                $relationQuery->where($relationTable.'.'.$relationColumn, 'NOT LIKE', '%'.$value.'%');
                                $firstConditionAdded = true;

                                continue;
                            }

                            // For negation we AND the NOT LIKE conditions so all columns must not match.
                            $relationQuery->where($relationTable.'.'.$relationColumn, 'NOT LIKE', '%'.$value.'%');
                        }

                        if (($resolvedRelationKey === 'adminuser') || ($resolvedRelationKey === 'user')) {
                            $concatSql = $this->buildMultipleColumnSearch([
                                'users.first_name',
                                'users.last_name',
                                'users.display_name',
                            ]);

                            $relationQuery->whereRaw(str_replace('LIKE', 'NOT LIKE', $concatSql), ["%{$value}%"]);
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
    private function applyAssignedToRelationFilter(Builder $query, string $relationKey, string $filterValue, string $boolean = 'and', bool $negate = false): Builder
    {
        $relationName = $this->resolveAssignedToRelationName();

        if ($relationName === null) {
            return $query;
        }

        $likeOperator = $negate ? 'NOT LIKE' : 'LIKE';
        $relationMethod = $boolean === 'or' ? 'orWhereHasMorph' : 'whereHasMorph';

        return $query->{$relationMethod}(
            $relationName,
            [User::class, Asset::class, Location::class],
            function (Builder $assigneeQuery, string $assigneeType) use ($filterValue, $likeOperator, $negate) {
                $columns = $this->getAssigneeColumnsByType($assigneeType);

                if (empty($columns)) {
                    return;
                }

                $table = (new $assigneeType)->getTable();
                $firstConditionAdded = false;

                foreach ($columns as $column) {
                    if (! $firstConditionAdded) {
                        $assigneeQuery->where($table.'.'.$column, $likeOperator, '%'.$filterValue.'%');
                        $firstConditionAdded = true;

                        continue;
                    }

                    // For negation, AND the conditions (all columns must not match).
                    // For normal LIKE, OR them (any column matching is sufficient).
                    $negate
                        ? $assigneeQuery->where($table.'.'.$column, $likeOperator, '%'.$filterValue.'%')
                        : $assigneeQuery->orWhere($table.'.'.$column, $likeOperator, '%'.$filterValue.'%');
                }

                if ($assigneeType === User::class) {
                    $concatSql = $this->buildMultipleColumnSearch(['users.first_name', 'users.last_name']);

                    $negate
                        ? $assigneeQuery->whereRaw(str_replace('LIKE', 'NOT LIKE', $concatSql), ["%{$filterValue}%"])
                        : $assigneeQuery->orWhereRaw($concatSql, ["%{$filterValue}%"]);
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
    private function applyCountAliasFilter(Builder $query, string $countAlias, string $filterValue, string $boolean = 'and', bool $negate = false): Builder
    {
        $havingMethod = $boolean === 'or' ? 'orHaving' : 'having';

        if (is_numeric($filterValue)) {
            $operator = $negate ? '!=' : '=';

            return $query->{$havingMethod}($countAlias, $operator, (int) $filterValue);
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
                $method = match (true) {
                    $isNull && $boolean === 'or' => 'orWhereNull',
                    $isNull => 'whereNull',
                    $boolean === 'or' => 'orWhereNotNull',
                    default => 'whereNotNull',
                };

                $query->{$method}($table.'.'.$dbColumn);

                return $query;
            }
        }

        // Direct attribute column.
        if (in_array($filterKey, $searchableAttributes, true)) {
            $method = match (true) {
                $isNull && $boolean === 'or' => 'orWhereNull',
                $isNull => 'whereNull',
                $boolean === 'or' => 'orWhereNotNull',
                default => 'whereNotNull',
            };

            $query->{$method}($table.'.'.$filterKey);

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

        // Relation key: no related record = "null", has a related record = "not null".
        $searchableRelations = $this->getSearchableRelations();
        $resolvedRelationKey = $this->resolveSearchableRelationKey($filterKey, $searchableRelations);

        if ($resolvedRelationKey !== null && ! $this->isAssignedToRelationKey($resolvedRelationKey)) {
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
     * Run additional, advanced searches that can't be done using the attributes or relations.
     *
     * This is a noop in this trait, but can be overridden in the implementing model, to allow more advanced searches
     *
     * @param  $query  Builder
     * @param  $terms  array
     * @return Builder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function advancedTextSearch(Builder $query, array $terms)
    {
        return $query;
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
