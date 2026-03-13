<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Handle query scoping for full company support.
 *
 * @todo    Move this to a more Laravel 5.2 esque way
 * @version v1.0
 */
final class CompanyableChildScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $model = $builder->getModel();

        return Company::scopeCompanyableChildren($model->getCompanyableParents(), $builder);
    }

    /**
     * @todo IMPLEMENT
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder)
    {
    }
}
