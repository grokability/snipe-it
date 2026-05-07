<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'assets';
    }

    public function viewRequestable(User $user, ?Asset $asset = null)
    {
        return $user->hasAccess('assets.view.requestable');
    }

    public function audit(User $user, ?Asset $asset = null)
    {
        return $user->hasAccess('assets.audit');
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
