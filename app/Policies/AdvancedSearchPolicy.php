<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Asset;

class AdvancedSearchPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'advancedSearch';
    }
}
