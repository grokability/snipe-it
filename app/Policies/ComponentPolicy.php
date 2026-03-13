<?php

namespace App\Policies;

class ComponentPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'components';
    }
}
