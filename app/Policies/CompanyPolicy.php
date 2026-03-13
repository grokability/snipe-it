<?php

namespace App\Policies;

class CompanyPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'companies';
    }

}
