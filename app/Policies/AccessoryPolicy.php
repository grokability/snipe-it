<?php

namespace App\Policies;

class AccessoryPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'accessories';
    }
}
