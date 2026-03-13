<?php

namespace App\Policies;

class PredefinedKitPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'kits';
    }
}
