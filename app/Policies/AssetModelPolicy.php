<?php

namespace App\Policies;

class AssetModelPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'models';
    }
}
