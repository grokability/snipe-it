<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\User;

class UserObserver
{
    /**
     * Listen to the User updating event. This fires automatically every time an existing asset is saved.
     *
     * @return void
     */
    public function updating(User $user)
    {

        // ONLY allow these fields to be stored
        // NOTE: company_id is intentionally excluded — company membership changes are logged
        // via User::syncCompaniesWithLogging() against the pivot table instead.
        $allowed_fields = [
            'email',
            'activated',
            'first_name',
            'last_name',
            'website',
            'country',
            'gravatar',
            'location_id',
            'phone',
            'jobtitle',
            'manager_id',
            'employee_num',
            'username',
            'notes',
            'ldap_import',
            'locale',
            'two_factor_enrolled',
            'two_factor_optin',
            'department_id',
            'address',
            'address2',
            'city',
            'state',
            'zip',
            'remote',
            'start_date',
            'end_date',
            'autoassign_licenses',
            'vip',
            'password',
            'permissions',
        ];

        $changed = [];

        foreach ($user->getRawOriginal() as $key => $value) {

            // Make sure the info is in the allow fields array
            if (in_array($key, $allowed_fields)) {

                $oldValue = $user->getRawOriginal()[$key];
                $newValue = $user->getAttributes()[$key];

                if ($key === 'permissions') {
                    // Compare decoded to avoid spurious diffs from key reordering or type coercion.
                    $oldDecoded = json_decode($oldValue ?? '{}', true) ?: [];
                    $newDecoded = json_decode($newValue ?? '{}', true) ?: [];
                    if ($oldDecoded == $newDecoded) {
                        continue;
                    }
                    // Only log the permission keys that actually changed.
                    $diffOld = [];
                    $diffNew = [];
                    foreach (array_unique(array_merge(array_keys($oldDecoded), array_keys($newDecoded))) as $permKey) {
                        $oldPerm = $oldDecoded[$permKey] ?? null;
                        $newPerm = $newDecoded[$permKey] ?? null;
                        if ($oldPerm != $newPerm) {
                            $diffOld[$permKey] = $oldPerm;
                            $diffNew[$permKey] = $newPerm;
                        }
                    }
                    $changed['permissions']['old'] = json_encode($diffOld);
                    $changed['permissions']['new'] = json_encode($diffNew);

                    continue;
                }

                if ($oldValue == $newValue) {
                    continue;
                }

                $changed[$key]['old'] = $oldValue;
                $changed[$key]['new'] = $newValue;

                // Do not store the hashed password in changes
                if ($key == 'password') {
                    $changed['password']['old'] = '*************';
                    $changed['password']['new'] = '*************';
                }
            }

        }

        if (count($changed) > 0) {
            $logAction = new Actionlog;
            $logAction->item_type = User::class;
            $logAction->item_id = $user->id;
            $logAction->target_type = User::class;
            $logAction->target_id = $user->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->created_by = auth()->id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');

            // Let syncCompaniesWithLogging() merge company changes into this entry
            // rather than creating a separate log row for the same edit session.
            $user->currentUpdateLogId = $logAction->id;
        }

    }

    /**
     * Listen to the User created event, and increment
     * the next_auto_tag_base value in the settings table when i
     * a new asset is created.
     *
     * @return void
     */
    public function created(User $user)
    {
        $logAction = new Actionlog;
        $logAction->item_type = User::class; // can we instead say $logAction->item = $asset ?
        $logAction->item_id = $user->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('create');
    }

    /**
     * Listen to the User deleting event.
     *
     * @return void
     */
    public function deleting(User $user)
    {
        $logAction = new Actionlog;
        $logAction->item_type = User::class;
        $logAction->item_id = $user->id;
        $logAction->target_type = User::class; // can we instead say $logAction->item = $asset ?
        $logAction->target_id = $user->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('delete');
    }

    /**
     * Listen to the User deleting event.
     *
     * @return void
     */
    public function restoring(User $user)
    {
        $logAction = new Actionlog;
        $logAction->item_type = User::class;
        $logAction->item_id = $user->id;
        $logAction->target_type = User::class; // can we instead say $logAction->item = $asset ?
        $logAction->target_id = $user->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->logaction('restore');
    }
}
