<?php

namespace App\Models\Contracts;

/**
 * Implemented by models that belong to a Company through an indirect
 * parent relationship rather than carrying their own company_id column.
 * Maintenance (via its Asset) and LicenseSeat (via its License) use
 * this to tell the Loggable trait which company to stamp on action
 * logs when the row itself has no company column.
 */
interface CompanyableChild
{
    public function getCompanyableParents();
}
