<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

/**
 * Policy for Asset Maintenances.
 *
 * A user may view or create maintenances on an asset if they have permission
 * to edit that asset. All other standard CRUD operations fall back to the
 * assets.edit permission, consistent with the rest of the application.
 */
final class MaintenancePolicy
{
    use HandlesAuthorization;

    /**
     * Superusers and admins are handled globally in AuthServiceProvider::boot().
     * Company-scoping is enforced at the model level via CompanyableChildTrait.
     */

    /**
     * Determine whether the user can list maintenances.
     * Requires asset edit permission (no specific asset to check against).
     */
    public function index(User $user): bool
    {
        return $user->hasAccess('assets.view');
    }

    /**
     * Determine whether the user can view a specific maintenance record.
     * Allowed if the user can edit the associated asset.
     */
    public function view(User $user, Maintenance $maintenance): bool
    {
        return Gate::allows('update', $maintenance->asset);
    }

    /**
     * Determine whether the user can create a maintenance record.
     * When checking against the class (no instance), fall back to assets.edit.
     * When an asset instance is provided via context, check update on that asset.
     */
    public function create(User $user, ?Asset $asset = null): bool
    {
        if ($asset instanceof Asset) {
            return Gate::allows('update', $asset);
        }

        return $user->hasAccess('assets.edit');
    }

    /**
     * Determine whether the user can update a maintenance record.
     * Allowed if the user can edit the associated asset.
     */
    public function update(User $user, Maintenance $maintenance): bool
    {
        return Gate::allows('update', $maintenance->asset);
    }

    /**
     * Determine whether the user can delete a maintenance record.
     * Allowed if the user can edit the associated asset and the record is not soft-deleted.
     */
    public function delete(User $user, Maintenance $maintenance): bool
    {
        return empty($maintenance->deleted_at)
            && Gate::allows('update', $maintenance->asset);
    }

    /**
     * Determine whether the user can upload or manage files attached to a maintenance record.
     * Allowed if the user can edit the associated asset.
     */
    public function files(User $user, Maintenance $maintenance): bool
    {
        return Gate::allows('update', $maintenance->asset);
    }

    /**
     * Determine whether the user can view history for a maintenance record.
     * Allowed when the user can view the maintenance itself, or has global activity view permission.
     */
    public function history(User $user, Maintenance $maintenance): bool
    {
        return Gate::allows('view', $maintenance->asset)
            || Gate::allows('view', $maintenance)
            || $user->hasAccess('activity.view');
    }
}
