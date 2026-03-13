<?php

namespace App\Policies;

class ManufacturerPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'manufacturers';
    }
}
