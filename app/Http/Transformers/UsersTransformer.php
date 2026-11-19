<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Consumable;
use App\Models\License;
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
        // Info-disclosure guard, matches the pattern on
        // transformUserCompact. This full transformer gets called by
        // nested endpoints (ConsumablesTransformer::transformCheckedoutConsumables,
        // ActionlogsTransformer, LocationsTransformer manager block) where the
        // wrapping endpoint only checked the wrapping resource's `.view`
        // permission. Without this guard, a caller with consumables.view
        // but not users.view reads the full user record (email, phone,
        // address, permissions, employee_num, department, etc.) via
        // GET /api/v1/consumables/{id}/users.
        //
        // Self-view exception: /api/v1/users/me returns $request->user()
        // through this transformer with no controller-level authorize()
        // check, and every authenticated user (regardless of users.view)
        // needs to fetch their own profile. Bypass the gate when the
        // caller is viewing themselves.
        //
        // Denied fallback includes display_name because a caller with
        // view permission on the wrapping thing (an accessory, a license
        // seat, an asset) legitimately needs to see WHO has it. What
        // gets stripped is PII (email, phone, address, department,
        // companies, permissions, employee_num, jobtitle, etc.).
        if (auth()->id() !== $user->id && Gate::denies('view', $user)) {
            return [
                'id' => (int) $user->id,
                'type' => 'user',
                'name' => e($user->display_name),
            ];
        }

        $role = null;
        if ($user->isSuperUser()) {
            $role = 'superadmin';
        } elseif ($user->isAdmin()) {
            $role = 'admin';
        }
        $array = [
            'id' => (int) $user->id,
            'avatar' => e($user->present()->gravatar) ?? null,
            'qr_code_url' => route('qr_code/common', ['object_type' => 'users', 'id' => $user->id]),
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
                'name' => e($user->manager->display_name),
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
                'name' => e($user->department->name),
                'tag_color' => ($user->department->tag_color) ? e($user->department->tag_color) : null,
            ] : null,
            'department_manager' => ($user->department?->manager) ? [
                'id' => (int) $user->department->manager->id,
                'name' => e($user->department->manager->display_name),
            ] : null,
            'location' => ($user->userloc) ? [
                'id' => (int) $user->userloc->id,
                'name' => e($user->userloc->name),
                'tag_color' => ($user->userloc->tag_color) ? e($user->userloc->tag_color) : null,
            ] : null,
            'notes' => Helper::parseEscapedMarkedownInline($user->notes),
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
            'assigned_maintenances_count' => (int) $user->assigned_maintenances_count,
            // Legacy field — kept for backward API compatibility; use `companies` for multi-company support.
            'company' => $user->companies->isNotEmpty() ? [
                'id' => (int) $user->companies->first()->id,
                'name' => e($user->companies->first()->name),
                'tag_color' => ($user->companies->first()->tag_color) ? e($user->companies->first()->tag_color) : null,
            ] : null,
            'companies' => $user->companies->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => e($c->name),
                'tag_color' => $c->tag_color ? e($c->tag_color) : null,
            ])->values(),
            'created_by' => ($user->createdBy) ? [
                'id' => (int) $user->createdBy->id,
                'name' => e($user->createdBy->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($user->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($user->updated_at, 'datetime'),
            'start_date' => Helper::getFormattedDateObject($user->start_date, 'date'),
            'end_date' => Helper::getFormattedDateObject($user->end_date, 'date'),
            'last_login' => Helper::getFormattedDateObject($user->last_login, 'datetime'),
            'deleted_at' => ($user->deleted_at) ? Helper::getFormattedDateObject($user->deleted_at, 'datetime') : null,
        ];

        $permissions_array['available_actions'] = [
            'update' => (Gate::allows('update', $user) && ($user->deleted_at == '')),
            'delete' => ($user->isDeletable() && (auth()->user()->can('canEditAuthFields', $user) && auth()->user()->can('editableOnDemo'))),
            'clone' => (Gate::allows('clone', $user) && ($user->deleted_at == '')),
            'restore' => (Gate::allows('create', User::class) && ($user->deleted_at != '')),
            'bulk_selectable' => [
                'edit' => (Gate::allows('update', $user) && $user->deleted_at == ''),
                'send_assigned' => (Gate::allows('update', $user) && $user->deleted_at == '' && ! empty($user->email)),
                'delete' => (Gate::allows('delete', $user) && $user->deleted_at == ''),
                'merge' => (Gate::allows('delete', $user) && $user->deleted_at == ''),
                'bulkpasswordreset' => ($user->deleted_at == '' && $user->activated == '1' && ! empty($user->email) && $user->ldap_import != '1'),
                'print' => $user->deleted_at == '',
            ],
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
     * @throws \Exception
     */
    public function transformUserCompact(User $user): array
    {
        // Info-disclosure guard. This method is embedded in the nested
        // assignee payload of accessory / license / component / asset
        // checkout listings, where the wrapping endpoint only checked the
        // wrapping resource's `.view` permission. Without this check, a
        // caller with (for example) accessories.view but not users.view
        // reads the full identity block via
        // GET /api/v1/accessories/{id}/checkedout.
        //
        // Denied fallback keeps id / type / name (display_name) because
        // view permission on the wrapping thing implies you can see WHO
        // it's checked out to. What gets stripped is PII (first_name,
        // last_name, username, email-adjacent, companies, etc.).
        //
        // Gate check runs against the target instance, not the class, so
        // FMCS company-scoping applies too - a caller with users.view can
        // still be denied identity of a user in a company they can't see.
        if (Gate::denies('view', $user)) {
            return [
                'id' => (int) $user->id,
                'type' => 'user',
                'name' => e($user->display_name),
            ];
        }

        $array = [
            'id' => (int) $user->id,
            'image' => e($user->present()->gravatar) ?? null,
            'type' => 'user',
            'name' => e($user->display_name),
            'first_name' => e($user->first_name),
            'last_name' => e($user->last_name),
            'username' => e($user->username),
            'display_name' => e($user->display_name),
            'companies' => $user->companies->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => e($c->name),
                'tag_color' => $c->tag_color ? e($c->tag_color) : null,
            ])->values(),
            'created_by' => $user->adminuser ? [
                'id' => (int) $user->adminuser->id,
                'name' => e($user->adminuser->present()->fullName),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($user->created_at, 'datetime'),
            'deleted_at' => ($user->deleted_at) ? Helper::getFormattedDateObject($user->deleted_at, 'datetime') : null,
        ];

        return $array;
    }

    public function transformUsersDatatable($users)
    {
        return (new DatatablesTransformer)->transformDatatables($users);
    }

    /**
     * @param  Collection<int, Consumable>  $consumables
     * @param  array<int, string|null>  $unitCostsById
     * @return array<string, mixed>
     */
    public function transformUserConsumables(Collection $consumables, array $unitCostsById, int $total): array
    {
        $rows = [];
        foreach ($consumables as $consumable) {
            $rows[] = $this->transformUserConsumableRow($consumable, $unitCostsById);
        }

        return (new DatatablesTransformer)->transformDatatables($rows, $total);
    }

    /**
     * @param  array<int, string|null>  $unitCostsById
     * @return array<string, mixed>
     */
    private function transformUserConsumableRow(Consumable $consumable, array $unitCostsById): array
    {
        $unitCost = $unitCostsById[$consumable->id] ?? null;
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $consumable->pivot;

        return [
            'id' => (int) $pivot->id,
            'consumable' => [
                'id' => (int) $consumable->id,
                'name' => e($consumable->name),
            ],
            'name' => e($consumable->name),
            'image' => $consumable->getImageUrl() ?: null,
            'qty' => 1,
            'purchase_cost' => Helper::formatCurrencyOutput($unitCost),
            'created_at' => Helper::getFormattedDateObject($pivot->created_at, 'datetime'),
            'note' => $pivot->note ? e($pivot->note) : null,
        ];
    }

    /**
     * @param  Collection<int, Accessory>  $accessories
     * @param  array<int, string|null>  $unitCostsById
     * @return array<string, mixed>
     */
    public function transformUserAccessories(Collection $accessories, array $unitCostsById, int $total): array
    {
        $rows = [];
        foreach ($accessories as $accessory) {
            $rows[] = $this->transformUserAccessoryRow($accessory, $unitCostsById);
        }

        return (new DatatablesTransformer)->transformDatatables($rows, $total);
    }

    /**
     * @param  array<int, string|null>  $unitCostsById
     * @return array<string, mixed>
     */
    private function transformUserAccessoryRow(Accessory $accessory, array $unitCostsById): array
    {
        $unitCost = $unitCostsById[$accessory->id] ?? null;
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $accessory->pivot;

        return [
            // row.id is the pivot id, which
            // genericCheckinCheckoutFormatter uses to build the
            // checkin URL when assigned_to is populated.
            'id' => (int) $pivot->id,
            'accessory' => [
                'id' => (int) $accessory->id,
                'name' => e($accessory->name),
            ],
            'name' => e($accessory->name),
            'image' => $accessory->getImageUrl() ?: null,
            'purchase_cost' => Helper::formatCurrencyOutput($unitCost),
            'created_at' => Helper::getFormattedDateObject($pivot->created_at, 'datetime'),
            'note' => $pivot->note ? e($pivot->note) : null,
            // Truthy assigned_to flags this row as "checked out" so
            // genericCheckinCheckoutFormatter's checkin branch fires.
            'assigned_to' => (int) ($pivot->assigned_to ?? 0),
            'checkin_backto' => 'user',
            'available_actions' => [
                'checkin' => Gate::allows('checkin', $accessory),
            ],
        ];
    }

    /**
     * @param  Collection<int, License>  $licenses
     * @return array<string, mixed>
     */
    public function transformUserLicenses(Collection $licenses, int $total): array
    {
        $rows = [];
        foreach ($licenses as $license) {
            $rows[] = $this->transformUserLicenseRow($license);
        }

        return (new DatatablesTransformer)->transformDatatables($rows, $total);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformUserLicenseRow(License $license): array
    {
        /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
        $pivot = $license->pivot;
        $canViewKeys = Gate::allows('viewKeys', $license);

        return [
            // row.id is the LicenseSeat id, which
            // licenseSeatInOutFormatter uses to build the checkin
            // URL. Note that row.license.id is the parent License
            // id, used for checkout URLs by the same formatter.
            'id' => (int) $pivot->id,
            'license' => [
                'id' => (int) $license->id,
                'name' => e($license->name),
            ],
            'license_id' => (int) $license->id,
            'name' => e($license->name),
            'serial' => $canViewKeys ? e($license->serial) : null,
            'category' => $license->category ? [
                'id' => $license->category->id,
                'name' => e($license->category->name),
            ] : null,
            'purchase_cost' => Helper::formatCurrencyOutput($license->purchase_cost),
            'purchase_order' => e($license->purchase_order),
            'order_number' => e($license->order_number),
            // Truthy assigned_user tells licenseSeatInOutFormatter
            // this seat is checked out to a user, so the checkin
            // branch fires. asset_id / assigned_asset stay unset
            // because a seat cannot be assigned to both at once.
            'assigned_user' => (int) ($pivot->assigned_to ?? 0) ?: null,
            'checkin_backto' => 'user',
            'available_actions' => [
                'checkin' => Gate::allows('update', $license),
                'bulk_selectable' => [
                    'checkin' => Gate::allows('checkin', $license),
                ],
            ],
        ];
    }
}
