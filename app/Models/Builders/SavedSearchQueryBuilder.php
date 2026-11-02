<?php

namespace App\Models\Builders;

use App\Models\SavedSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @template TModel of SavedSearch
 *
 * @extends Builder<TModel>
 */
class SavedSearchQueryBuilder extends Builder
{
    /**
     * Records owned by the current authenticated user. Called from
     * controllers and policies to keep the private-only semantics in
     * one place.
     */
    public function ownedByCurrent(): self
    {
        return $this->where('created_by', Auth::id());
    }

    /**
     * Records belonging to the given bs-table idTable. Combined with
     * ownedByCurrent() to list a user's presets for one listing page.
     */
    public function forTable(string $tableName): self
    {
        return $this->where('table_name', $tableName);
    }
}
