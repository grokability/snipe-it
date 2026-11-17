<?php

namespace App\Policies;

use App\Models\User;

class AssetModelPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'models';
    }

    /**
     * READ ability for model files (index / show / download). Cascades from
     * asset visibility: model file attachments (user manuals, spec sheets,
     * etc.) apply to every asset of a given model, so anyone who can view
     * assets can see them. Managing (upload/delete) still requires the
     * dedicated `models.files` grant via manageFiles() below.
     */
    public function files(User $user, $item = null)
    {
        if ($user->hasAccess('assets.view')) {
            return true;
        }

        return $user->hasAccess($this->columnName().'.files');
    }

    /**
     * WRITE ability for model files (upload / delete). Strict: requires the
     * dedicated `models.files` grant. The read cascade above must NOT
     * short-circuit here or an admin who withheld `models.files` while
     * granting `assets.files` to routine technicians would still see them
     * mutating the shared model file catalog.
     */
    public function manageFiles(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }

    /**
     * Gates /api/v1/account/requestable/models. Reuses the existing
     * `assets.view.requestable` permission so the five requestable
     * endpoints share one consistent gate. This is a bit of a hack,
     * but it works and avoids creating a new permission just for this one endpoint.
     */
    public function viewRequestable(User $user, $item = null)
    {
        return $user->hasAccess('assets.view.requestable');
    }
}
