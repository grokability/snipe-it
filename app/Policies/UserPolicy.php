<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'users';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
