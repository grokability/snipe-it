<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PredefinedFilter;
use App\Policies\SnipePermissionsPolicy; 

class PredefinedFilterPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'predefinedFilter';
    }

    
    public function view(User $user, $filter = null)
    {
        // Global permission
        if (parent::view($user, $filter)) {
            return true;
        }

        //temp
        if (is_string($filter) && $filter === PredefinedFilter::class) {
        return true; 
    }

        // Record-level permissions
        if ($filter instanceof PredefinedFilter) {
            return $filter->created_by === $user->id || $filter->userHasPermission($user, 'view');
        }

        return false;
    }

    public function update(User $user, $filter = null)
    {
        if (parent::update($user, $filter)) {
            return true;
        }

        if ($filter instanceof PredefinedFilter) {
            return $filter->created_by === $user->id || $filter->userHasPermission($user, 'edit');
        }

        return false;
    }

    public function delete(User $user, $filter = null)
    {
        if (parent::delete($user, $filter)) {
            return true;
        }

        if ($filter instanceof PredefinedFilter) {
            return $filter->created_by === $user->id || $filter->userHasPermission($user, 'delete');
        }

        return false;
    }

    public function create(User $user)
    {
        // Allow via global create permission
        return parent::create($user) || true; 
    }
}
