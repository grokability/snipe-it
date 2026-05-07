<?php

namespace App\Policies;

use App\Models\User;

class SupplierPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'suppliers';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
