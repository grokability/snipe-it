<?php

namespace App\Policies;

use App\Models\User;

abstract class CheckoutablePermissionsPolicy extends SnipePermissionsPolicy
{
    /**
     * Determine whether the user can checkout the accessory.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function checkout(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.checkout');
    }

    /**
     * Determine whether the user can checkin the accessory.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function checkin(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.checkin');
    }

    /**
     * Determine whether the user can manage the accessory.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function manage(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.checkin')
             || $user->hasAccess($this->columnName().'.edit')
             || $user->hasAccess($this->columnName().'.checkout');
    }
}
