<?php

namespace App\Policies;

use App\Models\User;

class ComponentPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'components';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
