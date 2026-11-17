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
     *
     * @return mixed
     */
    public function viewKeys(User $user, ?License $license = null)
    {
        // An explicit "-1" on licenses.keys is an absolute deny that
        // beats the implicit grants from licenses.create and
        // licenses.edit below.
        if ($user->isPermissionExplicitlyDenied('licenses.keys')) {
            return false;
        }

        if ($user->hasAccess('licenses.keys') || $user->hasAccess('licenses.create') || $user->hasAccess('licenses.edit')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can access files associated with licenses.
     *
     * @return mixed
     */
    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }

    /**
     * Gates the /api/v1/account/requestable/licenses endpoint. Reuses
     * the `assets.view.requestable` permission alongside AssetPolicy /
     * AccessoryPolicy / ConsumablePolicy / ComponentPolicy so the five
     * requestable endpoints have one consistent gate. This is a bit of a hack,
     * but it works and avoids creating a new permission just for this one endpoint.
     */
    public function viewRequestable(User $user, $item = null)
    {
        return $user->hasAccess('assets.view.requestable');
    }
}
