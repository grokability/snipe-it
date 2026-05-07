<?php

namespace App\Policies;

use App\Models\User;

class LocationPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'locations';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
