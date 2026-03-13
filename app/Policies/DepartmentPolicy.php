<?php

namespace App\Policies;

class DepartmentPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'departments';
    }
}
