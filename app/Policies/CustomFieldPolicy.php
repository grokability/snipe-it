<?php

namespace App\Policies;

class CustomFieldPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'customfields';
    }
}
