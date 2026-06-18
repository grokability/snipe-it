<?php

namespace App\Models;

use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\CompanyPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Companies.
 *
 * @version v1.8
 */
final class Company extends SnipeModel
{
    use CompanyableTrait;
    use HasFactory;
    use HasUploads;
    use Loggable;
    use SoftDeletes;

    protected $table = 'companies';

    // Declare the rules for the model validation
    protected $rules = [
        'name' => 'required|max:255|unique:companies,name',
        'fax' => 'min:7|max:35|nullable',
        'phone' => 'min:7|max:35|nullable',
        'email' => 'email|max:150|nullable',
    ];

    protected $presenter = CompanyPresenter::class;

    use Presentable;

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    use Searchable;
    use ValidatingTrait;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'phone',
        'fax',
        'email',
        'created_at',
        'updated_at',
        'notes',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'fax',
        'email',
        'tag_color',
        'notes',
    ];

    /**
     * Return the current user's company IDs by querying the pivot table directly.
     *
     * We deliberately bypass the Eloquent companies() relationship here because
     * loading that relationship triggers CompanyableScope on the Company model,
     * which calls this method again — infinite recursion.
     */
    private static function getCurrentUserCompanyIds(): array
    {
        if (! Auth::hasUser()) {
            return [];
        }

        return DB::table('company_user')
            ->where('user_id', auth()->id())
            ->pluck('company_id')
            ->toArray();
    }

    public static function isFullMultipleCompanySupportEnabled()
    {
        $settings = Setting::getSettings();

        // NOTE: this can happen when seeding the database
        if (is_null($settings)) {
            return false;
        } else {
            return $settings->full_multiple_companies_support == 1;
        }
    }

    public static function getIdFromInput($unescaped_input)
    {
        $escaped_input = e($unescaped_input);

        if ($escaped_input == '0') {
            return null;
        } else {
            return $escaped_input;
        }
    }

    /**
     * Get the company id for the current user taking into
     * account the full multiple company support setting
     * and if the current user is a super user.
     *
     * @return int|mixed|string|null
     */
    public static function getIdForCurrentUser($unescaped_input)
    {
        if (! self::isFullMultipleCompanySupportEnabled()) {
            return self::getIdFromInput($unescaped_input);
        } else {
            $current_user = auth()->user();

            // Super users should be able to set a company to whatever they need
            if ($current_user->isSuperUser() || $current_user->isMultiCompany()) {
                return self::getIdFromInput($unescaped_input);
            } else {
                $userCompanyIds = self::getCurrentUserCompanyIds();
                $submittedId = (int) self::getIdFromInput($unescaped_input);

                // Company membership is now determined entirely by the pivot (company_user table).
                // If the submitted value is a company the user actually belongs to, honour it.
                if ($submittedId && in_array($submittedId, $userCompanyIds)) {
                    return $submittedId;
                }

                // A user with pivot memberships who submits a company they don't belong to is
                // attempting cross-tenant assignment — reject outright rather than silently
                // overriding or storing null.
                if ($submittedId && ! empty($userCompanyIds)) {
                    throw ValidationException::withMessages([
                        'company_id' => [trans('validation.in', ['attribute' => 'company_id'])],
                    ]);
                }

                // No company submitted (or user has no pivot memberships) — fall back to the
                // user's single company if unambiguous, otherwise null.
                return count($userCompanyIds) === 1 ? $userCompanyIds[0] : null;
            }
        }
    }

    /**
     * Check to see if the current user should have access to the model.
     * I hate this method and I think it should be refactored.
     *
     * @return bool|void
     */
    public static function isCurrentUserHasAccess($companyable)
    {
        // When would this even happen tho??
        if (is_null($companyable)) {
            return false;
        }

        // If FMCS is not enabled, everyone has access, return true
        if (! self::isFullMultipleCompanySupportEnabled()) {
            return true;
        }

        // Again, where would this happen? But check that $companyable is not a string
        if (! is_string($companyable)) {
            $company_table = $companyable->getModel()->getTable();
            try {
                // This is primarily for the gate:allows-check in location->isDeletable()
                // Locations don't have a company_id so without this it isn't possible to delete locations with FullMultipleCompanySupport enabled
                // because this function is called by SnipePermissionsPolicy->before()
                if (! Schema::hasColumn($company_table, 'company_id')) {
                    return true;
                }

            } catch (\Exception $e) {
                Log::warning($e);
            }
        }

        if (auth()->user()) {
            if (auth()->user()->isSuperUser() || auth()->user()->isMultiCompany()) {
                return true;
            }

            $userCompanyIds = self::getCurrentUserCompanyIds();

            // Empty pivot = unrestricted only for true legacy "no-company" users
            // (those whose scalar company_id is also null). Users who had their
            // pivot cleared via the API retain their scalar company_id, so they
            // do NOT qualify for this bypass.
            if (empty($userCompanyIds) && is_null(auth()->user()->company_id)) {
                return true;
            }

            // Users are scoped by pivot membership, not company_id, so check the pivot directly.
            if ($companyable instanceof User) {
                $companyableCompanyIds = DB::table('company_user')
                    ->where('user_id', $companyable->id)
                    ->pluck('company_id')
                    ->toArray();

                // A null-company user (no pivot rows) is accessible in floater mode.
                if (empty($companyableCompanyIds)) {
                    return (bool) Setting::getSettings()->null_company_is_floater;
                }

                return ! empty(array_intersect($userCompanyIds, $companyableCompanyIds));
            }

            $companyable_company_id = ($companyable instanceof Company)
                ? $companyable->id
                : $companyable->company_id;

            // Null-company items are accessible to company-scoped users only when floater is on.
            if (is_null($companyable_company_id)) {
                return (bool) Setting::getSettings()->null_company_is_floater;
            }

            return in_array($companyable_company_id, $userCompanyIds);
        }

        return false;
    }

    /**
     * Filter an array of requested company IDs to only those the current user
     * belongs to. Superusers may assign any company; non-superusers are limited
     * to their own pivot memberships when FMCS is enabled.
     */
    public static function getIdsForCurrentUser(array $requestedIds): array
    {
        if (! self::isFullMultipleCompanySupportEnabled()) {
            return $requestedIds;
        }

        $current_user = auth()->user();

        if ($current_user->isSuperUser() || $current_user->isMultiCompany()) {
            return $requestedIds;
        }

        $allowedIds = self::getCurrentUserCompanyIds();

        return array_values(array_intersect($requestedIds, $allowedIds));
    }

    public static function isCurrentUserAuthorized()
    {
        return (! self::isFullMultipleCompanySupportEnabled()) || (auth()->user()->isSuperUser() || auth()->user()->isMultiCompany());
    }

    public static function canManageUsersCompanies()
    {
        return ! self::isFullMultipleCompanySupportEnabled()
            || auth()->user()->isSuperUser()
            || auth()->user()->isMultiCompany()
            || ! empty(self::getCurrentUserCompanyIds());
    }

    /**
     * Checks if company can be deleted
     *
     * @author [Dan Meltzer] [<dmeltzer.devel@gmail.com>]
     *
     * @since  [v5.0]
     *
     * @return bool
     */
    public function isDeletable()
    {

        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->components_count ?? $this->components()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->users_count ?? $this->users()->count()) === 0);
    }

    /**
     * @return int|mixed|string|null
     */
    public static function getIdForUser($unescaped_input)
    {
        if (! self::isFullMultipleCompanySupportEnabled() || auth()->user()->isSuperUser() || auth()->user()->isMultiCompany()) {
            return self::getIdFromInput($unescaped_input);
        } else {
            return self::getIdForCurrentUser($unescaped_input);
        }
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'company_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'company_id');
    }

    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'company_id');
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'company_id');
    }

    public function components()
    {
        return $this->hasMany(Component::class, 'company_id');
    }

    /**
     * START COMPANY SCOPING FOR FMCS
     */

    /**
     * Scoping table queries, determining if a logged in user is part of a company, and only allows the user to access items associated with that company if FMCS is enabled.
     *
     * This method is the one that the CompanyableTrait uses to contrain queries automatically, however that trait CANNOT be
     * applied to the user's model, since it causes an infinite loop against the authenticated user.
     *
     * @todo - refactor that trait to handle the user's model as well.
     *
     * @author [A. Gianotto] <snipe@snipe.net>
     *
     * @return mixed
     */
    public static function scopeCompanyables($query, $column = 'company_id', $table_name = null)
    {
        // If not logged in and hitting this, assume we are on the command line and don't scope?
        if (! self::isFullMultipleCompanySupportEnabled() || (Auth::hasUser() && (auth()->user()->isSuperUser() || auth()->user()->isMultiCompany())) || (! Auth::hasUser())) {
            return $query;
        } else {
            return self::scopeCompanyablesDirectly($query, $column, $table_name);
        }
    }

    /**
     * Scoping table queries, determining if a logged-in user is part of a company, and only allows
     * that user to see items associated with that company
     *
     * @see https://github.com/laravel/framework/pull/24518 for info on Auth::hasUser()
     */
    private static function scopeCompanyablesDirectly($query, $column = 'company_id', $table_name = null)
    {
        $companyIds = self::getCurrentUserCompanyIds();

        // If we are scoping the companies table itself, look for the company.id
        if ($query->getModel()->getTable() == 'companies') {
            if (empty($companyIds)) {
                return $query->whereNull('companies.id');
            }

            return $query->whereIn('companies.id', $companyIds);
        }

        $floater = Setting::getSettings()->null_company_is_floater;

        // Users are scoped by pivot membership (company_user), not by company_id column,
        // since a user may belong to multiple companies and company_id alone is insufficient.
        if ($query->getModel()->getTable() == 'users') {
            if (empty($companyIds)) {
                // Floater: null-company actor is unrestricted — see everyone.
                if ($floater) {
                    return $query;
                }

                // No pivot memberships and floater off: show only other null-company users.
                return $query->whereNotIn('users.id', function ($sub) {
                    $sub->select('user_id')->from('company_user');
                });
            }

            // Floater: also show null-company users (no pivot rows) to company-scoped actors.
            if ($floater) {
                return $query->where(function ($q) use ($companyIds) {
                    $q->whereIn('users.id', function ($sub) use ($companyIds) {
                        $sub->select('user_id')->from('company_user')->whereIn('company_id', $companyIds);
                    })->orWhereDoesntHave('companies');
                });
            }

            return $query->whereIn('users.id', function ($sub) use ($companyIds) {
                $sub->select('user_id')->from('company_user')->whereIn('company_id', $companyIds);
            });
        }

        // If the column exists in the table, use it to scope the query
        if ($query && $query->getModel() && Schema::hasColumn($query->getModel()->getTable(), $column)) {
            $table = ($table_name) ? $table_name.'.' : $query->getModel()->getTable().'.';

            if (empty($companyIds)) {
                // Floater: null-company actor sees all items (they are unrestricted for assets/etc).
                if ($floater) {
                    return $query;
                }

                return $query->whereNull($table.$column);
            }

            // action_logs: a NULL company_id means the logged object (AssetModel, Company, etc.)
            // has no company_id column of its own. Those are global objects, visible to all users,
            // so their log entries should not be hidden by the company filter.
            if ($query->getModel()->getTable() === 'action_logs') {
                return $query->where(function ($q) use ($table, $column, $companyIds) {
                    $q->whereIn($table.$column, $companyIds)
                        ->orWhereNull($table.$column);
                });
            }

            // Floater: null-company items are visible to users from any company.
            if ($floater) {
                return $query->where(function ($q) use ($table, $column, $companyIds) {
                    $q->whereIn($table.$column, $companyIds)
                        ->orWhereNull($table.$column);
                });
            }

            return $query->whereIn($table.$column, $companyIds);
        }
    }

    /**
     * Scope a users query to those belonging to the given company IDs, respecting floater mode.
     *
     * Extracted from controller-level inline logic so the same rule is enforced consistently
     * everywhere users are filtered by a specific set of company IDs (e.g. select2 dropdowns).
     */
    public static function scopeUsersByCompanyIds($query, array $companyIds): mixed
    {
        if (Setting::getSettings()->null_company_is_floater) {
            return $query->where(function ($q) use ($companyIds) {
                $q->whereHas('companies', fn ($q2) => $q2->whereIn('companies.id', $companyIds))
                    ->orWhereDoesntHave('companies');
            });
        }

        return $query->whereHas('companies', fn ($q) => $q->whereIn('companies.id', $companyIds));
    }

    /**
     * I legit do not know what this method does, but we can't remove it (yet).
     *
     * This gets invoked by CompanyableChildScope, but I'm not sure what it does.
     *
     * @author [A. Gianotto] <snipe@snipe.net>
     *
     * @return mixed
     */
    public static function scopeCompanyableChildren(array $companyable_names, $query)
    {

        if (count($companyable_names) == 0) {
            throw new Exception('No Companyable Children to scope');
        } elseif (! self::isFullMultipleCompanySupportEnabled() || (Auth::hasUser() && (auth()->user()->isSuperUser() || auth()->user()->isMultiCompany()))) {
            return $query;
        } else {
            $f = function ($q) {
                static::scopeCompanyablesDirectly($q);
            };

            $q = $query->where(
                function ($q) use ($companyable_names, $f) {
                    $q2 = $q->whereHas($companyable_names[0], $f);

                    for ($i = 1; $i < count($companyable_names); $i++) {
                        $q2 = $q2->orWhereHas($companyable_names[$i], $f);
                    }
                }
            );

            return $q;
        }
    }

    /**
     * Query builder scope to order on the user that created it
     */
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'companies.created_by', '=', 'admin_sort.id')->select('companies.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
