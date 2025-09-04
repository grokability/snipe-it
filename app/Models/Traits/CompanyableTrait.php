<?php

namespace App\Models\Traits;

use App\Models\CompanyableScope;
use App\Models\Setting;

trait CompanyableTrait
{
    /**
     * This trait is used to scope models to the current company. To use this scope on companyable models,
     * we use the "use Companyable;" statement at the top of the mode.
     *
     * @see    \App\Models\Company\Company::scopeCompanyables()
     * @return void
     */
    public static function bootCompanyableTrait()
    {
        // In Version 7.0 and before locations weren't scoped by companies, so add a check for the backward compatibility setting
        if (__CLASS__ != 'App\Models\Location') {
            static::addGlobalScope(new CompanyableScope);
        } else {
            if (Setting::getSettings()->scope_locations_fmcs == 1) {
                static::addGlobalScope(new CompanyableScope);
            }
        }
    }
}
