<?php

namespace App\Models\Builders;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of CustomField
 *
 * @extends Builder<TModel>
 */
class CustomFieldQueryBuilder extends Builder
{
    /** Fields in fieldsets assigned to at least one non-deleted asset model. */
    public function assignedToInUseFieldsets(): self
    {
        return $this->has('fieldset.models');
    }
}
