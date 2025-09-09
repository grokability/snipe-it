<?php

namespace App\Models\Traits;

use App\Models\CompanyableChildScope;

trait CompanyableChildTrait
{
    /**
     * Boot the companyable trait for a model.
     *
     * @return void
     */
    public static function bootCompanyableChildTrait()
    {
        static::addGlobalScope(new CompanyableChildScope);
    }
}
