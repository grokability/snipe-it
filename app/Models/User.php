<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Builders\UserQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasCalendarEvents;
use App\Models\Traits\HasImageUpload;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use App\Presenters\UserPresenter;
use App\Rules\CssColor;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperUser
 */
class User extends SnipeModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, HasLocalePreference, Importable
{
    use CompanyableTrait;
    use HasCalendarEvents;
    use HasFactory;
    use HasImageUpload;
    use HasUploads;

    public static function fileStorage(): FileStorage
    {
        return FileStorage::Avatars;
    }

    protected $presenter = UserPresenter::class;

    use Authenticatable, Authorizable, CanResetPassword, HasApiTokens;
    use Loggable, SoftDeletes, ValidatingTrait;
    use Notifiable;
    use Presentable;
    use Searchable;
    use UniqueUndeletedTrait;

    /**
     * Fields governed by the `canEditAuthFields` gate: credentials (username,
     * email, password), the activation flag, and the permission blob. Any
     * controller / importer / job that mass-assigns from user input must gate
     * writes to these fields on `canEditAuthFields` against the target, and
     * signal denial rather than silently dropping them. Add to this list to
     * bring a new field under the same gate everywhere at once.
     */
    public const GATED_AUTH_FIELDS = [
        'password',
        'username',
        'email',
        'activated',
        'permissions',
        'two_factor_optin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
        'reset_password_code',
        'persist_code',
        'two_factor_secret',
        'activation_code',
    ];

    protected $table = 'users';

    protected $injectUniqueIdentifier = true;

    /**
     * Transient (non-persisted) ID of the Actionlog entry written by UserObserver::updating()
     * during the current request. syncCompaniesWithLogging() merges company changes into this
     * entry instead of creating a separate one, so a single edit session produces one log row.
     */
    public ?int $currentUpdateLogId = null;

    protected $fillable = [
        'activated',
        'address',
        'city',
        'country',
        'department_id',
        'email',
        'employee_num',
        'first_name',
        'jobtitle',
        'last_name',
        'display_name',
        'ldap_import',
        'locale',
        'location_id',
        'manager_id',
        'password',
        'phone',
        'mobile',
        'notes',
        'state',
        'username',
        'zip',
        'remote',
        'start_date',
        'end_date',
        'scim_externalid',
        'avatar',
        'gravatar',
        'vip',
        'autoassign_licenses',
        'website',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'location_id' => 'integer',
        'legacy_company_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Model validation rules
     *
     * @var array
     */
    protected $rules = [
        'first_name' => 'required|string|max:191',
        'last_name' => 'nullable|string|max:191',
        'display_name' => 'nullable|string|max:191',
        'username' => 'required|string|min:1|unique_undeleted|max:191',
        'email' => 'email|nullable|max:191',
        'password' => 'required|min:8',
        'locale' => 'max:10|nullable',
        'website' => 'url|nullable|max:191',
        'manager_id' => 'nullable|exists:users,id|cant_manage_self|non_circular:users,id',
        'location_id' => 'exists:locations,id|nullable',
        'start_date' => 'nullable|date_format:Y-m-d',
        'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        'autoassign_licenses' => 'boolean',
        'address' => 'nullable|string|max:191',
        'city' => 'nullable|string|max:191',
        'state' => 'nullable|string|max:191',
        'country' => 'min:2|max:191|nullable',
        'zip' => 'max:10|nullable',
        'vip' => 'boolean',
        'remote' => 'boolean',
        'activated' => 'boolean',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'address',
        'city',
        'country',
        'display_name',
        'email',
        'employee_num',
        'first_name',
        'jobtitle',
        'last_name',
        'locale',
        'mobile',
        'notes',
        'phone',
        'state',
        'username',
        'website',
        'zip',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'userloc' => ['name', 'address', 'address2', 'city', 'state', 'zip'],
        'department' => ['name'],
        'groups' => ['name'],
        'companies' => ['name'],
        'manager' => ['first_name', 'last_name', 'username', 'display_name'],
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    protected $searchableCounts = [
        'accessories_count',
        'assets_count',
        'licenses_count',
        'consumables_count',
        'accessories_count',
        'manages_users_count',
        'manages_locations_count',
    ];

    /**
     * Virtual column aliases that map a single filter key to a set of real columns
     * searched via CONCAT (SQL) so that, for example, filtering by "name" searches
     * across both first_name and last_name together.
     *
     * Because "name" is not a real column on the users table we cannot add it to
     * $searchableAttributes; this map bridges that gap for structured filter queries.
     *
     * @var array<string, list<string>>
     */
    protected $searchableVirtualColumns = [
        'name' => ['first_name', 'last_name'],
    ];

    /**
     * Maps filter/API keys to the actual Eloquent relation names used in
     * $searchableRelations.  The User model uses "userloc" as its location
     * relation name (to avoid a collision with the framework's own "location"
     * magic), but every consumer — UI and API alike — sends the key "location".
     *
     * @var array<string, string>
     */
    protected $searchableRelationAliases = [
        'location' => 'userloc',
    ];

    /**
     * Narrow structured-filter relation columns for specific UI/API filter keys.
     *
     * The advanced-search "location" field represents the location name, so
     * structured filters should target only userloc.name (not address/city/etc).
     *
     * @var array<string, list<string>>
     */
    protected $searchableRelationFilterColumns = [
        'location' => ['name'],
    ];

    /**
     * This sets the name property on the user. It's not a real field in the database
     * (since we use first_name and last_name), but the Laravel mailable method
     * uses this to determine the name of the user to send emails to.
     *
     * We only have to do this on the User model and no other models because other
     * first-class objects have a name field.
     *
     * @return void
     */
    public $name;

    protected static function boot()
    {
        parent::boot();

        static::retrieved(
            function ($user) {
                $user->name = $user->getFullNameAttribute();
            }
        );
    }

    protected static function booted(): void
    {
        static::forceDeleted(function (User $user) {
            CheckoutRequest::where(['user_id' => $user->id])->forceDelete();
            $user->purgeAssociatedPassportTokens();
            // Soft-delete leaves pivot rows so a restore preserves group
            // memberships. Force-delete removes the row entirely, so the
            // pivot rows would point at nothing.
            $user->groups()->detach();
        });

        static::softDeleted(function (User $user) {
            CheckoutRequest::where(['user_id' => $user->id])->delete();
            $user->revokeAssociatedPassportTokens();
        });
    }

    /**
     * Revoke all Passport access/refresh tokens associated with this user.
     */
    private function revokeAssociatedPassportTokens(): void
    {
        $accessTokenIds = DB::table('oauth_access_tokens')
            ->where('user_id', $this->id)
            ->pluck('id');

        if ($accessTokenIds->isEmpty()) {
            return;
        }

        DB::table('oauth_access_tokens')
            ->whereIn('id', $accessTokenIds)
            ->update(['revoked' => true]);

        DB::table('oauth_refresh_tokens')
            ->whereIn('access_token_id', $accessTokenIds)
            ->update(['revoked' => true]);
    }

    /**
     * Hard-delete all Passport access/refresh tokens associated with this user.
     */
    private function purgeAssociatedPassportTokens(): void
    {
        $accessTokenIds = DB::table('oauth_access_tokens')
            ->where('user_id', $this->id)
            ->pluck('id');

        if ($accessTokenIds->isNotEmpty()) {
            DB::table('oauth_refresh_tokens')
                ->whereIn('access_token_id', $accessTokenIds)
                ->delete();
        }

        DB::table('oauth_access_tokens')
            ->where('user_id', $this->id)
            ->delete();
    }

    /**
     * This overrides the SnipeModel displayName accessor to return the full name if display_name is not set
     *
     * @see SnipeModel::displayName()
     *
     * @return Attribute<string|null, mixed>
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => ($value !== null && $value !== '') ? $value : $this->getFullNameAttribute(),
        );
    }

    public function isAvatarExternal(): bool
    {
        // Check if it's a google avatar or some external avatar
        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return true;
        }

        return false;
    }

    public function hasIndividualPermissions()
    {
        $permissions = [];

        if (is_object($this->permissions)) {
            $permissions = json_decode(json_encode($this->permissions), true);
        }

        if (is_string($this->permissions)) {
            $permissions = json_decode($this->permissions, true);
        }

        if (($permissions) && (is_array($permissions))) {
            foreach ($permissions as $permission) {
                if ($permission != 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Build a list of effective user permissions grouped by permission section.
     *
     * Includes explicit denials from user or group permissions so the UI can
     * show both allowed and denied entries.
     *
     * This is kind of duplicative from the other permission-checking methods, but it allows us to build a
     * list of permissions for display purposes without having to do a lot of super-confusing and
     * redundant checks in the UI layer.
     *
     * This will likely go away once we refactor the permissions to be in a database table instead of the
     * stupiud config file.
     */
    public function getEffectivePermissionsBySection(): array
    {
        $displayablePermissions = collect(config('permissions'))
            ->map(static fn (array $permissions): array => array_values(array_filter($permissions, static fn (array $permission): bool => ($permission['display'] ?? false) === true)))
            ->all();

        $configuredPermissions = collect($displayablePermissions)
            ->flatMap(static function (array $permissions, string $section) {
                return collect($permissions)->map(static function (array $permission) use ($section): array {
                    return [
                        'section' => $section,
                        'permission' => $permission['permission'],
                    ];
                });
            })
            ->unique('permission')
            ->values();

        $directPermissions = $this->decodePermissions();
        $directPermissions = is_array($directPermissions) ? $directPermissions : [];

        $groupGrantsByPermission = [];
        $groupDenialsByPermission = [];
        foreach ($this->groups as $group) {
            $groupPermissions = $group->decodePermissions();
            if (! is_array($groupPermissions)) {
                continue;
            }

            foreach ($groupPermissions as $permissionKey => $permissionValue) {
                if ((int) $permissionValue === 1) {
                    $groupGrantsByPermission[$permissionKey][] = $group->name;
                } elseif ((int) $permissionValue === -1) {
                    $groupDenialsByPermission[$permissionKey][] = $group->name;
                }
            }
        }

        $effectiveBySection = [];
        foreach ($configuredPermissions as $permissionConfig) {
            $permissionKey = $permissionConfig['permission'];
            $directPermissionValue = (int) ($directPermissions[$permissionKey] ?? 0);
            $isAllowed = $this->hasAccess($permissionKey);
            $isDenied = ($directPermissionValue === -1) || ((count($groupDenialsByPermission[$permissionKey] ?? []) > 0) && ! $isAllowed);

            if (! $isAllowed && ! $isDenied) {
                continue;
            }

            $status = $isDenied ? 'denied' : 'allowed';
            $source = 'group';
            $sourceGroups = $isDenied
                ? ($groupDenialsByPermission[$permissionKey] ?? [])
                : ($groupGrantsByPermission[$permissionKey] ?? []);

            if ($isDenied && $directPermissionValue === -1) {
                $source = 'individual';
                $sourceGroups = [];
            } elseif ($this->isSuperUser()) {
                $source = 'superuser';
                $sourceGroups = [];
            } elseif (! $isDenied && $directPermissionValue === 1) {
                $source = 'individual';
                $sourceGroups = [];
            }

            $effectiveBySection[$permissionConfig['section']][] = [
                'permission' => $permissionKey,
                'status' => $status,
                'source' => $source,
                'groups' => array_values(array_unique($sourceGroups)),
                'source_label' => $this->buildPermissionSourceLabel(
                    status: $status,
                    source: $source,
                    sourceGroups: $sourceGroups
                ),
            ];
        }

        return $effectiveBySection;
    }

    /**
     * Build a compact source label for a permission entry.
     */
    private function buildPermissionSourceLabel(string $status, string $source, array $sourceGroups = []): string
    {
        $statusLabel = $status === 'denied' ? 'Denied' : 'Allowed';
        $sourceLabel = match ($source) {
            'individual' => 'Individual',
            'superuser' => 'Superuser',
            default => 'Group',
        };

        if ($sourceGroups === []) {
            return $statusLabel.' ('.$sourceLabel.')';
        }

        return $statusLabel.' ('.$sourceLabel.'): '.implode(', ', array_values(array_unique($sourceGroups)));
    }

    /**
     * Internally check the user permission for the given section
     *
     * @return bool
     */
    protected function checkPermissionSection($section)
    {
        $user_groups = $this->groups;
        if (($this->permissions == '') && (count($user_groups) == 0)) {
            return false;
        }

        $user_permissions = $this->permissions;

        if (is_object($this->permissions)) {
            $user_permissions = json_decode(json_encode($this->permissions), true);
        }

        if (is_string($this->permissions)) {
            $user_permissions = json_decode($this->permissions, true);
        }

        $is_user_section_permissions_set = ($user_permissions != '') && array_key_exists($section, $user_permissions);
        // If the user is explicitly granted, return true
        if ($is_user_section_permissions_set && ($user_permissions[$section] == '1')) {
            return true;
        }
        // If the user is explicitly denied, return false
        if ($is_user_section_permissions_set && ($user_permissions[$section] == '-1')) {
            return false;
        }

        // Loop through the groups to see if any of them grant this permission
        foreach ($user_groups as $user_group) {
            $group_permissions = (array) json_decode($user_group->permissions, true);
            if (((array_key_exists($section, $group_permissions)) && ($group_permissions[$section] == '1'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check user permissions
     *
     * Parses the user and group permission masks to see if the user
     * is authorized to do the thing
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return bool
     */
    public function hasAccess($section)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->checkPermissionSection($section);
    }

    /**
     * Checks if the user is a SuperUser
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->checkPermissionSection('superuser');
    }

    public function canImpersonate(): bool
    {
        if (! $this->isSuperUser()) {
            return false;
        }

        $allowed = array_map('mb_strtolower', (array) config('app.user_impersonation_usernames'));

        return in_array(mb_strtolower((string) $this->username), $allowed, true);
    }

    public function mayImpersonate(User $target): bool
    {
        return $this->canImpersonate()
            && $target->id !== $this->id
            && ! $target->isSuperUser()
            && $target->deleted_at === null
            && $target->activated == 1;
    }

    public function twoFactorResettable(): bool
    {
        return $this->activated == '1'
            && $this->two_factor_active_and_enrolled()
            && ! in_array(Setting::getSettings()->two_factor_enabled, ['0', '', null], true);
    }

    /**
     * Checks if the user is an admin
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v8.1.18]
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->checkPermissionSection('admin');
    }

    /**
     * Whether this user is allowed to save another user with no company
     * assignment. Under floater mode, "no companies" means the target gains
     * system-wide visibility, so the actor needs to already be at that
     * privilege level: superusers can always grant it; floater actors
     * (themselves uncompanied) can grant it to others without escalating —
     * they already have the same access. The only blocked case is a
     * *companied* non-superuser trying to elevate someone to floater, which
     * would be a genuine escalation. When floater mode itself is off — or
     * FMCS is off — there's no floater to grant, so the answer is yes. See
     * #19200.
     */
    public function canGrantFloaterStatus(): bool
    {
        $settings = Setting::getSettings();

        if (! $settings->full_multiple_companies_support) {
            return true;
        }
        if (! $settings->null_company_is_floater) {
            return true;
        }
        if ($this->isSuperUser()) {
            return true;
        }

        return ! $this->companies()->exists();
    }

    /**
     * Checks if the user can edit their own profile
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.3.4]
     */
    public function canEditProfile(): bool
    {

        $setting = Setting::getSettings();
        if ($setting->profile_edit == 1) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the user is deletable
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.3.4]
     *
     * @return bool
     */
    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && $this->hasNoAssignmentBlockers()
            && ($this->deleted_at == '');
    }

    public function calendarEventDefinitions(): array
    {
        return [
            [
                'field' => 'start_date',
                'event_type' => 'user.start_date',
                'all_day' => true,
            ],
            [
                'field' => 'end_date',
                'event_type' => 'user.end_date',
                'all_day' => true,
            ],
        ];
    }

    /**
     * SCIM library shim. This could maybe be moved into the user model itself,
     * but since it hooks into arietimmerman's library, this was the easier path
     * without pulling it into traits. Returns the primary key so a scim-client
     * fetch of /Me matches the shape of a fetch of /Users/{id}
     */
    public function getUserId(): int
    {
        return (int) $this->id;
    }

    /**
     * The association-blocker half of isDeletable(): true only when the
     * user has no assigned assets / accessories / licenses / consumables
     * and isn't managing any users or locations. Split out from
     * isDeletable() so scripts running outside a request context,
     * Artisan `snipeit:ldap-sync --delete` flow in particular, can share
     * the exact same rule without needing an authenticated Gate user to
     * satisfy the delete-permission check.
     */
    public function hasNoAssignmentBlockers(): bool
    {
        return (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->manages_users_count ?? $this->managesUsers()->count()) === 0)
            && (($this->manages_locations_count ?? $this->managedLocations()->count()) === 0);
    }

    /**
     * Establishes the user -> company relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    /**
     * @deprecated Use companies() for multi-company support. This resolves via
     * the legacy_company_id mirror, which is kept in sync with the pivot but is
     * not authoritative and may be dropped in a future release.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'legacy_company_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user');
    }

    /**
     * Returns whether an FMCS company check should allow this user to receive
     * an item that belongs to the given company (null = uncompanied item).
     *
     * Mirrors the rules in CompanyableTrait::canCheckoutTo so the User-target
     * branch behaves the same as every other companyable target:
     *  - Both sides uncompanied → allowed (null↔null).
     *  - Item uncompanied, user companied → only when null_company_is_floater
     *    is on (floater mode treats null-company items as system-wide).
     *  - Item companied, user uncompanied → only when null_company_is_floater
     *    is on (floater users see everything).
     *  - Both sides companied → user's pivot must contain the item's company.
     */
    public function canReceiveFromCompany(?int $companyId): bool
    {
        // Query the pivot directly to avoid the Company model's FMCS global scope,
        // which would restrict results to the current actor's visible companies.
        $userCompanyIds = DB::table('company_user')
            ->where('user_id', $this->id)
            ->pluck('company_id');

        if (is_null($companyId)) {
            if ((bool) Setting::getSettings()->null_company_is_floater) {
                return true;
            }

            return $userCompanyIds->isEmpty();
        }

        if ($userCompanyIds->isEmpty()) {
            return (bool) Setting::getSettings()->null_company_is_floater;
        }

        if ($userCompanyIds->contains($companyId)) {
            return true;
        }

        // Membership in a parent company implies access to its children — accept
        // an item from a child company when the user belongs to its parent.
        return DB::table('companies')
            ->where('id', $companyId)
            ->whereIn('parent_id', $userCompanyIds)
            ->exists();
    }

    /**
     * Returns all companies this user has access to — the pivot memberships plus
     * any child companies of those memberships (one-level-deep hierarchy).
     * Used to scope FMCS dropdowns to companies the user is allowed to work with.
     */
    public function allCompanies(): Collection
    {
        $direct = $this->companies->unique('id');

        if ($direct->isEmpty()) {
            return $direct->values();
        }

        $children = Company::whereIn('parent_id', $direct->pluck('id'))->get();

        return $direct->concat($children)->unique('id')->values();
    }

    /**
     * Sync company pivot membership and log the change if the set of companies changed.
     *
     * When called after $user->save() in the same request, UserObserver::updating() will
     * have already written an Actionlog row and stored its ID in $this->currentUpdateLogId.
     * In that case we merge the company change into that existing entry so that a single
     * edit session (field changes + company changes) produces one log row, not two.
     */
    public function syncCompaniesWithLogging(array $companyIds): void
    {
        // Belt-and-suspenders coercion so no caller — API, web, importer,
        // artisan, tinker, a queue job, a future FMCS refactor — can slip
        // nested arrays or other non-scalars into ->sync(), which would
        // bind them into SQL query params and trip "Array to string
        // conversion" (elevated to ErrorException by Laravel's error
        // handler). Reduce to a flat, unique list of positive int ids.
        $companyIds = array_values(array_unique(array_filter(
            array_map('intval', array_filter($companyIds, 'is_scalar'))
        )));

        $oldIds = $this->companies()->orderBy('companies.id')->pluck('companies.id')->toArray();
        $this->companies()->sync($companyIds);
        $newIds = $this->companies()->orderBy('companies.id')->pluck('companies.id')->toArray();

        $this->syncLegacyCompanyIdMirror();

        if ($oldIds === $newIds) {
            return;
        }

        $companyChange = ['companies' => ['old' => $oldIds, 'new' => $newIds]];

        if ($this->currentUpdateLogId && ($existing = Actionlog::find($this->currentUpdateLogId))) {
            $meta = json_decode($existing->log_meta ?? '{}', true) ?: [];
            $existing->log_meta = json_encode(array_merge($meta, $companyChange));
            $existing->save();
            $this->currentUpdateLogId = null;

            return;
        }

        $logAction = new Actionlog;
        $logAction->item_type = static::class;
        $logAction->item_id = $this->id;
        $logAction->target_type = static::class;
        $logAction->target_id = $this->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->log_meta = json_encode($companyChange);
        $logAction->logaction('update');
    }

    /**
     * Sync group pivot membership and log the change if the set of groups changed.
     *
     * Same as syncCompaniesWithLogging(): if UserObserver::updating()
     * already wrote an Actionlog for this edit session (via
     * $this->currentUpdateLogId), the group diff is merged into that
     * existing row so a single edit produces one log entry, not two.
     * Otherwise a standalone Actionlog is written.
     */
    public function syncGroupsWithLogging(array $groupIds): void
    {
        // Same defensive coercion as the company version: reduce any
        // callable payload shape (API JSON, form input, bulk edit,
        // future LDAP importer) to a flat, unique list of positive int
        // ids before it hits ->sync(). Prevents "Array to string
        // conversion" tripping on nested arrays and drops junk shapes
        // (null, booleans, unparseable strings) silently.
        $groupIds = array_values(array_unique(array_filter(
            array_map('intval', array_filter($groupIds, 'is_scalar'))
        )));

        $oldSnapshot = $this->currentGroupSnapshot();
        $this->groups()->sync($groupIds);
        $newSnapshot = $this->currentGroupSnapshot();

        $this->recordGroupsChange($oldSnapshot, $newSnapshot);
    }

    /**
     * Log the attachment of a single group to this user. Called from
     * paths that mutate the pivot without going through
     * syncGroupsWithLogging() (SCIM SnipeMutableCollection::add(),
     * LdapSync attaching the LDAP default group, future sync adapters
     * that propagate group membership).
     *
     * The caller has already run ->attach() (or the equivalent). This
     * method's job is to record the delta if it was a real add. When
     * the group was already attached before the call, we no-op so
     * repeat-attach patterns don't produce noise.
     */
    public function logGroupAttached(int $groupId): void
    {
        $newSnapshot = $this->currentGroupSnapshot();
        if (! collect($newSnapshot)->contains('id', $groupId)) {
            // The caller's ->attach() didn't produce a real add (e.g.
            // duplicate insert silently ignored, race with another
            // process, group id doesn't exist). Nothing to log.
            return;
        }

        $oldSnapshot = array_values(array_filter(
            $newSnapshot,
            fn ($entry) => $entry['id'] !== $groupId,
        ));

        $this->recordGroupsChange($oldSnapshot, $newSnapshot);
    }

    /**
     * Log the detachment of a single group. Companion to
     * logGroupAttached(). Called from SCIM's
     * SnipeMutableCollection::remove() and any other path that runs
     * ->detach() on a single group without going through
     * syncGroupsWithLogging().
     */
    public function logGroupDetached(int $groupId): void
    {
        $newSnapshot = $this->currentGroupSnapshot();
        if (collect($newSnapshot)->contains('id', $groupId)) {
            // Detach didn't remove the group (never was attached, or
            // still present for some other reason). Nothing to log.
            return;
        }

        // Look up the detached group's current name so the log records
        // it verbatim, not the id alone. If the group row is somehow
        // gone by the time we look (deleted in the same request),
        // record the id with a placeholder rather than skipping the
        // log entirely, so the pivot mutation is still visible.
        $detached = Group::find($groupId);
        $oldSnapshot = $newSnapshot;
        $oldSnapshot[] = ['id' => $groupId, 'name' => $detached ? $detached->name : '#'.$groupId];
        usort($oldSnapshot, fn ($a, $b) => $a['id'] <=> $b['id']);

        $this->recordGroupsChange($oldSnapshot, $newSnapshot);
    }

    /**
     * Current pivot state as an ordered list of {id, name} pairs.
     * The name is snapshotted at log-write time so the history entry
     * preserves what the group was called when the change happened,
     * regardless of later renames or deletes.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function currentGroupSnapshot(): array
    {
        return $this->groups()
            ->orderBy('permission_groups.id')
            ->get(['permission_groups.id', 'permission_groups.name'])
            ->map(fn ($group) => ['id' => (int) $group->id, 'name' => (string) $group->name])
            ->all();
    }

    /**
     * Write (or merge into) an Actionlog for a groups pivot change.
     * Merges into $this->currentUpdateLogId when the observer already
     * opened a log row for this edit session so field + group
     * changes stay in one log entry. Otherwise writes a standalone
     * 'update' Actionlog row.
     *
     * @param  array<int, array{id: int, name: string}>  $oldSnapshot
     * @param  array<int, array{id: int, name: string}>  $newSnapshot
     */
    private function recordGroupsChange(array $oldSnapshot, array $newSnapshot): void
    {
        if ($oldSnapshot === $newSnapshot) {
            return;
        }

        $groupChange = ['groups' => ['old' => $oldSnapshot, 'new' => $newSnapshot]];

        if ($this->currentUpdateLogId && ($existing = Actionlog::find($this->currentUpdateLogId))) {
            $meta = json_decode($existing->log_meta ?? '{}', true) ?: [];
            $existing->log_meta = json_encode(array_merge($meta, $groupChange));
            $existing->save();
            $this->currentUpdateLogId = null;

            return;
        }

        $logAction = new Actionlog;
        $logAction->item_type = static::class;
        $logAction->item_id = $this->id;
        $logAction->target_type = static::class;
        $logAction->target_id = $this->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->created_by = auth()->id();
        $logAction->log_meta = json_encode($groupChange);
        $logAction->logaction('update');
    }

    /**
     * FMCS-safe wrapper around syncCompaniesWithLogging() for the user
     * update path. Folds the target's memberships in companies the
     * editor cannot see back into the submitted list before syncing,
     * so a scoped editor's save can't silently detach the target from
     * tenants outside the editing user's own membership.
     *
     * Superuser editors skip the merge because they can see every
     * company, so their submission already represents full intent.
     */
    public function syncCompaniesPreservingInvisibleTo(?User $editor, array $submittedCompanyIds): void
    {
        $submitted = array_map('intval', $submittedCompanyIds);

        // FMCS is off, so every user can see every company
        // and there is no invisible-to-editor set to preserve. Superuser
        // editors also skip because their submission already
        // represents full intent across all tenants. A null editor
        // means no scope context (CLI-run importer, seeder, artisan
        // command), which behaves like a superuser edit: sync the
        // submission verbatim because there is no scope to preserve
        // against.
        $fmcsOn = (bool) Setting::getSettings()->full_multiple_companies_support;
        if (! $fmcsOn || $editor === null || $editor->isSuperUser()) {
            $this->syncCompaniesWithLogging($submitted);

            return;
        }

        // Read editor + target memberships directly from the pivot so
        // Company's global CompanyableScope does not filter Companies
        // down to what the acting user (the editor) can see before we
        // compute the invisible-to-editor set. Going through
        // $editor->companies() / $this->companies() under a scoped
        // non-superuser editor would return only the editor's own
        // companies from the Companies side of the join, which turns
        // the whereNotIn below into an empty result and drops every
        // preserved company. See GH #19569.
        $editorDirect = DB::table('company_user')
            ->where('user_id', $editor->id)
            ->pluck('company_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // The editor's reach must match what every gate upstream of this
        // helper enforces (Company::getIdsForCurrentUser(), the
        // SaveUserRequest floater gate, parent_within_scope): direct
        // memberships PLUS their child companies. Using only the direct
        // rows silently drops a permitted child-company submission after
        // the gates have accepted it. Under null_company_is_floater that
        // left the target with no company at all, promoting them to a
        // system-wide floater and escaping tenant isolation. See
        // GHSA-35hv-26vj-9445.
        $editorChildren = empty($editorDirect) ? [] : DB::table('companies')
            ->whereIn('parent_id', $editorDirect)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $editorVisible = array_values(array_unique(array_merge($editorDirect, $editorChildren)));

        $submittedVisible = array_values(array_intersect($submitted, $editorVisible));

        $invisiblePreserved = DB::table('company_user')
            ->where('user_id', $this->id)
            ->whereNotIn('company_id', $editorVisible)
            ->pluck('company_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->syncCompaniesWithLogging(
            array_values(array_unique(array_merge($submittedVisible, $invisiblePreserved))),
        );
    }

    /**
     * Update the legacy users.company_id column so that it mirrors the
     * company_user pivot: empty pivot writes NULL; a non-empty pivot writes
     * the lowest-id pivot entry (arbitrary but stable).
     *
     * The column is a compatibility mirror maintained only for external
     * consumers that historically read it (the API transformer, CSV export
     * templates, SCIM schema mappings). It has NO role in FMCS scoping,
     * checkout eligibility, or any internal application logic. Do not read
     * it from application code.
     *
     * Call this after any write to the company_user pivot so the mirror
     * stays consistent. Writes via the query builder so it doesn't trigger
     * the User model's save events (which would recurse through
     * UserObserver::updating and produce a spurious change log entry).
     */
    public function syncLegacyCompanyIdMirror(): void
    {
        if (! $this->exists) {
            return;
        }

        $lowestPivotId = DB::table('company_user')
            ->where('user_id', $this->id)
            ->orderBy('company_id')
            ->value('company_id');

        $target = $lowestPivotId ? (int) $lowestPivotId : null;

        if ((int) $this->legacy_company_id === (int) $target && ($target !== null || is_null($this->legacy_company_id))) {
            return;
        }

        DB::table('users')->where('id', $this->id)->update(['legacy_company_id' => $target]);
        $this->legacy_company_id = $target;
        $this->syncOriginalAttribute('legacy_company_id');
    }

    /**
     * Establishes the user -> department relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Checks activated status
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return bool
     */
    public function isActivated()
    {
        return $this->activated == 1;
    }

    /**
     * Returns the full name attribute
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $setting = Setting::getSettings();

        if ($setting?->name_display_format == 'last_first') {
            return ($this->last_name) ? $this->last_name.' '.$this->first_name : $this->first_name;
        }

        return $this->last_name ? $this->first_name.' '.$this->last_name : $this->first_name;
    }

    /**
     * @return Attribute<string, never>
     */
    protected function linkLightColor(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                $fallback = '#296282';

                if ($value) {
                    return CssColor::sanitize($value, $fallback);
                }

                if (Setting::getSettings()) {
                    return CssColor::sanitize(Setting::getSettings()->link_light_color, $fallback);
                }

                return CssColor::sanitize($value, $fallback);
            },
        );
    }

    /**
     * @return Attribute<string, never>
     */
    protected function linkDarkColor(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                $fallback = '#5fa4cc';

                if ($value) {
                    return CssColor::sanitize($value, $fallback);
                }

                if (Setting::getSettings()) {
                    return CssColor::sanitize(Setting::getSettings()->link_dark_color, $fallback);
                }

                return CssColor::sanitize($value, $fallback);
            },
        );
    }

    /**
     * @return Attribute<string, never>
     */
    protected function navLinkColor(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                $fallback = '#ffffff';

                if ($value) {
                    return CssColor::sanitize($value, $fallback);
                }

                if (Setting::getSettings()) {
                    return CssColor::sanitize(Setting::getSettings()->nav_link_color, $fallback);
                }

                return CssColor::sanitize($value, $fallback);
            },
        );
    }

    /**
     * Establishes the user -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->morphMany(Asset::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed()->orderBy('id');
    }

    /**
     * Establishes the user -> maintenances relationship
     *
     * This would only be used to return maintenances that this user
     * created.
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'user_id')->withTrashed();
    }

    /**
     * Maintenances whose underlying asset was checked out to this user at the
     * time the maintenance was opened — i.e. the polymorphic checked_out_to_*
     * pair on the maintenances row points at this user.
     *
     * Distinct from maintenances() (which tracks who *created* the record)
     * and from responsibleParty() (whoever is responsible for completion).
     * Used by the user detail view's Maintenances tab and badge count.
     */
    public function assignedMaintenances(): MorphMany
    {
        return $this->morphMany(Maintenance::class, 'checked_out_to')->withTrashed();
    }

    /**
     * Establishes the user -> accessories relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     */
    public function accessories(): BelongsToMany
    {
        return $this->belongsToMany(Accessory::class, 'accessories_checkout', 'assigned_to', 'accessory_id')
            ->where('assigned_type', '=', 'App\Models\User')
            ->withPivot('id', 'created_at', 'note')->withTrashed()->orderBy('accessory_id');
    }

    /**
     * Establishes the user -> consumables relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     */
    public function consumables(): BelongsToMany
    {
        return $this->belongsToMany(Consumable::class, 'consumables_users', 'assigned_to', 'consumable_id')->withPivot('id', 'created_at', 'note')->withTrashed();
    }

    /**
     * Establishes the user -> license seats relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     */
    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'license_seats', 'assigned_to', 'license_id')->withPivot('id', 'created_at', 'updated_at');
    }

    public function directLicenses()
    {
        return $this->belongsToMany(License::class, 'license_seats', 'assigned_to', 'license_id')->withPivot('id', 'created_at', 'updated_at')->wherePivotNull('asset_id')->withTrashed();
    }

    /**
     * Establishes the user -> reportTemplates relationship
     */
    public function reportTemplates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class, 'created_by');
    }

    public function getImageUrl($path = null)
    {
        return $this->present()->gravatar();

    }

    /**
     * Establishes a count of all items assigned
     *
     * @author J. Vinsmoke
     *
     * @since  [v6.1]
     *
     * @return Relation
     */
    public function allAssignedCount()
    {
        $assetsCount = $this->assets()->count();
        $licensesCount = $this->licenses()->count();
        $accessoriesCount = $this->accessories()->count();
        $consumablesCount = $this->consumables()->count();

        $totalCount = $assetsCount + $licensesCount + $accessoriesCount + $consumablesCount;

        return (int) $totalCount;
    }

    /**
     * Establishes the user -> actionlogs relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function userlog()
    {
        return $this->hasMany(Actionlog::class, 'target_id')->where('target_type', '=', self::class)->orderBy('created_at', 'DESC')->withTrashed();
    }

    /**
     * Establishes the user -> location relationship
     *
     * Get the asset's location based on the assigned user
     *
     * @todo - this should be removed once we're sure we've switched it to location()
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function userloc()
    {
        return $this->belongsTo(Location::class, 'location_id')->withTrashed();
    }

    /**
     * Establishes the user -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id')->withTrashed();
    }

    /**
     * Establishes the user -> manager relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id')->withTrashed();
    }

    /**
     * Establishes the user -> managed users relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.4.1]
     *
     * @return Relation
     */
    public function managesUsers()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Establishes the user -> managed locations relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function managedLocations()
    {
        return $this->hasMany(Location::class, 'manager_id');
    }

    /**
     * Establishes the user -> groups relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'users_groups');
    }

    /**
     * Establishes the user -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function assetlog()
    {
        return $this->hasMany(Asset::class, 'id')->withTrashed();
    }

    /**
     * Establishes the user -> acceptances relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v7.0.7]
     *
     * @return Relation
     */
    public function acceptances()
    {
        return $this->hasMany(Actionlog::class, 'target_id')
            ->where('target_type', self::class)
            ->where('action_type', '=', 'accepted')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all assigned items that still have a pending acceptance for this user.
     */
    public function getAssignedItemsWithPendingAcceptance(): Collection
    {
        return CheckoutAcceptance::query()
            ->forUser($this)
            ->pending()
            ->with('checkoutable')
            ->get()
            ->map(fn (CheckoutAcceptance $acceptance) => $acceptance->checkoutable)
            ->filter()
            ->unique(fn ($item) => $item::class.':'.$item->getKey())
            ->values();
    }

    /**
     * Establishes the user -> eula relationship
     *
     * @return Relation
     *
     * @since  [v8.1.16]
     *
     * @author [Godfrey Martinez] [<gmartinez@grokability.com>]
     */
    public function eulas()
    {
        return $this->hasMany(Actionlog::class, 'target_id')
            ->with('item')
            ->select(['id', 'target_id', 'target_type', 'action_type', 'filename', 'accept_signature', 'created_at', 'note', 'item_id', 'item_type'])
            ->where('target_type', self::class)
            ->where('action_type', 'accepted')
            ->whereNotNull('filename')
            ->whereNotNull('accept_signature')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Establishes the user -> requested assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function checkoutRequests()
    {
        return $this->belongsToMany(Asset::class, 'checkout_requests', 'user_id', 'requestable_id')->whereNull('canceled_at');
    }

    /**
     * Set a common string when the user has been imported/synced from:
     *
     * - LDAP without password syncing
     * - SCIM
     * - CSV import where no password was provided
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.2.0]
     *
     * Stored raw (not bcrypted) on users who have no meaningful password
     * on file — created deactivated via the UI, imported without a
     * password, synced from LDAP/SCIM, or otherwise. Because it is stored
     * as a plain string, Hash::check at login will never return true no
     * matter what the input is, so there is no possible authentication
     * path against this placeholder.
     *
     * @return string
     */
    public function noPassword()
    {
        return '*** NO PASSWORD ***';
    }

    /**
     * Generate email from full name
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @param  string  $query
     * @return string
     */
    /**
     * The MySQL/MariaDB default collation utf8mb4_unicode_ci folds accents
     * and case, so `WHERE username = ?` on that engine can silently route
     * 'snípeitreport3' (with an accent on the i) to the row for 'snipeitreport3'.
     *
     * Federated/SSO auth flows (SAML, LDAP, REMOTE_USER, Google OAuth) must call
     * this after their username lookup so an attacker-controlled external identifier
     * can't authenticate as a different local account. hash_equals runs in constant
     * time so this check doesn't leak any timing signal.
     *
     * Returns the user when the strings match byte-for-byte after lowercasing,
     * null otherwise. (This was previously just bite-for-bite, but most IdPs are
     * case-insensitive, so we normalize to lowercase before comparing.)
     */
    public static function verifyExactUsernameMatch(?self $user, string $expected): ?self
    {
        if ($user === null) {
            return null;
        }

        return hash_equals(mb_strtolower((string) $user->username), mb_strtolower($expected)) ? $user : null;
    }

    public static function generateEmailFromFullName($name)
    {
        $username = self::generateFormattedNameFromFullName($name, Setting::getSettings()->email_format);

        return $username['username'].'@'.Setting::getSettings()->email_domain;
    }

    public static function generateFormattedNameFromFullName($users_name, $format = 'filastname')
    {

        // If there was only one name given
        if (strpos($users_name, ' ') === false) {
            $first_name = $users_name;
            $last_name = '';
            $username = $users_name;
        } else {

            // Respect name_display_format when splitting a joined full
            // name into first and last.
            if (Setting::getSettings()?->name_display_format === 'last_first') {
                [$last_name, $first_name] = explode(' ', $users_name, 2);
            } else {
                [$first_name, $last_name] = explode(' ', $users_name, 2);
            }

            // Assume filastname by default
            $username = str_slug(substr($first_name, 0, 1).$last_name);

            if ($format == 'firstname.lastname') {
                $username = str_slug($first_name).'.'.str_slug($last_name);
            } elseif ($format == 'lastnamefirstinitial') {
                $username = str_slug($last_name.substr($first_name, 0, 1));
            } elseif ($format == 'firstintial.lastname') {
                $username = substr($first_name, 0, 1).'.'.str_slug($last_name);
            } elseif ($format == 'firstname_lastname') {
                $username = str_slug($first_name).'_'.str_slug($last_name);
            } elseif ($format == 'firstname') {
                $username = str_slug($first_name);
            } elseif ($format == 'lastname') {
                $username = str_slug($last_name);
            } elseif ($format == 'firstinitial.lastname') {
                $username = str_slug(substr($first_name, 0, 1).'.'.str_slug($last_name));
            } elseif ($format == 'lastname_firstinitial') {
                $username = str_slug($last_name).'_'.str_slug(substr($first_name, 0, 1));
            } elseif ($format == 'lastname.firstinitial') {
                $username = str_slug($last_name).'.'.str_slug(substr($first_name, 0, 1));
            } elseif ($format == 'firstnamelastname') {
                $username = str_slug($first_name).str_slug($last_name);
            } elseif ($format == 'firstnamelastinitial') {
                $username = str_slug(($first_name.substr($last_name, 0, 1)));
            } elseif ($format == 'lastname.firstname') {
                $username = str_slug($last_name).'.'.str_slug($first_name);
            }
        }

        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['username'] = strtolower($username);

        return $user;
    }

    /**
     * Check whether two-factor authorization is requiredfor this user
     *
     * 0 = 2FA disabled
     * 1 = 2FA optional
     * 2 = 2FA universally required
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return bool
     */
    public function two_factor_active()
    {

        // If the 2FA is optional and the user has opted in
        if ((Setting::getSettings()->two_factor_enabled == '1') && ($this->two_factor_optin == '1')) {
            return true;
        }

        // If the 2FA is required for everyone so is implicitly active
        elseif (Setting::getSettings()->two_factor_enabled == '2') {
            return true;
        }

        return false;
    }

    /**
     * Check whether two-factor authorization is required and the user has activated it
     * and enrolled a device
     *
     * 0 = 2FA disabled
     * 1 = 2FA optional
     * 2 = 2FA universally required
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.6.14]
     *
     * @return bool
     */
    public function two_factor_active_and_enrolled()
    {

        // If the 2FA is optional and the user has opted in and is enrolled
        if ((Setting::getSettings()->two_factor_enabled == '1') && ($this->two_factor_optin == '1') && ($this->two_factor_enrolled == '1')) {
            return true;
        }
        // If the 2FA is required for everyone and the user has enrolled
        elseif ((Setting::getSettings()->two_factor_enabled == '2') && ($this->two_factor_enrolled)) {
            return true;
        }

        return false;

    }

    /**
     * Get the admin user who created this user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v6.0.5]
     *
     * @return Relation
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Decode JSON permissions into array
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return array | \stdClass
     */
    public function decodePermissions()
    {
        // If the permissions are an array, convert it to JSON
        if (is_array($this->permissions)) {
            $this->permissions = json_encode($this->permissions);
        }

        $permissions = json_decode($this->permissions ?? '{}', JSON_OBJECT_AS_ARRAY);

        // Otherwise, loop through the permissions and cast the values as integers
        if ((is_array($permissions)) && ($permissions)) {
            foreach ($permissions as $permission => $value) {

                if (! is_int($permission)) {
                    $permissions[$permission] = (int) $value;
                } else {
                    \Log::info('Weird data here - skipping it');
                    unset($permissions[$permission]);
                }
            }

            return $permissions ?: new \stdClass;
        }

        return new \stdClass;
    }

    /**
     * Run additional, advanced searches. Overrides advancedTextSearch
     * on the Searchable trait to also search user name with spaces in
     * it via orWhereMultipleColumns.
     *
     * Both column orders are tried so a user named "John Doe" matches
     * both "John Doe" and "Doe John" regardless of the install's
     * name_display_format. Issue #19626.
     *
     * @param  array<int, string>  $terms  The search terms
     */
    public function advancedTextSearch(Builder $outer, Builder $nested, array $terms): void
    {
        foreach ($terms as $term) {
            $nested->orWhereMultipleColumns(
                [
                    'users.first_name',
                    'users.last_name',
                ], $term
            )->orWhereMultipleColumns(
                [
                    'users.last_name',
                    'users.first_name',
                ], $term
            );
        }
    }

    /**
     * Get the preferred locale for the user.
     *
     * This uses the HasLocalePreference contract to determine the user's preferred locale,
     * used by Laravel's mail system to determine the locale for sending emails.
     * https://laravel.com/docs/11.x/mail#user-preferred-locales
     */
    public function preferredLocale(): string
    {
        return $this->locale ?? Setting::getSettings()->locale ?? config('app.locale');
    }

    protected bool $userTotalCostComputed = false;

    public function getUserTotalCost()
    {
        if ($this->userTotalCostComputed) {
            return $this;
        }

        $this->asset_cost = (float) $this->assets()->sum('purchase_cost');
        $this->license_cost = (float) $this->licenses()->sum('purchase_cost');
        $this->maintenance_cost = (float) $this->assignedMaintenances()->sum('cost');

        $this->accessory_cost = $this->sumPivotUnitCosts(
            pivotTable: 'accessories_checkout',
            pivotFk: 'accessory_id',
            modelClass: \App\Models\Accessory::class,
            extraWhere: ['assigned_type' => \App\Models\User::class],
        );
        $this->consumable_cost = $this->sumPivotUnitCosts(
            pivotTable: 'consumables_users',
            pivotFk: 'consumable_id',
            modelClass: \App\Models\Consumable::class,
        );

        $this->total_user_cost = $this->asset_cost + $this->accessory_cost + $this->consumable_cost + $this->license_cost + $this->maintenance_cost;

        $this->userTotalCostComputed = true;

        return $this;
    }

    /**
     * Sum (pivot_row_count * last_unit_cost) across every distinct
     * item this user has any pivot rows for.
     *
     * $modelClass must use the HasOrders trait.
     *
     * @param  array<string, mixed>  $extraWhere  additional pivot-side filters (e.g. polymorphic assigned_type)
     */
    private function sumPivotUnitCosts(
        string $pivotTable,
        string $pivotFk,
        string $modelClass,
        array $extraWhere = [],
    ): float {
        $countsByItem = \Illuminate\Support\Facades\DB::table($pivotTable)
            ->where('assigned_to', $this->id);
        foreach ($extraWhere as $column => $value) {
            $countsByItem->where($column, $value);
        }
        $countsByItem = $countsByItem
            ->groupBy($pivotFk)
            ->select($pivotFk)
            ->selectRaw('COUNT(*) as pivot_count')
            ->pluck('pivot_count', $pivotFk)
            ->all();

        if ($countsByItem === []) {
            return 0.0;
        }

        $items = $modelClass::query()
            ->whereIn('id', array_keys($countsByItem))
            ->get(['id', 'default_purchase_cost']);

        $unitCosts = $modelClass::lastUnitCostsFor($items);

        $total = 0.0;
        foreach ($countsByItem as $itemId => $count) {
            $total += (float) ($unitCosts[$itemId] ?? 0) * (int) $count;
        }

        return $total;
    }

    public function newEloquentBuilder($query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    /**
     * Get all direct and indirect subordinates for this user.
     *
     * @return Collection
     */
    public function getAllSubordinates()
    {
        $subordinates = collect();
        $this->fetchSubordinatesRecursive($this, $subordinates);

        return $subordinates->unique('id');
    }

    /**
     * Get all direct and indirect subordinates for this user, including self.
     *
     * @return Collection
     */
    public function getAllSubordinatesIncludingSelf()
    {
        $subordinates = collect([$this]);
        $this->fetchSubordinatesRecursive($this, $subordinates);

        return $subordinates->unique('id');
    }

    /**
     * Recursive helper function to fetch subordinates.
     */
    protected function fetchSubordinatesRecursive(User $manager, Collection &$subs)
    {
        // Eager load 'managesUsers' to prevent N+1 queries in recursion
        $directSubordinates = $manager->managesUsers()->with('managesUsers')->get();

        foreach ($directSubordinates as $directSubordinate) {
            // Add subordinate if not already in the collection
            if (! $subs->contains('id', $directSubordinate->id)) {
                $subs->push($directSubordinate);
                // Recursive call for this subordinate's subordinates
                $this->fetchSubordinatesRecursive($directSubordinate, $subs);
            }
        }
    }

    /**
     * Check if the current user is a direct or indirect manager of the given user.
     */
    public function isManagerOf(User $userToCheck): bool
    {
        // Optimization: If it's the same user, they are not their own manager
        if ($this->id === $userToCheck->id) {
            return false;
        }

        // User::$rules prevents new cycles via non_circular on manager_id,
        // but legacy rows or two-pass CSV imports can still exist in the DB.
        // The depth cap of 50 matches the default in the non_circular validator.
        $visited = [$userToCheck->id => true];
        $manager = $userToCheck->load('manager')->manager;
        $hops = 0;

        while ($manager && $hops++ < 50) {
            if ($manager->id === $this->id) {
                return true;
            }
            if (isset($visited[$manager->id])) {
                return false;
            }
            $visited[$manager->id] = true;
            $manager = $manager->load('manager')->manager;
        }

        return false;
    }
}
