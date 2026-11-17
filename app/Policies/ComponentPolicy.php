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

    /**
     * Gates the /api/v1/account/requestable/components endpoint. Reuses
     * the `assets.view.requestable` permission alongside AssetPolicy /
     * AccessoryPolicy / ConsumablePolicy so the four requestable
     * endpoints have one consistent gate. This is a bit of a hack,
     * but it works and avoids creating a new permission just for this one endpoint.
     */
    public function viewRequestable(User $user, $item = null)
    {
        return $user->hasAccess('assets.view.requestable');
    }
}
