<?php

namespace App\Policies;

use App\Models\User;

class AssetModelPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'models';
    }

    public function files(User $user, $item = null)
    {
        // Set this to true so that users who can see the asset can also see the associated model files
        if ($user->hasAccess('assets.files')) {
            return true;
        }

        return $user->hasAccess($this->columnName().'.files');
    }
}
