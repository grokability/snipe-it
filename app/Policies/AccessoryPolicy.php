<?php

namespace App\Policies;

use App\Models\User;

class AccessoryPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'accessories';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
