<?php

namespace App\Policies;

class DepreciationPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'depreciations';
    }
}
