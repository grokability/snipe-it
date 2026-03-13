<?php

namespace App\Policies;

class LocationPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'locations';
    }
}
