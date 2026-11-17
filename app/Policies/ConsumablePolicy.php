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

    /**
     * Gates the /api/v1/account/requestable/consumables endpoint. Reuses
     * the `assets.view.requestable` permission alongside AssetPolicy /
     * AccessoryPolicy / ComponentPolicy so the four requestable
     * endpoints have one consistent gate. This is a bit of a hack,
     * but it works and avoids creating a new permission just for this one endpoint.
     */
    public function viewRequestable(User $user, $item = null)
    {
        return $user->hasAccess('assets.view.requestable');
    }
}
