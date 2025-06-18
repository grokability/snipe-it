<?php

namespace App\Policies;

use App\Models\License;
use App\Models\User;

class LicensePolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'licenses';
    }

    /**
     * Determine whether the user can view license keys.
     * This gets a little tricky, UX/logic-wise. If a user has the ability
     * to create a license (which requires a product key), shouldn't they
     * have the ability to see the product key as well?
     *
     * Example: I create the license, realize I need to change
     * something (maybe I got the product key wrong), and now I can never
     * see/edit that product key.
     *
     * @see https://github.com/grokability/snipe-it/issues/6956
     * @param  \App\Models\User  $user
     * @param  \App\Models\License  $license
     * @return mixed
     */
    public function viewKeys(User $user, License $license = null)
    {
        if ($user->hasAccess('licenses.keys') || $user->hasAccess('licenses.create') || $user->hasAccess('licenses.edit')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can access files associated with licenses.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function files(User $user, $license = null)
    {
        if ($user->hasAccess('licenses.files'))  {
            return true;
        }
        return false;

    }
}
