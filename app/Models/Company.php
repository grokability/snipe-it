<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
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
use Illuminate\Support\Facades\Storage;
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
    use UniqueUndeletedTrait;

    protected $table = 'companies';

    // Declare the rules for the model validation
    protected $rules = [
        'name' => 'required|max:255|unique_undeleted',
        'fax' => 'min:7|max:35|nullable',
        'phone' => 'min:7|max:35|nullable',
        'email' => 'email|max:150|nullable',
        'parent_id' => 'nullable|integer|exists:companies,id|parent_must_be_top_level:companies,id|must_have_no_children:companies,id',
    ];

    protected $casts = [
        'parent_id' => 'integer',
    ];

    /**
     * A company with no parent stores NULL, never 0 — the empty string from an
     * unselected select2 and a literal 0 would otherwise survive the integer
     * cast and break the `exists:` validation + parent/child queries.
     */
    public function setParentIdAttribute($value): void
    {
        $this->attributes['parent_id'] = ($value === '' || $value === null || (int) $value === 0)
            ? null
            : (int) $value;
    }

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
        'parent_id',
        'phone',
        'fax',
        'email',
        'tag_color',
        'notes',
    ];

    /**
     * Per-request memoization for getCurrentUserCompanyIds(), keyed by user id.
     * CompanyableScope::apply() calls that method on every Eloquent query against
     * a Companyable model, and the index/transformer hot path runs hundreds of
     * such queries per page — without memoization we issue thousands of redundant
     * pivot reads. Cleared explicitly in tests via flushCompanyIdsCache().
     *
     * @var array<int, array<int>>
     */
    private static array $companyIdsCache = [];

    /**
     * Return the current user's company IDs by querying the pivot table directly.
     *
     * We deliberately bypass the Eloquent companies() relationship here because
     * loading that relationship triggers CompanyableScope on the Company model,
     * which calls this method again — infinite recursion.
     *
     * If a user is a member of a parent company, they implicitly have access to
     * all of that company's children too. We expand the direct pivot set by
     * pulling in children of any directly-assigned company. The one-level-deep
     * constraint enforced by validation means a single child lookup is sufficient.
     */
    private static function getCurrentUserCompanyIds(): array
    {
        if (! Auth::hasUser()) {
            return [];
        }

        $userId = auth()->id();

        if (array_key_exists($userId, self::$companyIdsCache)) {
            return self::$companyIdsCache[$userId];
        }

        $directIds = DB::table('company_user')
            ->where('user_id', $userId)
            ->pluck('company_id')
            ->toArray();

        if (empty($directIds)) {
            return self::$companyIdsCache[$userId] = [];
        }

        $childIds = DB::table('companies')
            ->whereIn('parent_id', $directIds)
            ->pluck('id')
            ->toArray();

        return self::$companyIdsCache[$userId] = array_values(array_unique(array_merge($directIds, $childIds)));
    }

    /**
     * Reset the per-user company-ids memoization. Called from TestCase::setUp()
     * so that test isolation isn't broken by static state surviving across tests
     * (RefreshDatabase rolls back the DB but not PHP static properties).
     */
    public static function flushCompanyIdsCache(): void
    {
        self::$companyIdsCache = [];
    }

    /**
     * Return the set of company IDs a user viewing $companyId should see items
     * from when "hierarchy expansion" is requested — the company itself, its
     * direct parent (if any), and any of its direct children.
     *
     * Used by the company show-page tabs (users / assets / licenses / etc.) so
     * that viewing a child company also surfaces items inherited from the
     * parent, and viewing a parent surfaces items from its children. The
     * one-level-deep validator caps the chain at depth 2, so this is at most
     * three rows.
     *
     * Returns the original id alone if the company can't be found, so callers
     * can pass the result straight into a whereIn without special-casing.
     */
    public static function reachableCompanyIds(int|string $companyId): array
    {
        $companyId = (int) $companyId;
        if ($companyId <= 0) {
            return [];
        }

        $row = DB::table('companies')->where('id', $companyId)->first(['id', 'parent_id']);
        if (! $row) {
            return [$companyId];
        }

        $ids = [(int) $row->id];
        if ($row->parent_id) {
            $ids[] = (int) $row->parent_id;
        }

        $childIds = DB::table('companies')
            ->where('parent_id', $companyId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        return array_values(array_unique(array_merge($ids, $childIds)));
    }

    /**
     * Walk the companies-by-parent map, emitting each company with a `use_text`
     * prefix that reflects its depth. Children appear directly under their
     * parent; orphans (children whose parent isn't in the visible set — can
     * happen under FMCS scoping) are surfaced as top-level so they aren't lost.
     *
     * The map's keys are parent_id values, with `0` used for "no parent / top-
     * level". Using 0 (not null) avoids PHP 8.4's deprecation of null array
     * offsets when callers build the map from `$company->parent_id`.
     *
     * Mirrors Location::indenter so the SelectlistTransformer renders the same
     * "-- Child Co" indentation it already does for locations.
     */
    public static function indenter(array $companies_by_parent, int $parent_id = 0, string $prefix = ''): array
    {
        $results = [];

        if (! array_key_exists($parent_id, $companies_by_parent)) {
            return [];
        }

        foreach ($companies_by_parent[$parent_id] as $company) {
            $company->use_text = trim($prefix.' '.$company->name);
            $company->use_image = ($company->image)
                ? Storage::disk('public')->url('companies/'.$company->image)
                : null;
            $results[] = $company;

            if (array_key_exists($company->id, $companies_by_parent)) {
                $results = array_merge(
                    $results,
                    self::indenter($companies_by_parent, $company->id, $prefix.'--'),
                );
            }
        }

        return $results;
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
            if ($current_user->isSuperUser()) {
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
            if (auth()->user()->isSuperUser()) {
                return true;
            }

            // For User targets the visibility rule is already encoded in the
            // CompanyableScope. If the actor can see this user in their scoped
            // list, they can act on it (the role-permission check that runs
            // after this still has final say). Doing this here keeps per-target
            // access in lockstep with list visibility — the back-patch for
            // #19187 tightened the bypass branch below but never updated the
            // per-target path, which left actors able to see users they
            // couldn't then edit. One check, one query, same logic as the list.
            if ($companyable instanceof User) {
                return User::where('users.id', $companyable->id)->exists();
            }

            $userCompanyIds = self::getCurrentUserCompanyIds();

            // Empty pivot = unrestricted only for true legacy "no-company" users
            // (those whose scalar company_id is also null). Users who had their
            // pivot cleared via the API retain their scalar company_id, so they
            // do NOT qualify for this bypass.
            if (empty($userCompanyIds) && is_null(auth()->user()->company_id)) {
                return true;
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

        if ($current_user->isSuperUser()) {
            return $requestedIds;
        }

        $allowedIds = self::getCurrentUserCompanyIds();

        return array_values(array_intersect($requestedIds, $allowedIds));
    }

    public static function isCurrentUserAuthorized()
    {
        return (! self::isFullMultipleCompanySupportEnabled()) || (auth()->user()->isSuperUser());
    }

    public static function canManageUsersCompanies()
    {
        return ! self::isFullMultipleCompanySupportEnabled()
            || auth()->user()->isSuperUser()
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
            && (($this->users_count ?? $this->users()->count()) === 0)
            && (($this->children_count ?? $this->children()->count()) === 0);
    }

    /**
     * @return int|mixed|string|null
     */
    public static function getIdForUser($unescaped_input)
    {
        if (! self::isFullMultipleCompanySupportEnabled() || auth()->user()->isSuperUser()) {
            return self::getIdFromInput($unescaped_input);
        } else {
            return self::getIdForCurrentUser($unescaped_input);
        }
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }

    /**
     * Parent company (one level only — children cannot themselves have children).
     *
     * Bypasses CompanyableScope because the scope hardcodes `companies.id` in its
     * where clause, which collides with Eloquent's self-relation auto-alias
     * (`laravel_reserved_0`) and produces "Unknown column 'laravel_reserved_0.parent_id'"
     * on the index page. Hierarchy is metadata about a row the user already sees,
     * not an access decision, so unscoping here is semantically correct too.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->withoutGlobalScopes();
    }

    /**
     * Child companies. The one-level-deep validator on parent_id guarantees
     * children of a child cannot be created, so this is the full descendant set.
     * See parent() above for why the global scope is dropped.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->withoutGlobalScopes();
    }

    /**
     * Sort the company list by the parent company's name. Left join so that
     * top-level companies (parent_id IS NULL) still appear in the results.
     *
     * Use addSelect (not select) so prior withCount subqueries on the query
     * survive — select() replaces the whole columns list and would strip the
     * eager *_count columns, forcing isDeletable() and the transformer into a
     * 7-query-per-row N+1.
     */
    public function scopeOrderParent($query, $order)
    {
        return $query->leftJoin('companies as parent_co', 'companies.parent_id', '=', 'parent_co.id')
            ->addSelect('companies.*')
            ->orderBy('parent_co.name', $order);
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
        if (! self::isFullMultipleCompanySupportEnabled() || (Auth::hasUser() && auth()->user()->isSuperUser()) || (! Auth::hasUser())) {
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
        } elseif (! self::isFullMultipleCompanySupportEnabled() || (Auth::hasUser() && auth()->user()->isSuperUser())) {
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
     * Query builder scope to order on the user that created it.
     *
     * Use addSelect (not select) so prior withCount subqueries on the query
     * survive — see scopeOrderParent() for the same rationale.
     */
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'companies.created_by', '=', 'admin_sort.id')
            ->addSelect('companies.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }
}
