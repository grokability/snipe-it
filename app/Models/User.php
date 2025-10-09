<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use App\Presenters\UserPresenter;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Watson\Validating\ValidatingTrait;

class User extends SnipeModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, HasLocalePreference
{
    use HasFactory;
    use CompanyableTrait;
    use HasUploads;

    protected $presenter = UserPresenter::class;
    use SoftDeletes, ValidatingTrait, Loggable;
    use Authenticatable, Authorizable, CanResetPassword, HasApiTokens;
    use UniqueUndeletedTrait;
    use Notifiable;
    use Presentable;
    use Searchable;

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

    protected $fillable = [
        'activated',
        'address',
        'city',
        'company_id',
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
        'manager_id'   => 'integer',
        'location_id'  => 'integer',
        'company_id'   => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    /**
     * Model validation rules
     *
     * @var array
     */

    protected $rules = [
        'first_name'              => 'required|string|min:1|max:191',
        'last_name'               => 'nullable|string|max:191',
        'display_name'            => 'nullable|string|max:191',
        'username'                => 'required|string|min:1|unique_undeleted|max:191',
        'email'                   => 'email|nullable|max:191',
        'password'                => 'required|min:8',
        'locale'                  => 'max:10|nullable',
        'website'                 => 'url|nullable|max:191',
        'manager_id'              => 'nullable|exists:users,id|cant_manage_self',
        'location_id'             => 'exists:locations,id|nullable|fmcs_location',
        'start_date'              => 'nullable|date_format:Y-m-d',
        'end_date'                => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        'autoassign_licenses'     => 'boolean',
        'address'                 => 'nullable|string|max:191',
        'city'                    => 'nullable|string|max:191',
        'state'                   => 'nullable|string|max:191',
        'country'                 => 'min:2|max:191|nullable',
        'zip'                     => 'max:10|nullable',
        'vip'                     => 'boolean',
        'remote'                  => 'boolean',
        'activated'               => 'boolean',
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
        'userloc'    => ['name', 'address', 'address2', 'city', 'state', 'zip'],
        'department' => ['name'],
        'groups'     => ['name'],
        'company'    => ['name'],
        'manager'    => ['first_name', 'last_name', 'username', 'display_name'],
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
        });

        static::softDeleted(function (User $user) {
            CheckoutRequest::where(['user_id' => $user->id])->delete();
        });
    }

    /**
     * This overrides the SnipeModel displayName accessor to return the full name if display_name is not set
     * @see SnipeModel::displayName()
     * @return Attribute
     */

    protected function displayName(): Attribute
    {
        return Attribute:: make(
            get: fn(mixed $value) => $value ?? $this->getFullNameAttribute(),
        );
    }

    public function isAvatarExternal() : bool
    {
        // Check if it's a google avatar or some external avatar
        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return true;
        }

        return false;
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
        //If the user is explicitly granted, return true
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
     * @since  [v1.0]
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
     * @since  [v1.0]
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->checkPermissionSection('superuser');
    }

    /**
     * Checks if the user is an admin
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v8.1.18]
     * @return bool
     */
    public function isAdmin()
    {
        return $this->checkPermissionSection('admin');
    }


    /**
     * Checks if the user can edit their own profile
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v6.3.4]
     * @return bool
     */
    public function canEditProfile() : bool
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
     * @since  [v6.3.4]
     * @return bool
     */
    public function isDeletable()
    {

        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->manages_users_count ?? $this->managesUsers()->count()) === 0)
            && (($this->manages_locations_count ?? $this->managedLocations()->count()) === 0)
            && ($this->deleted_at == '');
    }


    /**
     * Establishes the user -> company relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Establishes the user -> department relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    /**
     * Checks activated status
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
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
     * @since  [v2.0]
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
     * Establishes the user -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->morphMany(\App\Models\Asset::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed()->orderBy('id');
    }

    /**
     * Establishes the user -> maintenances relationship
     *
     * This would only be used to return maintenances that this user
     * created.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function maintenances()
    {
        return $this->hasMany(\App\Models\Maintenance::class, 'user_id')->withTrashed();
    }

    /**
     * Establishes the user -> accessories relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function accessories()
    {
        return $this->belongsToMany(\App\Models\Accessory::class, 'accessories_checkout', 'assigned_to', 'accessory_id')
            ->where('assigned_type', '=', 'App\Models\User')
            ->withPivot('id', 'created_at', 'note')->withTrashed()->orderBy('accessory_id');
    }

    /**
     * Establishes the user -> consumables relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function consumables()
    {
        return $this->belongsToMany(\App\Models\Consumable::class, 'consumables_users', 'assigned_to', 'consumable_id')->withPivot('id', 'created_at', 'note')->withTrashed();
    }

    /**
     * Establishes the user -> license seats relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenses()
    {
        return $this->belongsToMany(\App\Models\License::class, 'license_seats', 'assigned_to', 'license_id')->withPivot('id', 'created_at', 'updated_at');
    }

    /**
     * Establishes the user -> reportTemplates relationship
     */
    public function reportTemplates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class, 'created_by');
    }

    /**
     * Establishes a count of all items assigned
     *
     * @author J. Vinsmoke
     * @since  [v6.1]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    Public function allAssignedCount()
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
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userlog()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'target_id')->where('target_type', '=', self::class)->orderBy('created_at', 'DESC')->withTrashed();
    }

    /**
     * Establishes the user -> location relationship
     *
     * Get the asset's location based on the assigned user
     *
     * @todo - this should be removed once we're sure we've switched it to location()
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userloc()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id')->withTrashed();
    }

    /**
     * Establishes the user -> location relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id')->withTrashed();
    }

    /**
     * Establishes the user -> manager relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id')->withTrashed();
    }

    /**
     * Establishes the user -> managed users relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v6.4.1]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function managesUsers()
    {
        return $this->hasMany(\App\Models\User::class, 'manager_id');
    }


    /**
     * Establishes the user -> managed locations relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function managedLocations()
    {
        return $this->hasMany(\App\Models\Location::class, 'manager_id');
    }

    /**
     * Establishes the user -> groups relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function groups()
    {
        return $this->belongsToMany(\App\Models\Group::class, 'users_groups');
    }

    /**
     * Establishes the user -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assetlog()
    {
        return $this->hasMany(\App\Models\Asset::class, 'id')->withTrashed();
    }



    /**
     * Establishes the user -> acceptances relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v7.0.7]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function acceptances()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'target_id')
            ->where('target_type', self::class)
            ->where('action_type', '=', 'accepted')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Establishes the user -> eula relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     * @since  [v8.1.16]
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
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
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
     * @since  [v6.2.0]
     * @return string
     */
    public function noPassword()
    {
        return "*** NO PASSWORD ***";
    }


    /**
     * Query builder scope to return NOT-deleted users
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     *
     * @param  string $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeGetNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Query builder scope to return users by email or username
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     *
     * @param  string $query
     * @param  string $user_username
     * @param  string $user_email
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeMatchEmailOrUsername($query, $user_username, $user_email)
    {
        return $query->where('email', '=', $user_email)
            ->orWhere('username', '=', $user_username)
            ->orWhere('username', '=', $user_email);
    }

    /**
     * Generate email from full name
     * 
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v2.0]
     *
     * @param  string $query
     * @return string
     */
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

            list($first_name, $last_name) = explode(' ', $users_name, 2);

            // Assume filastname by default
            $username = str_slug(substr($first_name, 0, 1).$last_name);

            if ($format=='firstname.lastname') {
                $username = str_slug($first_name) . '.' . str_slug($last_name);
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
     * @since  [v6.0.5]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by')->withTrashed();
    }


    /**
     * Decode JSON permissions into array
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
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

                if (!is_integer($permission)) {
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
     * Query builder scope to search on text filters for complex Bootstrap Tables API
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $filter JSON array of search keys and terms
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByFilter($query, $filter)
    {
        return $query->where(
            function ($query) use ($filter) {
                foreach ($filter as $fieldname => $search_val) {

                    if ($fieldname == 'first_name') {
                        $query->where('users.first_name', 'LIKE', '%' . $search_val . '%');
                    }
                    if ($fieldname == 'last_name') {
                        $query->where('users.last_name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'display_name') {
                        $query->where('users.display_name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'name') {
                        $query->where('users.last_name', 'LIKE', '%' . $search_val . '%')
                            ->orWhere('users.first_name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'username') {
                        $query->where('users.username', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'email') {
                        $query->where('users.email', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'phone') {
                        $query->where('users.phone', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'mobile') {
                        $query->where('users.mobile', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'phone') {
                        $query->where('users.phone', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'jobtitle') {
                        $query->where('users.jobtitle', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'created_at') {
                        $query->where('users.created_at', '=', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'updated_at') {
                        $query->where('users.updated_at', '=', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'start_date') {
                        $query->where('users.start_date', '=', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'end_date') {
                        $query->where('users.end_date', '=', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'employee_num') {
                        $query->where('users.employee_num', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'locale') {
                        $query->where('users.locale', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'address') {
                        $query->where('users.address', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'state') {
                        $query->where('users.state', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'zip') {
                        $query->where('users.zip', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'country') {
                        $query->where('users.country', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'vip') {
                        $query->where('users.vip', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'remote') {
                        $query->where('users.remote', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'start_date') {
                        $query->where('users.purchase_date', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'notes') {
                        $query->where('users.notes', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'location') {
                        $query->whereHas(
                            'location', function ($query) use ($search_val) {
                            $query->where('locations.name', 'LIKE', '%' . $search_val . '%');
                        }
                        );
                    }

                    if ($fieldname == 'company') {
                        $query->whereHas(
                            'company', function ($query) use ($search_val) {
                            $query->where('companies.name', 'LIKE', '%' . $search_val . '%');
                        }
                        );
                    }


                }


            }
        );
    }

    /**
     * Query builder scope to search user by name with spaces in it.
     * We don't use the advancedTextSearch() scope because that searches
     * all of the relations as well, which is more than what we need.
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     * @param  array                              $terms The search terms
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSimpleNameSearch($query, $search)
    {
        return $query->where('first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
            ->orWhere('display_name', 'LIKE', '%' . $search . '%')
            ->orWhereMultipleColumns(
                [
                'users.first_name',
                'users.last_name',
                ], $search
            );
    }

    /**
     * Run additional, advanced searches.
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     * @param  array                              $terms The search terms
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function advancedTextSearch(Builder $query, array $terms)
    {
        foreach($terms as $term) {
            $query->orWhereMultipleColumns(
                [
                'users.first_name',
                'users.last_name',
                ], $term
            );
        }

        return $query;
    }

    /**
     * Query builder scope to return users by group
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     * @param  int                                $id
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByGroup($query, $id)
    {
        return $query->whereHas(
            'groups', function ($query) use ($id) {
                $query->where('permission_groups.id', '=', $id);
            }
        );
    }

    /**
     * Return only admins and superusers
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     */
    public function scopeOnlySuperAdmins($query)
    {

        return $query->where('users.permissions', 'LIKE', '%"superuser":"1"%')
            ->orWhere('users.permissions', 'LIKE', '%"superuser":1%')
            ->orWhereHas(
                'groups', function ($query) {
                    $query->where('permission_groups.permissions', 'LIKE', '%"superuser":"1"%')
                        ->orWhere('permission_groups.permissions', 'LIKE', '%"superuser":1%');
                    }
            );

    }

    /**
     * Return only admins and superusers
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     */
    public function scopeOnlyAdminsAndSuperAdmins($query)
    {

        return $query->where('users.permissions', 'LIKE', '%"superuser":"1"%')
            ->orWhere('users.permissions', 'LIKE', '%"superuser":1%')
            ->orWhere('users.permissions', 'LIKE', '%"admin":1%')
            ->orWhere('users.permissions', 'LIKE', '%"admin":"1"%')
            ->orWhereHas(
                'groups', function ($query) {
                $query->where('permission_groups.permissions', 'LIKE', '%"superuser":"1"%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"superuser":1%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"admin":1%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"admin":"1"%');
            }
            );

    }



    /**
     * Query builder scope to order on manager
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManager($query, $order)
    {
        // Left join here, or it will only return results with parents
        return $query->leftJoin('users as users_manager', 'users.manager_id', '=', 'users_manager.id')->orderBy('users_manager.first_name', $order)->orderBy('users_manager.last_name', $order);
    }

    /**
     * Query builder scope to order on company
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations as locations_users', 'users.location_id', '=', 'locations_users.id')->orderBy('locations_users.name', $order);
    }

    /**
     * Query builder scope to order on department
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderDepartment($query, $order)
    {
        return $query->leftJoin('departments as departments_users', 'users.department_id', '=', 'departments_users.id')->orderBy('departments_users.name', $order);
    }

    /**
     * Query builder scope to order on admin user
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param string                             $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderByCreatedBy($query, $order)
    {
        // Left join here, or it will only return results with parents
        return $query->leftJoin('users as admin_user', 'users.created_by', '=', 'admin_user.id')
            ->orderBy('admin_user.first_name', $order)
            ->orderBy('admin_user.last_name', $order);
    }


    /**
     * Query builder scope to order on company
     *
     * @param Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                              $order Order
     *
     * @return Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCompany($query, $order)
    {
        return $query->leftJoin('companies as companies_user', 'users.company_id', '=', 'companies_user.id')->orderBy('companies_user.name', $order);
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

    public function getUserTotalCost()
    {
        $asset_cost= 0;
        $license_cost= 0;
        $accessory_cost= 0;
        foreach ($this->assets as $asset){
            $asset_cost += $asset->purchase_cost;
            $this->asset_cost = $asset_cost;
        }
        foreach ($this->licenses as $license){
            $license_cost += $license->purchase_cost;
            $this->license_cost = $license_cost;
        }
        foreach ($this->accessories as $accessory){
            $accessory_cost += $accessory->purchase_cost;
            $this->accessory_cost = $accessory_cost;
        }

        $this->total_user_cost = ($asset_cost + $accessory_cost + $license_cost);


        return $this;
    }
    public function scopeUserLocation($query, $location, $search)
    {

        return $query->where('location_id', '=', $location)
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.email', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.permissions', 'LIKE', '%' . $search . '%')
            ->orWhere('users.country', 'LIKE', '%' . $search . '%')
            ->orWhere('users.phone', 'LIKE', '%' . $search . '%')
            ->orWhere('users.jobtitle', 'LIKE', '%' . $search . '%')
            ->orWhere('users.employee_num', 'LIKE', '%' . $search . '%')
            ->orWhere('users.username', 'LIKE', '%' . $search . '%')
            ->orWhere('users.display_name', 'LIKE', '%' . $search . '%')
            ->orwhereRaw('CONCAT(users.first_name," ",users.last_name) LIKE \''.$search.'%\'');

    }

    /**
     * Get all direct and indirect subordinates for this user.
     *
     * @return \Illuminate\Support\Collection
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
     * @return \Illuminate\Support\Collection
     */
    public function getAllSubordinatesIncludingSelf()
    {
        $subordinates = collect([$this]);
        $this->fetchSubordinatesRecursive($this, $subordinates);
        return $subordinates->unique('id');
    }

    /**
     * Recursive helper function to fetch subordinates.
     *
     * @param User                           $manager
     * @param \Illuminate\Support\Collection $subs
     */
    protected function fetchSubordinatesRecursive(User $manager, \Illuminate\Support\Collection &$subs)
    {
        // Eager load 'managesUsers' to prevent N+1 queries in recursion
        $directSubordinates = $manager->managesUsers()->with('managesUsers')->get();

        foreach ($directSubordinates as $directSubordinate) {
            // Add subordinate if not already in the collection
            if (!$subs->contains('id', $directSubordinate->id)) {
                 $subs->push($directSubordinate);
                 // Recursive call for this subordinate's subordinates
                 $this->fetchSubordinatesRecursive($directSubordinate, $subs);
            }
        }
    }

    /**
     * Check if the current user is a direct or indirect manager of the given user.
     *
     * @param  User $userToCheck
     * @return bool
     */
    public function isManagerOf(User $userToCheck): bool
    {
        // Optimization: If it's the same user, they are not their own manager
        if ($this->id === $userToCheck->id) {
            return false;
        }

        // Eager load manager relationship to potentially reduce queries in the loop
        $manager = $userToCheck->load('manager')->manager;
        while ($manager) {
            if ($manager->id === $this->id) {
                return true;
            }
            // Move up the hierarchy (load relationship if not already loaded)
            $manager = $manager->load('manager')->manager;
        }
        return false;
    }
}
