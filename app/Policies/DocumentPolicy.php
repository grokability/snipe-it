<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'documents';
    }

    /**
     * Viewers plus the assignee themselves (spec §22 matrix).
     */
    public function view(User $user, $item = null)
    {
        if ($item instanceof Document && (int) $item->assigned_to_id === (int) $user->id) {
            return true;
        }

        return $user->hasAccess('documents.view');
    }

    /**
     * Signers: the assignee for their document, or holders of documents.sign.
     */
    public function sign(User $user, $item = null)
    {
        if ($item instanceof Document && (int) $item->assigned_to_id === (int) $user->id) {
            return true;
        }

        return $user->hasAccess('documents.sign');
    }

    public function signRole(User $user, Document $document, string $role, string $method = 'digital'): bool
    {
        if ($role === 'employee') {
            return (int) $document->assigned_to_id === (int) $user->id
                || ($method === 'printed' && $user->hasAccess('documents.sign'));
        }

        return $user->hasAccess('documents.sign');
    }

    public function cancel(User $user, $item = null)
    {
        return $user->hasAccess('documents.cancel');
    }

    public function download(User $user, $item = null)
    {
        if ($item instanceof Document && (int) $item->assigned_to_id === (int) $user->id) {
            return true;
        }

        return $user->hasAccess('documents.download');
    }
}
