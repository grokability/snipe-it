<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class UsersTransformer
{
    public function transformUsers(Collection $users, $total)
    {
        $array = [];
        foreach ($users as $user) {
            $array[] = self::transformUser($user);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformUser(User $user)
    {

        $role = null;
        if ($user->isSuperUser()) {
            $role = 'superadmin';
        } elseif ($user->isAdmin()) {
            $role = 'admin';
        }
        $array = [
                'id' => (int) $user->id,
                'avatar' => e($user->present()->gravatar) ?? null,
                'name' => e($user->getFullNameAttribute()) ?? null,
                'first_name' => e($user->first_name) ?? null,
                'last_name' => e($user->last_name) ?? null,
                'display_name' => ($user->getRawOriginal('display_name')) ? e($user->getRawOriginal('display_name')) : null,
                'username' => e($user->username) ?? null,
                'remote' => ($user->remote == '1') ? true : false,
                'locale' => ($user->locale) ? e($user->locale) : null,
                'employee_num' => ($user->employee_num) ? e($user->employee_num) : null,
                'manager' => ($user->manager) ? [
                    'id' => (int) $user->manager->id,
                    'name'=> e($user->manager->display_name),
                ] : null,
                'jobtitle' => ($user->jobtitle) ? e($user->jobtitle) : null,
                'vip' => ($user->vip == '1') ? true : false,
                'phone' => ($user->phone) ? e($user->phone) : null,
                'mobile' => ($user->mobile) ? e($user->mobile) : null,
                'website' => ($user->website) ? e($user->website) : null,
                'address' => ($user->address) ? e($user->address) : null,
                'city' => ($user->city) ? e($user->city) : null,
                'state' => ($user->state) ? e($user->state) : null,
                'country' => ($user->country) ? e($user->country) : null,
                'zip' => ($user->zip) ? e($user->zip) : null,
                'email' => ($user->email) ? e($user->email) : null,
                'department' => ($user->department) ? [
                    'id' => (int) $user->department->id,
                    'name'=> e($user->department->name),
                ] : null,
                'department_manager' => ($user->department?->manager) ? [
                    'id' => (int) $user->department->manager->id,
                    'name'=> e($user->department->manager->display_name),
                ] : null,
                'location' => ($user->userloc) ? [
                    'id' => (int) $user->userloc->id,
                    'name'=> e($user->userloc->name),
                ] : null,
                'notes'=> Helper::parseEscapedMarkedownInline($user->notes),
                'role' => $role,
                'permissions' => $user->decodePermissions(),
                'activated' => ($user->activated == '1') ? true : false,
                'autoassign_licenses' => ($user->autoassign_licenses == '1') ? true : false,
                'ldap_import' => ($user->ldap_import == '1') ? true : false,
                'two_factor_enrolled' => ($user->two_factor_active_and_enrolled()) ? true : false,
                'two_factor_optin' => ($user->two_factor_active()) ? true : false,
                'assets_count' => (int) $user->assets_count,
                'licenses_count' => (int) $user->licenses_count,
                'accessories_count' => (int) $user->accessories_count,
                'consumables_count' => (int) $user->consumables_count,
                'manages_users_count' => (int) $user->manages_users_count,
                'manages_locations_count' => (int) $user->manages_locations_count,
                'company' => ($user->company) ? ['id' => (int) $user->company->id, 'name'=> e($user->company->name)] : null,
                'created_by' => ($user->createdBy) ? [
                    'id' => (int) $user->createdBy->id,
                    'name'=> e($user->createdBy->display_name),
                ] : null,
                'created_at' => Helper::getFormattedDateObject($user->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($user->updated_at, 'datetime'),
                'start_date' => Helper::getFormattedDateObject($user->start_date, 'date'),
                'end_date' => Helper::getFormattedDateObject($user->end_date, 'date'),
                'last_login' => Helper::getFormattedDateObject($user->last_login, 'datetime'),
                'deleted_at' => ($user->deleted_at) ? Helper::getFormattedDateObject($user->deleted_at, 'datetime') : null,
            ];

        $permissions_array['available_actions'] = [
            'update' => (Gate::allows('update', User::class) && ($user->deleted_at == '')),
            'delete' => $user->isDeletable(),
            'clone' => (Gate::allows('create', User::class) && ($user->deleted_at == '')),
            'restore' => (Gate::allows('create', User::class) && ($user->deleted_at != '')),
        ];

        $array += $permissions_array;

        $numGroups = $user->groups->count();
        if ($numGroups > 0) {
            $groups['total'] = $numGroups;
            foreach ($user->groups as $group) {
                $groups['rows'][] = [
                    'id' => (int) $group->id,
                    'name' => e($group->name),
                ];
            }
            $array['groups'] = $groups;
        } else {
            $array['groups'] = null;
        }

        return $array;
    }

    /**
     * This gives a compact view of the user data without any additional relational queries,
     * allowing us to 1) deliver a smaller payload and 2) avoid additional queries on relations that
     * have not been easy/lazy loaded already
     *
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function transformUserCompact(User $user) : array
    {

        $array = [
            'id' => (int) $user->id,
            'image' => e($user->present()->gravatar) ?? null,
            'type' => 'user',
            'name' => e($user->getFullNameAttribute()),
            'first_name' => e($user->first_name),
            'last_name' => e($user->last_name),
            'username' => e($user->username),
            'display_name' => e($user->display_name),
            'created_by' => $user->adminuser ? [
                'id' => (int) $user->adminuser->id,
                'name'=> e($user->adminuser->present()->fullName),
            ]: null,
            'created_at' => Helper::getFormattedDateObject($user->created_at, 'datetime'),
            'deleted_at' => ($user->deleted_at) ? Helper::getFormattedDateObject($user->deleted_at, 'datetime') : null,
        ];

        return $array;
    }

    public function transformUsersDatatable($users)
    {
        return (new DatatablesTransformer)->transformDatatables($users);
    }
}
