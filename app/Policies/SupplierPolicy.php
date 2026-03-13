<?php

namespace App\Policies;

class SupplierPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'suppliers';
    }
}
