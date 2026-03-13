<?php

namespace App\Policies;

class CategoryPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'categories';
    }
}
