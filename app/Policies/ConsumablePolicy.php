<?php

namespace App\Policies;

use App\Models\User;

class ConsumablePolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'consumables';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
