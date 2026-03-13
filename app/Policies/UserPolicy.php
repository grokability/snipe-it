<?php

namespace App\Policies;

class UserPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'users';
    }
}
