<?php

namespace App\Policies;

use App\Models\SavedSearch;
use App\Models\User;

/**
 * Private-only visibility for v1: every record is scoped to its
 * creator.
 */
class SavedSearchPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavedSearch $savedSearch): bool
    {
        return $savedSearch->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SavedSearch $savedSearch): bool
    {
        return $savedSearch->created_by === $user->id;
    }

    public function delete(User $user, SavedSearch $savedSearch): bool
    {
        return $savedSearch->created_by === $user->id;
    }
}
